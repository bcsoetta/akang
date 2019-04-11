<?php
class C_app extends Base_Controller{
	public function __construct(){
		parent::__construct();	//call parent's ctor
		//load all model used
		$this->load_model('user');
		$this->load_model('menu');
		$this->load_model('app');
	}
	
	// halaman request pib
	function request($doctype) {
		// gotta check user role + login state
		$this->user->forceLogin();

		// ensure role
		if (!$this->user->hasRole(R_PPJK) && $doctype == 'PIB') {
			return forbid();
		}

		if (!$this->user->hasRole(R_PJT) && $doctype == 'CN_PIBK') {
			return forbid();
		}

		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Request Pemeriksaan Fisik '. ($doctype == 'CN_PIBK' ? 'CN/PIBK' : 'CARNET') .')';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		// data buat view request_pib
		$requestData = array(
			'maxFileSize' 	=> 1024*1024*8,	// max 8 Mb
			'listGudang' 	=> $this->user->getListGudang( $this->user->getData()['id'] ),
			'docType'		=> $doctype
			);

		$data['mainContent'] = $this->load_view('request_pib', $requestData, true);
		$this->load_view('index', $data);
	}

	function parseinput() {
		// error codes
		$errCodes = array(
			0 => "No Error. Proceed with caution.",
			1 => "Server thinks it's too big.",
			2 => "Obviously either the file is too big or you're too dumb.",
			3 => "Partial upload happening, dunno why",
			4 => "No file uploaded. What a waste of time",
			6 => "Server error. Contact Duknis Soetta please.",
			7 => "Shit I can't write",
			8 => "Whoa dude, this is serious shit!!"
			);

		$allowedMIME = array(
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.ms-excel'
			);

		// CORS header
		header("Access-Control-Allow-Origin: *");

		
		// logic parsing excel input
		//-cek ada ga inputnya
		//--klo ada, 
		//---cek ada error ga uploadnya
		//---cek mime typenya
		//---proses file/error
		//--klo gk, 
		//---error

		// First check : MAKE SURE THE KEY AND THE FILE EXIST!!
		if (!isset($_FILES['inputFile'])) {
			header('HTTP/1.0 500 The form did not send the input file!');
			return;
		} else if (count($_FILES['inputFile']) < 1) {
			header('HTTP/1.0 600 The form send something, but it aint the input file');
			return;
		} 

		// Second check : MAKE SURE THERE'S NO ERROR!!
		$code = $_FILES['inputFile']['error'];

		if ($code != UPLOAD_ERR_OK) {
			// something's wrong
			header('HTTP/1.0 400 '.$errCodes[$code]);
			die();
		}

		// Third check : MAKE SURE MIME TYPE IS IN ALLOWED MIME TYPE
		/*
		if (!in_array($_FILES['inputFile']['type'], $allowedMIME)) {
			header("HTTP/1.0 400 You're trying to upload unsupported file format, pal. => " . $_FILES['inputFile']['type']);
			die();
		}*/

		// It's safe now, start to load data
		$this->load_model('excel');

		// $data = $this->excel->loadRawData($_FILES['inputFile']['tmp_file']);
		$retData = $this->excel->readRawData($_FILES['inputFile']['tmp_name']);

		if (!$retData) {
			header("HTTP/1.0 400 For some reason, the file was not readable. Check your files, dude!");
			die();
		}

		// Success!!! Return as JSON
		header('Content-Type: application/json; charset=UTF-8');

		// $parsedData = $this->excel->parsePIBRequest($data);
		echo json_encode($this->excel->parsePIBRequest($retData['data'], $retData['type']));
	}

	function test() {
		$this->app->testGarbage(1);
		$this->app->testGarbage(31);
		$this->app->testGarbage(null);
	}

	// this function receive the submitted form through ajax
	// return the usable state
	function processrequest($doctype) {
		// $this->load_model('app');
		// CORS header
		header("Access-Control-Allow-Origin: *");
		header('Content-Type: application/json; charset=UTF-8');


		$returnData = array(
			'success' => false,
			'error' => 'Invalid request type',
			'msg' => ''
			);

		// check shit
		if (
			!isset($_POST['no_pib'])
			|| !isset($_POST['tgl_pib'])
			|| !isset($_POST['importir'])
			) {

			$returnData['error'] = "You sent an empty form!!";
			echo json_encode($returnData);
			die();
		}

		// header('Content-Type: application/json');
		if ($doctype == 'CARNET') {
			$data = array(
			'uploader_id'	=> $this->user->getData()['id'],
			'gudang'		=> htmlentities(trim($_POST['lokasi'])),
			'doctype'		=> $doctype,
			'no_pib'		=> $_POST['no_pib'],
			'tgl_pib'		=> $_POST['tgl_pib'],
			'importir'		=> $_POST['importir'],
			'jml_item'		=> $_POST['jml_item'],
			'berat_kg'		=> $_POST['berat_kg']
			// 'img_name'		=> $_POST['img_name'],
			// 'img_src'		=> $_POST['img_src']
			);
			// echo json_encode($bulk);

			$result = $this->app->submitRequest(
				$data['uploader_id'],
				$data['gudang'],
				$data['doctype'],
				$data['no_pib'],
				$data['tgl_pib'],
				$data['importir'],
				$data['jml_item'],
				$data['berat_kg']
				// $data['img_name'],
				// $data['img_src']
				);

			if ($result) {
				// echo ;

				$returnData['success'] = true;
				$returnData['error'] = '';
				$returnData['msg'] = "data CARNET submitted with batch id #".$result;
				$returnData['redirect'] = base_url('app/viewbatch/'.$result);
			} else {
				// echo ;
				$returnData['success'] = false;
				$returnData['error'] = "CARNET request failed. reason: " . $this->app->getLastError();
				$returnData['msg'] = '';
			}

		} else if ($doctype == 'CN_PIBK') {
			if (!isset($_POST['lokasi'])) {
				$_POST['lokasi'] = '';
			}

			$data = array(
			'uploader_id'	=> $this->user->getData()['id'],
			'gudang'		=> htmlentities(trim($_POST['lokasi'])),
			'doctype'		=> $doctype,
			'no_pib'		=> $_POST['no_pib'],
			'tgl_pib'		=> $_POST['tgl_pib'],
			'importir'		=> $_POST['importir'],
			'jml_item'		=> $_POST['jml_item'],
			'berat_kg'		=> $_POST['berat_kg']
			);

			$result = $this->app->submitRequest(
				$data['uploader_id'],
				$data['gudang'],
				$data['doctype'],
				$data['no_pib'],
				$data['tgl_pib'],
				$data['importir'],
				$data['jml_item'],
				$data['berat_kg']
				) && ($data['gudang'] != '' );

			if ($result) {
				// echo "data CN/PIBK submitted with batch id #".$result;
				$returnData['success'] = true;
				$returnData['error'] = '';
				$returnData['msg'] = "data CN/PIBK submitted with batch id #".$result;
				$returnData['redirect'] = base_url('app/viewbatch/'.$result);
			} else {
				// echo "CN/PIBK failed. reason: " . $this->app->getLastError();
				$returnData['success'] = false;
				$returnData['error'] = "CN/PIBK request failed. reason: " . $this->app->getLastError();
				$returnData['msg'] = '';
			}
		} 

		

		echo json_encode($returnData);
		
		/*header('Content-Type: application/json;');
		echo json_encode($_POST);*/


	}

	function testdata($id) {
		$batchData = $this->app->getBatchData($id);

		print_r($batchData);
	}


	// this page views the batch with corresponding id
	function viewbatch($batch_id) {
		// make sure user's logged in
		$this->user->forceLogin();

		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Viewing Batch #'. $batch_id .')';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$batchData = $this->app->getBatchData($batch_id);
		
		if ($batchData) {
			$data['mainContent'] = $this->load_view('view_batch', $batchData, true);
		} else {
			$data['mainContent'] = "No batch data found for batch #" . $batch_id;
		}

		// show it
		$this->load_view('index', $data);

		
	}

	// this page browse request sent by ppjk/pjt
	private function browserequest() {
		// gotta make sure user's logged in
		$this->user->forceLogin();

		$data = array();
		$data['pagetitle'] = $this->app->getTitle() . ' Browse Permohonan Pemeriksaan Fisik';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$browseData = null;
		if ( $this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER) ) {
			$browseData = array('adminMode'=>true);
		}

		$data['mainContent'] = $this->load_view('browse_request', $browseData, true);

		$this->load_view('index', $data);
	}

	// this page browse outstanding per gudang
	// tampilkan info per gudang (jml dok outstanding, pemeriksa aktif)
	private function browseoutstanding() {
		// gotta make sure user's logged in
		$this->user->forceLogin();

		$data = array();
		$data['pagetitle'] = $this->app->getTitle() . ' Browse Data per Gudang';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$browseData = array(
			'listGudang' => ($this->user->hasRole(R_SUPERUSER) || $this->user->hasRole(R_ADMIN_PABEAN) ? $this->app->getActiveWarehouse() : $this->user->getListGudang($this->user->getData()['id']) ),
			'isAuthorized' => $this->user->hasRole(R_SUPERUSER) || $this->user->hasRole(R_ADMIN_PABEAN)
			);

		$data['mainContent'] = $this->load_view('browse_outstanding', $browseData, true);

		$this->load_view('index', $data);
	}

	// this page browse by awb
	private function browseawb() {
		// gotta make sure user's logged in
		$this->user->forceLogin();

		$data = array();
		$data['pagetitle'] = $this->app->getTitle() . ' Browse Data per AWB';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));		

		// here be custom view
		$browseData = array(
			'adminMode'	=> false
		);

		// is it submitted?
		if (isset($_GET['hawb'])) {
			$browseData['hawb'] = stripslashes(trim($_GET['hawb']));
			$browseData['result'] = $this->app->queryStatusAWB($browseData['hawb']);

			if ($this->user->getData()['role_code'] & R_PEMERIKSA) {
				// activate special menu and
				// if last response is not finished, 
				$respCount = count($browseData['result']['response']);

				// is pemeriksa
				$browseData['adminMode'] = true;

				if ($respCount > 0) {
					if ($browseData['result']['response'][$respCount-1]['status'] != 'FINISHED') {
						$browseData['canFinish'] = true;

						if ($browseData['result']['response'][$respCount-1]['status'] != 'OVERTIME')
							$browseData['canOvertime'] = true;
					}
				}


			}
		}


		$data['mainContent'] = $this->load_view('browse_awb', $browseData, true);

		$this->load_view('index', $data);
	}

	// this page browse stuffs
	function browse($data) {
		if ($data == 'request') {
			// browse for request data
			$this->browserequest();
			die();
		} else if ($data == 'outstanding') {
			// browse for outstanding per gudang
			$this->browseoutstanding();
			die();
		} else if ($data == 'awb') {
			$this->browseawb();
			die();
		} else {
			// shit, forbid it
			return forbid();
		}
	}

	// this AJAX RESPONSE PAGE is responsible to
	// process the query
	function query($data) {
		if ($this->user->isLoggedIn())
			$this->user->refreshSession();
		// this shall accept CORS
		header('Access-Control-Allow-Origin: *');

		if ($data == 'request') {
			return $this->queryrequest();
		} else if ($data == 'outstanding') {
			return $this->queryoutstanding();
		} else if ($data == 'activewarehouse') {
			return $this->queryactivewarehouse();
		} else if ($data == 'awb') {
			return $this->queryawb();
		}

		return forbid();
	}

	// return data queried by awb
	function queryawb() {
		// grab parameter by GET
		if (isset($_GET['hawb'])) {
			// grab result
			$input = trim($_GET['hawb']);

			// dummy test
			$data = array(
					'result' => $this->app->queryListAWB($input),
					'status' => 'success',
					'queryString' => $input
				);

			// test
			// sleep(1);

			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			echo json_encode($data);
		}

		die();
	}

	function queryrequest() {
		// echo json_encode($_POST);
		// just sanitize shit here?
		// also fill in default values
		$searchParam = array(
			'datestart' => isset($_POST['datestart']) ? htmlentities(trim($_POST['datestart'])) : date('d/m/Y'),
			'dateend' => isset($_POST['dateend']) ? htmlentities(trim($_POST['dateend'])) : date('d/m/Y'),
			'paramtype' => isset($_POST['paramtype']) ? htmlentities(trim($_POST['paramtype'])) : null,
			'paramvalue' => isset($_POST['paramvalue']) ? htmlentities(trim($_POST['paramvalue'])) : null,
			'doctype' => isset($_POST['doctype']) ? htmlentities(trim($_POST['doctype'])) : 'ALL',
			'pageid' => isset($_POST['pageid']) ? htmlentities(trim($_POST['pageid'])) : 1,
			'itemperpage' => isset($_POST['itemperpage']) ? htmlentities(trim($_POST['itemperpage'])) : 3	// defaults to 10 item per page
			);

		// force id?
		if (!($this->user->hasRole(R_PEMERIKSA) || $this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER)) )
			$searchParam['forceid'] = $this->user->getData()['id'];

		$data = $this->app->queryRequest($searchParam);

		if ($data === false) {
			header('HTTP/1.0 400 '.$this->app->getLastError());
			die();
		}

		// perhaps no data?
		if (is_null($data)) {
			header('Content-Type: application/json; charset=UTF-8');
			echo json_encode(array(
				'error' => 'No data found. Tough luck, pal'
				));
			die();
		} else if (is_array($data)) {
			// some good shit
			header('Content-Type: application/json; charset=UTF-8');
			echo json_encode($data);
			die();
		} else {
			header('HTTP/1.0 500 Internal server error, I suppose.');
		}
	}

	function queryoutstanding() {
		header('Content-Type: application/json;');
		
		$listGudang = $_POST['gudang'];

		$data = $this->app->queryOutstanding($listGudang);

		if ($data) {
			header('Content-Type: application/json; charset=UTF-8;');

			// tambahin ah
			$this->load_model('pemeriksa');

			$dataGudang = $this->pemeriksa->getPemeriksa(pemeriksa::ROLE_ALL, pemeriksa::STATUS_BUSY, pemeriksa::LOKASI_ALL);

			// let's fix it
			foreach ($data as $gudang => &$value) {
				if (isset($dataGudang['gudang'][$gudang]['PIB']))
					$value['PIB']['pemeriksa_aktif'] = count($dataGudang['gudang'][$gudang]['PIB']);

				if (isset($dataGudang['gudang'][$gudang]['CN_PIBK']))
					$value['CN_PIBK']['pemeriksa_aktif'] = count($dataGudang['gudang'][$gudang]['CN_PIBK']);				

				if (isset($dataGudang['gudang'][$gudang]['CARNET']))
					$value['CARNET']['pemeriksa_aktif'] = count($dataGudang['gudang'][$gudang]['CARNET']);
			}

			echo json_encode($data);
			die();
		} 

		// nyampe sni brati eror
		header('HTTP/1.0 404 Data outstanding tidak ditemukan');
	}

	function queryactivewarehouse() {
		// no parameter needed, just list the shiets
		$data = $this->app->queryOutstanding(array('GBDL', 'GIMP'));

		if ($data) {
			header('Content-Type: application/json;');
			echo json_encode($data);
		}
		else {
			echo $this->app->getLastError();
		}
		// if ($data) {
		// 	header('Content-Type: application/json; charset=UTF-8;');
		// 	echo json_encode($data);
		// 	die();
		// }

		// // we reach here, error then
		// header('HTTP/1.0 500 Dunno why but error happens');
	}

	function echoresponse() {
		// echo all get and post
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json;');

		echo json_encode(array(
			'GET' => $_GET,
			'POST' => $_POST,
			'FILES' => $_FILES
			));
	}

	/*
	Fungsi proxy aja, biar urlnya ciamik :()
	*/
	public function manage($type,$id) {
		if ($type == 'user')
			return $this->manageuser($id);
		else if ($type == 'gudang')
			return $this->managegudang($id);

		return forbid();
	}

	public function manageuser($id) {
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

		
		if (!isset($id)) {
			// show list of all users
			$data['mainContent'] = $this->load_view('user_list', 
									array(
										'userlist' => $this->user->getAllRegisteredUser()
									), true);
		}
		else {
			// show data about particular user only
			$data['mainContent'] = $this->load_view('user_detail',
									array(
										'userdata' => $this->user->getRegisteredData($id),
										'listGudang' => $this->user->getAllRegisteredGudang()
									),
									true);
		}

		$this->load_view('index', $data);
	}

	public function managegudang($id) {
		echo "To be constructed...";
	}
}
?>