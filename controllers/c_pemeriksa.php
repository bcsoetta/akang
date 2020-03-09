<?php
class C_pemeriksa extends Base_Controller {

	public function __construct() {
		parent::__construct();

		//load all model used
		$this->load_model('user');
		$this->load_model('menu');
		$this->load_model('app');
		$this->load_model('pemeriksa');
	}

	// halaman ini buat ngabsen pemeriksa
	public function absen() {
		// gotta check user role + login state
		$this->user->forceLogin();

		// ensure role is admin_pabean or superuser
		if ( !($this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER)) ) {
			return forbid();
		}

		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Absen Pemeriksa)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		// data buat view request_pib
		$data['mainContent'] = $this->load_view('absen_pemeriksa', null, true);
		$this->load_view('index', $data);
	}

	// halaman untuk cetak bap
	public function cetakbap() {
		// gotta check user role + login state
		$this->user->forceLogin();

		if (!$this->user->hasRole(R_PEMERIKSA)) {
			return forbid();
		}

		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Cetak BAP)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		// Form cetak BAP?
		$uid = $this->user->getData()['id'];

		$formCariBap = $this->load_view('browse_bap', [
			'id_pemeriksa'	=> $uid,
			'listGudang'	=> $this->user->getAssignedLocation($uid)
		], true);

		$data['mainContent']	= $formCariBap;
		$this->load_view('index', $data);

	}

	// page ini merespon ajax request utk query data absen
	public function statusabsen($tanggal, $bulan, $tahun) {
		$this->user->forceLogin();

		if ( ! ($this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER) ) ) {
			return forbid();
		}

		if (!isset($tahun))
			$tahun = date('Y');
		if (!isset($bulan))
			$bulan = date('m');
		if (!isset($tanggal))
			$tanggal = date('d');

		// force two digit
		if (strlen($tanggal) == 1)
			$tanggal = '0'.$tanggal;
		if (strlen($bulan) == 1)
			$bulan = '0'.$bulan;

		// echo "Tgl absen. " . $tanggal . "-" . $bulan . "-" . $tahun . "<br/>";

		$absenPemeriksa = $this->pemeriksa->getAbsenPemeriksa($tanggal . '/' . $bulan . '/' . $tahun);

		// print_r($absenPemeriksa);
		// will it error?
		if (!$absenPemeriksa) {
			header('HTTP/1.0 500 Internal error. Dunno why.');
			die();
		}

		// good to go
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json; charset=UTF-8;');

		// just json the fuck out of it
		echo json_encode($absenPemeriksa);
	}

	// page ini merespon ajax request untuk nyimpen absen pemeriksa
	public function simpanabsen() {
		$this->user->forceLogin();

		if ( !($this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER) ) ) {
			return forbid();
		}
		// print_r($_POST);
		$tanggal = $_POST['tglAbsen'];
		$pemeriksa = $_POST['pemeriksa'];

		// var_dump($pemeriksa);

		// var_dump( $this->pemeriksa->simpanAbsenPemeriksa($tanggal, $pemeriksa) );
		$result = $this->pemeriksa->simpanAbsenPemeriksa($tanggal, $pemeriksa);

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json; charset=UTF-8;');

		echo json_encode(array(
			'result' => $result,
			'tglAbsen' => $tanggal
			));
	}

	// page ini buat mantau status pemeriksa
	public function status() {
		$this->user->forceLogin();

		if (! ($this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER))) {
			return forbid();
		}

		// aman, lanjut
		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Status Pemeriksa)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		// data buat view request_pib
		$data['mainContent'] = $this->load_view('status_pemeriksa', null, true);
		$this->load_view('index', $data);
	}

	// page ini ajax response buat ambil status pemeriksa
	public function querystatus() {
		$this->user->forceLogin();

		if (! ($this->user->hasRole(R_SUPERUSER) || $this->user->hasRole(R_ADMIN_PABEAN) ))
			return forbid();

		$data = $this->pemeriksa->getStatusPemeriksa();
		$gudang = $this->app->getActiveWarehouse();

		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json;');
		echo json_encode(array(
			'gudang' => $gudang,
			'pemeriksa' => $data
			));
	}

	// page ini ajax response buat ngeluarin bap
	public function querynonbap() {
		// 
	}

	// page ini ajax response buat simpan status penugasan pemeriksa
	public function simpanstatus() {
		$this->user->forceLogin();

		if (! ($this->user->hasRole(R_SUPERUSER) || $this->user->hasRole(R_ADMIN_PABEAN) ) ) {
			return forbid();
		}
		// print_r($_POST);

		// header('Content-Type: application/json;');
		// echo json_encode($_POST);

		// olah dulu
		// $queryData = $this->pemeriksa->buildQueryData($_POST['pemeriksa']);

		// echo json_encode($queryData);

		// test
		// ada data yg dikirim?
		if (!isset($_POST['pemeriksa'])) {
			header('HTTP/1.0 500 No data sent. Foolish.');
			die();
		}

		// ambil status skrg
		$currStatus = $this->pemeriksa->getStatusPemeriksa();

		// simpan yang berubah saja
		$delta = $this->pemeriksa->getStatusDifference($_POST['pemeriksa'], $currStatus);

		$result = $this->pemeriksa->simpanStatusPemeriksa($delta);

		// $retData = array(
		// 	'currentStatus' => $currStatus,
		// 	'newStatus' => $_POST['pemeriksa'],
		// 	'difference' => $delta
		// 	);

		header('Content-Type: application/json;');
		// echo json_encode($retData);

		echo json_encode($result);
	}

	// test
	public function test() {
		$listLokasi = $this->user->getAssignedLocation(
			$this->user->getData()['id']
			);
		print_r($listLokasi);

		echo '<br><br>';

		$listDokumen = $this->pemeriksa->getDokumenOutstanding('CN_PIBK', $listLokasi);
		print_r($listDokumen);
	}

	/*
	This page is AJAX-Response, output is data of available inspector
	in JSON format
	*/
	public function available($role) {
		$this->user->forceLogin();

		if (! ($this->user->hasRole(R_SUPERUSER) || $this->user->hasRole(R_ADMIN_PABEAN) ) ) {
			return forbid();
		}

		// echo $role;

		// aman, lanjut
		$roleCode = ($role == 'CARNET' ? pemeriksa::ROLE_CARNET : ($role == 'CN_PIBK' ? pemeriksa::ROLE_CNPIBK : pemeriksa::ROLE_ALL) );

		$data = $this->pemeriksa->getPemeriksa($roleCode, pemeriksa::STATUS_AVAILABLE);

		// print_r($roleCode);

		header('Access-Control-Allow-Origin: *');

		$retData = array();

		if ($data === false) {
			// ada kesalahan, cek lagi
			$retData['error'] = $this->pemeriksa->getLastError();
		} else if ($data) {
			// normal
			$retData = $data;
		} else {
			// gk ada pemeriksa
			$retData['error'] = 'Gk ada pemeriksa yg siap ndan.';
		}

		header('Content-Type: application/json; charset=UTF-8;');
		echo json_encode($retData);
	}

	/*
	This page is AJAX-Response, output is data of current assignment of inspectors,
	their whereabout, time of assignment
	in JSON format
	*/
	public function penugasan() {
		// print_r($_POST);
		if (!$this->user->isLoggedIn())
			return forbid();

		// gotta have specific role
		if (! ($this->user->hasRole(R_ADMIN_PABEAN) || $this->user->hasRole(R_SUPERUSER)) )
			return forbid();

		// CORS header
		header('Access-Control-Allow-Origin: *;');

		if (isset($_POST['lokasi']) && isset($_POST['pemeriksa'])) {
			// ada pemeriksa + lokasi, sip dah
			$lokasi = $_POST['lokasi'];
			$pemeriksa = $_POST['pemeriksa'];

			// pecah jadi array
			$lokasi = explode(',', $lokasi);

			// bikin status data
			$statusData = array();

			foreach ($pemeriksa as $id) {
				$statusData[$id] = array(
					'status' => 'BUSY',
					'lokasi' => $lokasi
					);
			}

			// now save it
			$result = $this->pemeriksa->simpanStatusPemeriksa($statusData);

			header('Content-Type: application/json;');
			if ($result) {
				// echo "Sukses menyimpan status pemeriksa!";
				
				echo json_encode(true);

				die();
			} else {
				echo json_encode(array(
					'error' => $this->pemeriksa->getLastError()
					));
				die();
			}
		}

		header("HTTP/1.0 500 Wah gagal kirim bos! reason: kesalahan dalam parameter" );
	}

	// page ini menampilkan list pemeriksa aktif di gudang tsb
	public function aktif() {
		$this->user->forceLogin();

		if (!$this->user->hasRole(R_SUPERUSER|R_ADMIN_PABEAN|R_PPJK|R_PJT))
			return forbid();


		// aman, lanjut
		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Browse Pemeriksa Aktif per Gudang)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		// data buat view request_pib
		$data['mainContent'] = 'some shit here';
		$this->load_view('index', $data);
	}

	public function busy($lokasi) {
		$this->user->forceLogin();

		// CORS Header
		header('Access-Control-Allow-Origin: *');

		$listLokasi = pemeriksa::LOKASI_ALL;

		if (isset($lokasi))
			$listLokasi = explode(',', $lokasi);

		// grab status
		$data = $this->pemeriksa->getPemeriksa(pemeriksa::ROLE_ALL, pemeriksa::STATUS_BUSY, $listLokasi);

		// print_r($data);
		header('Content-Type: application/json;');
		echo json_encode($data);
	}

	// page ini utk menampilkan list barang siap periksa
	// UNTUK PEMERIKSA ONLY
	public function listbarang() {
		$this->user->forceLogin();

		if (!$this->user->hasRole(R_PEMERIKSA))
			return forbid();

		// aman, lanjut
		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Browse Outstanding PIB/CN/PIBK)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		// data buat view list dokumen
		$uid = $this->user->getData()['id'];

		$listDokumen = array(
			'listLokasi'	=> $this->user->getAssignedLocation($uid),
			'role'			=> $this->pemeriksa->getAbsensi($uid)['status']
			);

		$data['mainContent'] = $this->load_view('list_dokumen', $listDokumen, true);
		$this->load_view('index', $data);
	}

	public function getdokumen() {
		$this->user->forceLogin();

		if (!$this->user->hasRole(R_PEMERIKSA))
			return forbid();

		// let's limit user's visibility
		$doctype = $_POST['doctype'];
		$lokasi = $_POST['lokasi'];
		$status = $_POST['status'];

		// absen
		$absensi = $this->pemeriksa->getAbsensi($this->user->getData()['id']);
		if (!in_array($doctype, $absensi['status']))
			return forbid();

		// print_r($absensi);

		// echo '<br><br>';

		// print_r($_POST);

		$dokumen = $this->pemeriksa->getDokumenOutstanding($doctype, $lokasi, $status);
		// echo '<br><br>';

		// print_r($dokumen);

		$retData = array(
			'doctype'	=> $doctype,
			'lokasi'	=> $lokasi,
			'dokumen'	=> $dokumen
			);

		// CORS HEADER
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json; charset=UTF-8;');
		echo json_encode($retData);
	}


	// buat ngeflag dokumen selesai
	//	$status : {SELESAI, BATAL, ON_PROCESS}
	//	$id : {id dokumen, atau kosongin tp set $_POST['dokumen'] ke array berisi list dokumen}
	public function flag($status, $id, $catatan) {
		$this->user->forceLogin();

		if (! ($this->user->hasRole(R_PEMERIKSA) || $this->user->hasRole(R_SUPERUSER)) )
			return forbid();

		$flag = '';

		// mungkin via post
		if (isset($_POST['status']))
			$status = $_POST['status'];
		else if (isset($_GET['status']))
			$status = $_GET['status'];

		switch ($status) {
			case 'selesai':
				$flag = 'FINISHED';
				break;
			case 'batal':
				$flag = 'CANCELED';
				break;
			case 'proses':
				$flag = 'ON_PROCESS';
				break;
			case 'overtime':
				$flag = 'OVERTIME';
				break;
			case 'tidaksesuai':
				$flag = 'INCONSISTENT';
				break;
			default:
				$flag = '';
				break;
		}

		$execData = array(
			'userid'	=> $this->user->getData()['id'],
			'flag'		=> $flag,
			'catatan'	=> ''
			);

		// ambil id
		if (isset($id)) {
			// echo "mencoba ngeflag dokumen: " . $id. " menjadi: " . $flag;
			// die();
			$execData['docid']	= array($id);
		}

		// kagak ada id, ambil data array
		if (isset($_POST['dokumen']))
			$ids = $_POST['dokumen'];
		else if (isset($_GET['dokumen']))
			$ids = $_GET['dokumen'];
		else 
			$ids = null;

		if (isset($ids)) {
			// echo "massive flagging to: " . $status. '<br>';
			// print_r($ids);
			// die();
			$execData['docid']	= $ids;
		}

		// mungkin ada catatan
		if (isset($catatan))
			$execData['catatan'] = urldecode($catatan);
		else if (isset($_POST['catatan']))
			$execData['catatan'] = urldecode($_POST['catatan']);
		else if (isset($_GET['catatan']))
			$execData['catatan'] = urldecode($_GET['catatan']);

		$result = $this->app->flagDokumen(
			$execData['userid'],
			$execData['flag'],
			$execData['docid'],
			$execData['catatan']
			);

		// Cors header
		// header('Access-Control-Allow-Origin: *');
		if (!$result) {
			header('HTTP/1.0 500 eror ngeflag dokumen. Lapor duknis plis. ' . $this->app->getLastError());
			die();
		}

		// normal case
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json;');

		echo json_encode($result);
	}

	public function updatestatus($status) {
		$this->user->forceLogin();

		if (! ($this->user->hasRole(R_PEMERIKSA) || $this->user->hasRole(R_SUPERUSER)) )
			return forbid();

		// prepare query
		$qstring = "INSERT INTO
						status_pemeriksa (
							user_id,
							status,
							lokasi
						)
					VALUES (
						:uid,
						:status,
						:lokasi
					);";

		// prepare parameter
		if ($status == 'available') {
			$param = array(
				':uid'	=> $this->user->getData()['id'],
				':status'	=> 'AVAILABLE',
				':lokasi'	=> 'BCSH'
			);
		} else if ($status == 'non-aktif') {
			$param = array(
				':uid'	=> $this->user->getData()['id'],
				':status'	=> 'BUSY',
				':lokasi'	=> 'BCSH'
			);
		} else {
			// set data
			$title = $this->app->getTitle() . ' - Update status';
			$msg = 'Gagal mengupdate status...kontak Duknis plis';

			// gunakan halaman redirection
			$data = array(
				'pagetitle' => $title,
				'message' => $msg,
				'seconds' => 3,
				'targetName' => 'Halaman sebelumnya',
				'target' => base_url('')
				);

			$this->load_view('message_redirect', $data, false);
			die();
		}

		// // header('location: ' . base_url(''));
		// print_r($param);

		$statusData = array(
			$this->user->getData()['id']	=> array('status' => strtoupper($status) )
			);

		$result = $this->pemeriksa->simpanStatusPemeriksa($statusData);

		// now we redirect
		if ($result) {
			header('location: ' . base_url(''));	
			// echo "Berhasilkah? " . $result;
		} else {
			// set data
			$title = $this->app->getTitle() . ' - Update status';
			$msg = 'Gagal mengupdate status ('.$this->pemeriksa->getLastError().')...kontak Duknis plis';

			// gunakan halaman redirection
			$data = array(
				'pagetitle' => $title,
				'message' => $msg,
				'seconds' => 3,
				'targetName' => 'Halaman sebelumnya',
				'target' => base_url('')
				);

			$this->load_view('message_redirect', $data, false);
			die();
		}
		
	}

	// untuk melihat performa pemeriksa
	public function performa() {
		$this->user->forceLogin();

		if (!$this->user->hasRole(R_ADMIN_PABEAN))
			return forbid();


		$searchParam = array();

		// save param if ever saved
		if (isset($_POST['datestart']))
			$searchParam['datestart'] = trim(stripslashes($_POST['datestart']));		// tanggal mulai
		else
			$searchParam['datestart'] = date('d/m/Y');

		if (isset($_POST['dateend']))
			$searchParam['dateend'] = trim(stripslashes($_POST['dateend']));			// tanggal selesai
		else
			$searchParam['dateend'] = date('d/m/Y');

		if (isset($_POST['selectedPemeriksa']))
			$searchParam['selectedPemeriksa'] = $_POST['selectedPemeriksa'];			// pemeriksa yang dipilih (list ID)
		else
			$searchParam['selectedPemeriksa'] = array();

		if (isset($_POST['doctype']))
			$searchParam['doctype'] = $_POST['doctype'];								// jenis dokumen yang dicari
		else
			$searchParam['doctype'] = array();

		$searchParam['listPemeriksa'] = $this->pemeriksa->getListPemeriksa(true);


		// data utk browse
		// 1. list pemeriksa
		// 2. range tanggal utk cek performa
		// 3. detil dokumen apa aja yg diperiksa? (JENIS, Jumlah, etc)
		$performData = array();

		$performData['searchParam'] = $searchParam;

		$performData['searchResult'] = $this->pemeriksa->queryPerforma(
			$searchParam['datestart'],
			$searchParam['dateend'],
			$searchParam['selectedPemeriksa']
		);

		// data utk halaman utama
		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Performa Pemeriksa PIB/CN/PIBK)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$data['mainContent'] = $this->load_view('performa_pemeriksa', $performData, true);
		$this->load_view('index', $data);
	}

	// untuk melihat record list dokumen yang diperiksa
	public function record($pemId, $tglPeriksa, $gudang, $doctype, $namaPemeriksa, $status) {
		$this->user->forceLogin();

		$data = array();
		$data['pagetitle'] = $this->app->getTitle().' (Record ' . $namaPemeriksa . '@' . $tglPeriksa . '@' . $gudang . ')';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		$recordData = array(
			'data' => $this->pemeriksa->queryRecordPemeriksaan($pemId, $tglPeriksa, $gudang, $doctype, $status),
			'fullname' => $namaPemeriksa,
			'doctype' => $doctype,
			'tglPeriksa' => $tglPeriksa,
			'gudang' => $gudang
		);

		$data['mainContent'] = $this->load_view('record_pemeriksa', $recordData, true);

		$this->load_view('index', $data);
	}
}
?>