<?php
/*
	Model: user
	berisi fungsi2 pembantu utk memanage user
*/

// Role user
define('R_PJT', 1);
define('R_PPJK', 2);
define('R_ADMIN_PABEAN', 4);
define('R_SUPERUSER', 8);
define('R_PEMERIKSA', 16);
define('R_CARNET_HANDLER', 32);	// Currently unused

// Settingan session user
define('USER_SESSION', 60*30);

class user extends Base_Model{
	// private $lastErr = "";

	function __construct(){
		parent::__construct();
		$this->load_db();	//we load database
		if(!isset($_SESSION)){
			// global $session;
			// session_name($session['name']);
			session_start();
			//init search data
			if(!isset($_SESSION['loginData']))
				$_SESSION['loginData'] = array();
		}
	}

	// public function setLastError($msg) {
	// 	$this->lastErr = $msg;
	// }

	// public function getLastError() {
	// 	return $this->lastErr;
	// }

	public function sapiLogin($username, $pass, $ipAddress, $port) {
		// validasi data
		$username 	= trim($username);
		$ipPattern 	= '/(\:{2}1|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|localhost)/';

		// convert ::1 to 127.0.0.1
		$ipAddress = ($ipAddress == '::1' ? '127.0.0.1' : $ipAddress);
		$ipAddress = ($ipAddress == 'localhost' ? '127.0.0.1' : $ipAddress);

		// check validitas
		$valid 	= strlen($username) <= 32 && strlen($pass) <= 128 && preg_match($ipPattern, $ipAddress) && $port < 65536;

		if (!$valid) {
			// langsung keluar
			return array(
				'status'	=> false,
				'error'	=> array('data login tidak valid'),
				'loginData'	=> null
				);
		}

		// simple session login
		/*
		-cek kombinasi username + password
		-kalo valid
		--cek apakah ada session yg blum expired dengan key (id, ip, port)
		---kalo ada
		----refresh session, pake session ini
		---else
		----buat session baru
		-else
		--return error (username/pass tidak valid)
		*/

		// query untuk cek kombinasi user + pass
		$q_cek_user_pass = "SELECT
								a.id,
								a.username,
								a.fullname,
								a.role,
								(a.role+0) role_code
							FROM
								user a
							WHERE
								a.username = :uname
								AND a.password = MD5(:pass)
								AND a.active = 'Y'";

		// query untuk cek session
		$q_cek_session = "SELECT
							a.id,
							a.user_id,
							a.ip_address,
							a.time_started,
							a.expire
						FROM
							user_session a
						WHERE
							a.user_id = :uid
							AND a.ip_address = INET_ATON(:ipaddr)
							AND a.expire > NOW()";

		$q_create_session = "INSERT INTO
								user_session(
									user_id,
									ip_address,
									expire
								)
							VALUES (
								:uid,
								INET_ATON(:ipaddr),
								ADDDATE(NOW(),INTERVAL 30 MINUTE)
							)";

		$q_update_session = "UPDATE
								user_session a
							SET
								a.expire = ADDDATE(NOW(),INTERVAL 30 MINUTE)
							WHERE
								a.id = :sid";

		$retData = array(
			'loginData' => null,
			'status' => false,
			'error' => array()
			);
		
		$this->db->beginTransaction();
		try {
			$stmt_cek_user_pass = $this->db->prepare($q_cek_user_pass);

			$res1 = $stmt_cek_user_pass->execute(array(
				':uname' => $username,
				':pass' => $pass
				));
			if (!$res1) {
				$retData['error'][] = 'Failed reading database';
				return $retData;
			}

			// let's read data
			$readData = $stmt_cek_user_pass->fetchAll(PDO::FETCH_ASSOC);

			if (count($readData) < 1) {
				$retData['error'][] = 'Username/Password is wrong';
				return $retData;
			}

			// store login data
			$retData['loginData'] = $readData[0];
			// explode role list
			$retData['loginData']['role'] = explode(',', $retData['loginData']['role']);

			// now cek session
			$stmt_cek_session = $this->db->prepare($q_cek_session);

			$res2 = $stmt_cek_session->execute(array(
				':uid' => $retData['loginData']['id'],
				':ipaddr' => $ipAddress
				));

			if (!$res2) {
				$retData['error'][] = 'Failed reading session';
				return $retData;
			}

			$readData = $stmt_cek_session->fetchAll(PDO::FETCH_ASSOC);

			if (count($readData) < 1) {
				// create new session
				$stmt_create_session = $this->db->prepare($q_create_session);

				$sess_data = array(
					':uid' => $retData['loginData']['id'],
					':ipaddr' => $ipAddress
					);

				// print_r($sess_data);

				$res3 = $stmt_create_session->execute($sess_data);

				/*if (!$res3) {
					echo "error creating session";
				}*/
				// var_dump($res3);
			} else {
				// update old session
				$sid = $readData[0]['id'];

				$stmt_update_session = $this->db->prepare($q_update_session);

				$res4 = $stmt_update_session->execute(array(
					':sid' => $sid
					));
			}

			// just return true
			$retData['status'] = true;

			$this->db->commit();

		} catch (PDOException $e) {
			$this->db->rollback();

			$retData['status'] = false;
			$retData['loginData'] = null;
			$retData['error'][] = $e->getMessage();
		}

		return $retData;
	}

	// cek role
	public function hasRole($role_id) {
		if (!$this->isLoggedIn())
			return false;

		return ($_SESSION['loginData']['role_code'] & $role_id) == $role_id;
	}

	// get gudang
	public function getListGudang($id) {
		$q_get_list_gudang = "
			SELECT
				gudang
			FROM
				user_gudang_pair
			WHERE
				user_id = :id
			";

		$stmt_get_list_gudang = $this->db->prepare($q_get_list_gudang);

		$result = $stmt_get_list_gudang->execute(array(
			':id' => $id
			));

		if (!$result)
			return null;

		$rows = $stmt_get_list_gudang->fetchAll(PDO::FETCH_ASSOC);

		// var_dump($rows);

		if (count($rows) < 1)
			return null;

		$gudang = array();
		foreach ($rows as $row) {
			$gudang[] = $row['gudang'];
		}
		return $gudang;
	}

	public function getAssignedLocation($id) {
		$qstring = "
			SELECT
				a.lokasi
			FROM
				status_pemeriksa a
			INNER JOIN
				(
				SELECT
					user_id,
					MAX(time) recent		
				FROM
					status_pemeriksa
				GROUP BY
					user_id
				) latest
				ON
					a.user_id = latest.user_id AND a.time = latest.recent
			WHERE
				a.`status` = 'BUSY'
				AND a.user_id = :userid
			";


		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array(
				':userid'	=> $id
				));

			// get result here
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// fetch semua lokasi ke dalam flat array
			$lokasi = array();

			foreach ($data as $row) {
				$lokasi[] = $row['lokasi'];
			}

			return $lokasi;
		} catch (PDOException $e) {
			return false;
		}

		return false;
	}

	public function isTimeout() {
		// not logged in is timed out
		if (!$this->isLoggedIn())
			return true;

		// logged in
		return $this->getData()['timeout'] >= time();
	}

	// fill session
	public function registerUserSession($loginData) {
		if (isset($loginData['id'])
			&& isset($loginData['username'])
			&& isset($loginData['fullname'])
			&& isset($loginData['role'])) {

			$_SESSION['loginData'] = $loginData;

			$_SESSION['loginData']['timeout'] = time() + USER_SESSION;
			return true;
		}

		return false;
	}

	// change password
	public function changePassword($uid, $oldPassword, $newPassword) {
		$qstring = "
			UPDATE
				user a
			SET
				a.password = MD5(:newpass)
			WHERE
				a.id = :userid
				AND a.password = MD5(:oldpass)
				AND a.active = 'Y'
			";

		try {
			$stmt = $this->db->prepare($qstring);

			$result = $stmt->execute(array(
				':userid' => $uid,
				':newpass' => $newPassword,
				':oldpass' => $oldPassword
				));
			// is the rowCount > 0?
			return $stmt->rowCount() > 0;
		} catch (PDOException $e) {
			return false;
		}
		return false;
	}

	// attempt to logout


	//attempt to login
	/*function tryLogin($uname, $pass){
		$qstring = "SELECT
						*,
						a.privilege+0 privCode
					FROM 
						USER a
					WHERE
						username = :uname 
						AND PASSWORD = SHA1(:pass);";
										
		$stmt = $this->db->prepare($qstring);
		$data = array(
			'uname'=>$uname,
			'pass'=>$pass
			);
		//there can be only one
		if($stmt->execute($data) && $stmt->rowCount()==1){
			//user was able to log in...gather some data here
			$_SESSION['user']['loginStatus'] = 1;	//login status
			$_SESSION['user']['timeOut'] = $this->getNewTimeOut();	//the timeout
			//fetch data
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			foreach($data as $k=>$v){
				$_SESSION['user'][$k]=$v;
			}
			return true;
		}
		return false;
	}	*/

	//attempt logout
	public function attemptLogout() {
		unset($_SESSION['loginData']);
		$_SESSION['loginData'] = array();
	}

	public function message($msg) {
		if(isset($msg)){
			$_SESSION['loginData']['message'] = $msg;
		}
	}

	public function getMessage() {
		$ret = $_SESSION['loginData']['message'];
		$_SESSION['loginData']['message'] = '';
		return $ret;
	}

	//check if user is logged in
	public function isLoggedIn() {
		//gotta refresh time here.....
		return isset($_SESSION['loginData']['id']);
	}

	//get important data
	public function getData() {
		return $_SESSION['loginData'];
	}

	// update session
	public function refreshDBSession($userid, $ipAddress) {
		$q_update_session = "
				UPDATE
					user_session a
				SET
					a.expire = DATEADD(NOW() INTERVAL 20 MINUTE)
				WHERE

			";
	}

	// refresh session
	public function refreshSession() {
		$_SESSION['loginData']['timeOut'] = time() + USER_SESSION;
	}

	//force redirect
	public function forceLogin(){
		if(!$this->isLoggedIn()) {
			header('Location: '.base_url('user/login'));
		} else {
			//refresh timer
			if ($_SESSION['loginData']['timeout'] < time()) {
				$this->attemptLogout();		//logout
				header('Location: '.base_url('user/login'));	//we've timeout, so force new login
			}
			else {
				$_SESSION['loginData']['timeout'] = time() + USER_SESSION;	//push timeout further
			}
		}
	}

	// get all registered gudang
	public function getAllRegisteredGudang() {
		$qstring = "
			SELECT
				a.gudang
			FROM
				grup_gudang a
			WHERE
				a.gudang <> 'BCSH';
		";

		try {
			$stmt = $this->db->prepare($qstring);

			$res = $stmt->execute();

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$retData = array();
			foreach ($data as $val) {
				$retData[] = $val['gudang'];
			}

			return $retData;
		} catch (PDOException $e) {
			return false;
		}
		return false;
	}

	// get all registered users
	public function getAllRegisteredUser() {
		$qstring = "
			SELECT
				a.id,
				a.username,
				a.fullname,
				a.role,
				a.active,
				a.role+0 role_code
			FROM
				user a;
			";

		$qgudang = "
			SELECT
				*
			FROM
				user_gudang_pair;
			";

		try {
			$stmt = $this->db->prepare($qstring);
			$res = $stmt->execute();

			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$indexed = array();
			// force to indexed perhaps
			foreach ($data as &$user) {
				$indexed[$user['id']] = $user;
				$indexed[$user['id']]['gudang'] = array();
			}

			// run second query
			$stmt2 = $this->db->prepare($qgudang);
			$res2 = $stmt2->execute();

			$data = $stmt2->fetchAll(PDO::FETCH_ASSOC);

			foreach ($data as &$pair) {
				$indexed[$pair['user_id']]['gudang'][] = $pair['gudang'];
			}

			// now flatten it out
			foreach ($indexed as &$user) {
				$user['gudang'] = implode(',', $user['gudang']);
			}

			return $indexed;
		} catch (PDOException $e) {
			return false;
		}
		return false;
	}


	public function getRegisteredData($id) {
		$quser = "
			SELECT
				a.id,
				a.username,
				a.fullname,
				a.role,
				a.active,
				a.role+0 role_code
			FROM
				user a
			WHERE
				a.id = :uid;";

		$qgudang = "
			SELECT
				b.gudang
			FROM
				user_gudang_pair b
			WHERE
				b.user_id = :uid;
			";

		try {
			$execData = array(':uid' => $id);

			$stmtUser = $this->db->prepare($quser);
			$res1 = $stmtUser->execute($execData);

			$data = $stmtUser->fetchAll(PDO::FETCH_ASSOC);

			// no data, return right ahead
			if (!count($data))
				return false;

			$retData = $data[0];
			$retData['gudang'] = array();
			$retData['role'] = explode(',', $retData['role']);

			// now second query
			$stmtGudang = $this->db->prepare($qgudang);
			$res2 = $stmtGudang->execute($execData);

			$data = $stmtGudang->fetchAll(PDO::FETCH_ASSOC);

			foreach ($data as $value) {
				$retData['gudang'][] = $value['gudang'];
			}
			// $retData['gudang'] = $data;

			return $retData;

		} catch (PDOException $e) {
			return false;
		}
		return false;
	}


	// buat nyimpen data user yang diubah
	// $user isinya:
	//	'id' => user id ybs
	//	'username' => username,
	//	'role'	=> role user dalam bentuk flat string 'PPJK,PJT'
	//	'fullname'	=> nama user lengkap
	// $gudang : array berisi list gudang yang dimiliki user (hanya PJT/PPJK yg wajib ngisi ini)
	public function save($user, $gudang) {
		$qupdateUser = "
			UPDATE
				user a
			SET
				a.username = :username,
				a.role = :role,
				a.active = :active,
				a.fullname = :fullname
			WHERE
				a.id = :id
			";

		$qdeleteGudang = "
			DELETE FROM
				user_gudang_pair
			WHERE
				user_id = :id
			";

		$qaddGudang = "
			INSERT INTO
				user_gudang_pair(user_id, gudang)
			VALUES
				(:id, :gudang)
			ON DUPLICATE KEY
				UPDATE user_id=user_id;
			";

		$this->db->beginTransaction();

		try {
			// first, update user
			$stmtUpdateUser = $this->db->prepare($qupdateUser);

			$res1 = $stmtUpdateUser->execute($user);

			// next, delete all gudang associated with this user
			$stmtDeleteGudang = $this->db->prepare($qdeleteGudang);

			$res2 = $stmtDeleteGudang->execute(array(
						':id' => $user[':id']
					));

			// next, add all gudang
			if (isset($gudang)) {
				if (is_array($gudang)) {
					$stmtAddGudang = $this->db->prepare($qaddGudang);

					foreach ($gudang as $kd_gudang) {
						$data = array(
							':id' => $user[':id'],
							':gudang' => $kd_gudang
							);
						$res3 = $stmtAddGudang->execute($data);
					}
				}
			}

			$this->db->commit();

			return true;
		} catch (Exception $e) {
			$this->db->rollback();

			$this->setLastError($e->getMessage());
		}
		return false;
	}

	/*
	This function resets password of a particular user
	$uid = user id that needs password reset
	$newPassword = new password
	*/
	public function resetPassword($uid, $newPassword = '123456') {
		// this resets user password temporarily

		$qupdateUser = "
			UPDATE
				user a
			SET
				a.password = md5(:password)
			WHERE
				a.id = :id;
			";

		try {
			$stmt = $this->db->prepare($qupdateUser);

			$res = $stmt->execute(array(
				':id' => $uid,
				':password' => $newPassword
				));
			return $stmt->rowCount() > 0;
		} catch (PDOException $e) {
			$this->setLastError($e->getMessage());
			return false;
		}

		return false;
	}

	/*
	This function deletes user referenced by $uid
	$uid = user that needs to be deleted
	*/
	public function delete($uid) {
		$qdelete = "
			DELETE FROM
				user
			WHERE
				id = :id
			LIMIT
				1
			";

		try {
			$stmtDelete = $this->db->prepare($qdelete);

			$res = $stmtDelete->execute(array(
				':id' => $uid
				));
			return $stmtDelete->rowCount() > 0;
		} catch (PDOException $e) {
			$code = $e->getCode();

			switch ($code) {
				case 23000:
					$this->setLastError("User dengan ID: $uid masih memiliki data di dalam database. Proses terpaksa dibatalkan");
					break;
				
				default:
					$this->setLastError($e->getCode());
					break;
			}

			
			return false;
		}
		return false;
	}

	/*
	Fungsi ini menambahkan user ke dalam database user
	$username => username utk login
	$password => password utk login
	$fullname => Nama Lengkap user
	$role => string berisi kombinasi privilege user , misal 'PPJK,PJT,ADMIN_PABEAN'
	$active => status akun user, misal perlu daftar user tapi dalam kondisi non-aktif
	$gudang => array berisi list gudang yang dihubungkan dengan user (utk PPJK/PJT saja)
	*/
	public function add($username, $password, $fullname, $role, $active, $gudang) {
		// gotta sanitize shit here
		if (strlen($username) < 4) {
			$this->setLastError("Username too short: minimum of 4 chars required");
			return false;
		}

		if (strlen($password) < 6) {
			$this->setLastError("Password too short: password minimum length is 4 chars");
			return false;
		}

		if (strlen($fullname) < 6) {
			$this->setLastError("Full name too short: ensure it's at least 6 chars");
			return false;
		}

		$qaddUser = "
			INSERT INTO 
				user(username, password, fullname, role, active)
			VALUES
				(
					:username, MD5(:password), :fullname, :role, :active
					);
			";

		$qlinkGudang = "
			INSERT INTO
				user_gudang_pair(user_id, gudang)
			VALUES
				(
					:id, :gudang
					)
			";

		// two tables involved, use TRANSACTION
		$this->db->beginTransaction();
		try {
			// 1. register akun user
			$stmtAddUser = $this->db->prepare($qaddUser);

			$res1 = $stmtAddUser->execute(array(
				':username' => $username,
				':password' => $password,
				':fullname' => $fullname,
				':role' => $role,
				':active' => $active
				));

			// 2. grab inserted user id
			$uid = $this->db->lastInsertId();

			// 3. add all gudang
			if (isset($gudang)) {
				if (is_array($gudang)) {
					if (count($gudang > 0)) {
						$stmtLinkGudang = $this->db->prepare($qlinkGudang);

						foreach ($gudang as $kd_gudang) {
							$res2 = $stmtLinkGudang->execute(array(
								':id' => $uid,
								':gudang' => $kd_gudang
								));
						}
					}
				}
			}
			

			// 4. last, commit transaction
			$this->db->commit();

			return $uid;
		} catch (PDOException $e) {
			// shit we failed, roll back transaction
			$this->db->rollback();
			// leave message for debugging
			if ($e->getCode() == 23000) {
				// double. Kasih pesen
				$this->setLastError("User '$username' sudah terdaftar" );
			} else
				$this->setLastError($e->getMessage());
			return false;
		}
		return false;
	}
}
?>