<?php

require_once  'libraries/ceisax/vendor/autoload.php';
use Jasny\SSO\Broker;

class C_user extends Base_Controller{
	public function __construct(){
		parent::__construct();	//call parent's ctor
		//load all model used
		$this->load_model('user');
		$this->load_model('carnet');
		$this->load_model('app');
		$this->load_model('menu');

		$this->sso = new Broker('http://192.168.146.248/ssoserver/', '3', 'q9Qk3e8PY2');
		$this->sso->attach(true);
	}
	
	function index(){
		//if we're not expired, keep alive
	}

	function login($error){
		// gotta check if already logged in
		if ($this->user->isLoggedIn()) {
			// redirect
			header('location: '.base_url(''));
		} else if ( ($userInfo = $this->user->getSSOProfile() ) != null ) {
			// welp it's logged in SSO, so gotta create new user
			echo 'New User detected!';
			// create new user
			$newUser = $this->user->createDefaultUser(
				$userInfo['username'], 
				$userInfo['name'],
				'Y',
				$userInfo['user_id']
			);

			if ($newUser) {
				// echo ", so created with id : " . $newUser;
				// just redirect to base page
				header('location: ' . base_url(''));
			} else {
				echo $this->user->getLastError();
			}	
			exit;		
		}

		$data=array();
		$data['pagetitle'] = $this->app->getTitle();
		if(isset($_GET['error']))
			$data['loginErr']=1;
		$this->load_view('login', $data);
	}

	function logout(){
		// $this->user->attemptLogout();
		// header('location: '.base_url(''));
		$this->logoutsso();
	}

	function logoutsso() {
		$this->user->attemptSSOLogout();
		header('location: '.base_url(''));
	}

	function validate(){
		//validasi login. redirect ke index klo berhasil
		//balikin ke login kalo gagal
		//header('location: '.base_url('user/login?error=1'));

		$this->load_model('user');

		$username	= isset($_POST['username']) ? $_POST['username'] : '';
		$password	= isset($_POST['password']) ? $_POST['password'] : '';
		$ipAddress	= $_SERVER['REMOTE_ADDR'];
		$port	= $_SERVER['REMOTE_PORT'];

		$result = $this->user->sapiLogin($username, $password, $ipAddress, $port);

		if ($result['status']) {
			// login success, save user session
			$this->user->registerUserSession($result['loginData']);
			// set message
			$this->user->message('Selamat Datang, ' . $result['loginData']['fullname']);
			// redirect
			header('location: ' . base_url(''));
		} else {
			header('location: '.base_url('user/login?error='. htmlentities($result['error'][0])));
		}
		/*if(!isset($_POST['username']) || !isset($_POST['password'])){
			//insufficient data. redirect
			header('location: '.base_url('user/login?error=1'));
		}else{
			//attempt login
			if($this->user->tryLogin($_POST['username'], $_POST['password'])){
				//redirect to index page
				$this->user->message('Selamat Datang');
				header('location: '.base_url(''));
			}else{
				//failed, redirect back to error page
				header('location: '.base_url('user/login?error=1'));
			}
		}*/
	}

	public function dummysso($username, $password) {
		header('Content-type: application/json;');
		try {
			echo json_encode($this->user->dummyLogin($username, $password));
		} catch (\Exception $e) {
			header('HTTP/1.1 400 Bad Request');
			echo json_encode([
				'message' => $e->getMessage(),
				'code' => $e->getCode()
			]);
		}
	}

	public function getlogindata() {
		var_dump($this->user->getData());
	}

	public function checklogin() {
		// var_dump( $this->user->isLoggedIn() );
		// var_dump( $this->user->getSSOProfile());

		if ($this->user->isLoggedIn()) {
			header('Content-type: application/json;');
			echo json_encode($this->user->getSSOProfile());
		}
	}

	// validate data dari SSO
	public function validatesso() {
		// validate login using sso

		// load user model
		$this->load_model('user');

		// grab necessary parameters
		$username	= isset($_POST['username']) ? $_POST['username'] : '';
		$password	= isset($_POST['password']) ? $_POST['password'] : '';
		$ipAddress	= $_SERVER['REMOTE_ADDR'];
		$port	= $_SERVER['REMOTE_PORT'];

		$result = $this->user->ssoLogin($username, $password, $ipAddress, $port);

		// result is set, meaning login successful, store user data
		if ($result['status']) {
			// var_dump($result['loginData']);
			
			// login success, save user session
			$this->user->registerUserSession($result['loginData']);
			// exit;
			// set message
			$this->user->message('Selamat Datang, ' . $result['loginData']['fullname']);
			// redirect
			header('location: ' . base_url(''));
		} else {
			header('location: '.base_url('user/login?error='. htmlentities($result['error'][0])));
		}
		// var_dump($result);
	}

	// this page is for changing password
	function changepass() {
		// gotta check user role + login state
		$this->user->forceLogin();


		$data = array();

		if (isset($_POST['newpass'])) {
			$newPassword = $_POST['newpass'];
			$oldPassword = $_POST['oldpass'];
			$uid = $this->user->getData()['id'];

			if (!$this->user->changePassword($uid, $oldPassword, $newPassword))	
				$data['message'] = 'Gagal mengubah password';
			else
				$data['message'] = 'Password berhasil diubah';
		}

		$data['pagetitle'] = $this->app->getTitle().' (Ganti Password)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$data['mainContent'] = $this->load_view('change_pass', null, true);
		$this->load_view('index', $data);
	}

	function simpan() {
		// header('Content-Type: application/json;');
		$data = array();

		$result = false;
		$msg ='';

		if ( $_POST['action'] == 'Simpan') {
			
				// prepare save data
			
			// user specific data
			$udata = array();
			$udata[':id'] = $_POST['id'];
			$udata[':username'] = $_POST['username'];
			$udata[':fullname'] = $_POST['fullname'];
			$udata[':active'] = isset($_POST['active']) ? 'Y' : 'N';
			$udata[':role'] = isset($_POST['role']) ? implode(',', $_POST['role']) : '';

			// list of paired gudang
			$gudang = isset($_POST['gudang']) ? $_POST['gudang'] : array();

			$result = $this->user->save($udata, $gudang);

			if ($result) {
				$msg = "Update Data User berhasil";
			} else {
				$msg = "Gagal update user. " . $this->user->getLastError();
			}

			$title = "Update data user";
		} else if ($_POST['action'] == 'Reset Password') {
			// coba ubah password di sini
			$uid = $_POST['id'];
			$newPassword = $_POST['password'];

			$result = $this->user->resetPassword($uid, $newPassword);

			if ($result) {
				$msg = "Reset Password berhasil";
			} else {
				$msg = "Gagal reset password. " . $this->user->getLastError();
			}

			$title = "Reset Password";
		}

		//var_dump($result);

		// gunakan halaman redirection
		$data = array(
			'pagetitle' => $title,
			'message' => $msg,
			'seconds' => 3,
			'targetName' => 'Halaman sebelumnya',
			'target' => base_url('app/manage/user')
			);

		$this->load_view('message_redirect', $data, false);


		// echo json_encode($_POST);
	}

	// FORM_PROCESS_PAGE: buat delete user
	public function delete($uid) {
		// force login
		$this->user->forceLogin();

		// if not superuser, forbid
		if (!$this->user->hasRole(R_SUPERUSER))
			return forbid();

		// try
		$result = $this->user->delete($uid);

		if ($result)
			$msg = "User with id: ". $uid . " was deleted";
		else
			$msg = "Failed to delete user with id: " . $uid . ", alasan: " . $this->user->getLastError();

		$title = "Menghapus user...";

		// gunakan halaman redirection
		$data = array(
			'pagetitle' => $title,
			'message' => $msg,
			'seconds' => 3,
			'targetName' => 'Halaman sebelumnya',
			'target' => base_url('app/manage/user')
			);

		$this->load_view('message_redirect', $data, false);
	}

	// halaman buat tambah user
	public function add() {
		$this->user->forceLogin();

		if (!$this->user->hasRole(R_SUPERUSER))
			return forbid();

		$data = array();
		$data['pagetitle'] = $this->app->getTitle() . ' Browse Data per Gudang';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$data['mainContent'] = $this->load_view('user_detail', array(
										'listGudang' => $this->user->getAllRegisteredGudang(),
										'password' => '123456',
										'mode' => 'add'
									), true);

		// show page
		$this->load_view('index', $data, false);
	}

	/*
	FORM_PROCESS_PAGE: buat daftar user baru
	*/
	public function register() {
		// force login
		$this->user->forceLogin();

		// cuma super user yg boleh akses
		if (!$this->user->hasRole(R_SUPERUSER))
			return forbid();

		// bikin default data
		$username = isset($_POST['username']) ? $_POST['username'] : '';
		$password = isset($_POST['password']) ? $_POST['password'] : '123456';
		$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
		$role = isset($_POST['role']) ? implode(',', $_POST['role']) : '';
		$active = isset($_POST['active']) ? 'Y' : 'N';

		// data gudang
		$gudang = isset($_POST['gudang']) ? $_POST['gudang'] : null;

		

		$result = $this->user->add(
			$username, $password, $fullname, $role, $active, $gudang
			);

		if ($result === false)
			$msg = "Gagal mendaftarkan user. " . $this->user->getLastError();
		else
			$msg = "User terdaftar dengan id: " . $result;

		$title = "Registrasi user";

		// gunakan halaman redirection
		$data = array(
			'pagetitle' => $title,
			'message' => $msg,
			'seconds' => 3,
			'targetName' => 'Halaman sebelumnya',
			'target' => base_url('app/manage/user')
			);

		$this->load_view('message_redirect', $data, false);
	}

	public function ssologin() {
		$loginUrl = base_url('user/login');

		try {
			$result = $this->sso->login('admin', '1231');
		} catch (NotAttachedException $e) {
			$redirectUrl = base_url('user/login?error=' . urlencode($e->getMessage()));
			header('location: ' . $redirectUrl);
			exit;
		} catch (Jasny\SSO\Exception $e) {
			$redirectUrl = base_url('user/login?error=' . urlencode($e->getMessage()));
			header('location: ' . $redirectUrl);
		}
		

		if (isset($result)) {
			// header('Content-type: application/json');
			var_dump($result);
		}
	}
}
?>