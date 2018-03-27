<?php
/*
	Model: menu
	berisi modul terkait menu sesuai dengan privilege user
*/


class menu extends Base_Model {

	// all other menu extend from here
	static $baseMenu = array(
		0 => array('title'=> 'Home', 'parentId'=>null, 'style'=>'normal', 'target'=> ''),
		1 => array('title'=> 'User', 'parentId'=>null, 'style'=>'right'),
			2 => array('title'=> 'Profile', 'parentId'=>1, 'target'=> 'user/profile/'),
			3 => array('title'=> 'Change Password', 'parentId'=>1, 'target'=> 'user/changepass/')
		);

	private $menuItems = array();
	
	function __construct() {
		parent::__construct();
		// lazy start session
		if (!isset($_SESSION)) {
			global $session;
			session_name($session['name']);
			session_start();
		}
	}

	function clearMenu() {
		$this->menuItems = array();
	}

	function addMenuItem($title, $parentId, $style, $target) {
		$menuItem = array();

		if (isset($target))
			$menuItem['target'] = $target;

		if (isset($style))
			$menuItem['style'] = $style;

		if (isset($title))
			$menuItem['title'] = $title;

		if (isset($parentId))
			$menuItem['parentId'] = $parentId;
		
		// act as root
		$lastItemId	= count($this->menuItems);
		$this->menuItems[]	= $menuItem;

		return $lastItemId;
	}

	function setupMenuHierarchy() {
		$totalItems = count($this->menuItems);

		// loop backward, removing all details one by one
		for ($i = $totalItems-1; $i >= 0; --$i) {
			$mi = $this->menuItems[$i];

			if (isset($mi['parentId'])) {
				if (isset($this->menuItems[$mi['parentId']])) {
					// and set the parent's child relation
					// 	make sure it has children's room
					if (!isset($this->menuItems[$mi['parentId']]['child']))
						$this->menuItems[$mi['parentId']]['child']	= array();
					//	set parent of this item
					$this->menuItems[$mi['parentId']]['child'][$i]	= $mi;
					// remove it
					unset($this->menuItems[$i]);
					// sort parent's child
					ksort($this->menuItems[$mi['parentId']]['child']);
				}
			}
		}
	}

	/*
	0 => array('title'=> 'Home', 'parentId'=>null, 'style'=>'normal', 'target'=> ''),
	1 => array('title'=> 'User', 'parentId'=>null, 'style'=>'right'),
		2 => array('title'=> 'Profile', 'parentId'=>1, 'target'=> 'user/profile/'),
		3 => array('title'=> 'Change Password', 'parentId'=>1, 'target'=> 'user/changepass/')
	*/

	/*
	Fungsi ini adalah fungsi utama untuk menggenerate menu
	*/

	function generateMenuScheme($userId, $role) {
		$this->clearMenu();

		// root menu HOME
		$mn_home = $this->addMenuItem('Home', null, 'normal', '');

		// root menu USER
		$mn_user = $this->addMenuItem('User', null, 'right', null);
		// var_dump($mn_user);
			// $mn_user_profile	= $this->addMenuItem('Profile', $mn_user, null, 'user/profile');
			$mn_user_changepass	= $this->addMenuItem('Change Password', $mn_user, null, 'user/changepass');

			// $mn_manual = $this->addMenuItem('Manual', $mn_user, null, 'user/manual');

		if ($role & (R_SUPERUSER|R_ADMIN_PABEAN|R_PPJK|R_PJT))
			$mn_contoh_file = $this->addMenuItem('Contoh File Upload', $mn_user, null, 'assets/dok_pengajuan.xls');


		// menu umum browse
		// hanya utk PPJK, PJT, ADMIN, ADMIN_PABEAN, PEMERIKSA
		if ($role & (R_PPJK|R_PJT|R_ADMIN_PABEAN|R_SUPERUSER|R_PEMERIKSA)) {
			$mn_browse = $this->addMenuItem('Browse', null, 'normal', null);

				// for browsing request. 
				if ($role & (R_PPJK|R_PJT) )
					$mn_browse_request = $this->addMenuItem('Permohonan Pemeriksaan Fisik', $mn_browse, null, 'app/browse/request');

				// browse outstanding buat smua
				if ($role & (R_SUPERUSER|R_ADMIN_PABEAN|R_PPJK|R_PJT)) {
					$mn_browse_outstanding = $this->addMenuItem('Outstanding Per Gudang', $mn_browse, null, 'app/browse/outstanding');
				}

				// // browse pemeriksa aktif buat admin+pengguna jasa
				// if ($role & (R_SUPERUSER|R_ADMIN_PABEAN|R_PPJK|R_PJT)) {
				// 	$mn_browse_pemeriksa = $this->addMenuItem('Pemeriksa Aktif', $mn_browse, null, 'pemeriksa/aktif');
				// }

				// browse status by awb
				$mn_browse_hawb = $this->addMenuItem('Berdasarkan AWB/NO PIB', $mn_browse, null, 'app/browse/awb');
		}


		// menu PJT
		if ($role & (R_PPJK|R_PJT)) {
			// root menu PJT/PPJK (upload)
			$mn_request_pemeriksaan = $this->addMenuItem('Request', null, 'normal', null);

			// spesifik PPJK (PIB only)
			if ($role & R_PPJK) {
				$mn_pemeriksaan_pib = $this->addMenuItem('Pemeriksaan Fisik PIB', $mn_request_pemeriksaan, null, 'app/request/PIB');
			}

			if ($role & R_PJT) {
				$mn_pemeriksaan_cnpibk = $this->addMenuItem('Pemeriksaan Fisik CN/PIBK', $mn_request_pemeriksaan, null, 'app/request/CN_PIBK');
			}
		}

		// menu pemeriksa
		if ($role & R_PEMERIKSA) {
			$mn_pemeriksa = $this->addMenuItem('Pemeriksa', null, 'normal', null);

			// $mn_periksa_pib = $this->addMenuItem('List PIB', $mn_pemeriksa, null, 'app/listpib');
			// $mn_periksa_cnpibk = $this->addMenuItem('List CN/PIBK', $mn_pemeriksa, null, 'app/listcnpibk');

				$mn_list_dok = $this->addMenuItem('List Barang Siap Periksa', $mn_pemeriksa, null, 'pemeriksa/listbarang');
		}

		// menu admin pabean
		if ($role & R_ADMIN_PABEAN) {
			$mn_admin_pabean = $this->addMenuItem('Admin Pabean', null, 'normal', null);

			$mn_absen_pemeriksa = $this->addMenuItem('Absen Pemeriksa', $mn_admin_pabean, null, 'pemeriksa/absen');
			// $mn_penugasan = $this->addMenuItem('Penunjukkan Pemeriksa', $mn_admin_pabean, null, 'app/tunjukpemeriksa');
			$mn_update_pemeriksa = $this->addMenuItem('Status Pemeriksa', $mn_admin_pabean, null, 'pemeriksa/status');
			// $mn_laporan = $this->addMenuItem('Laporan Kegiatan', $mn_admin_pabean, null, 'app/report');
			$mn_performa_pemeriksa = $this->addMenuItem('Performa Pemeriksa', $mn_admin_pabean, null, 'pemeriksa/performa');
		}

		// menu superuser
		if ($role & R_SUPERUSER) {
			$mn_admin = $this->addMenuItem('Superuser', null, 'normal', null);

			$mn_manage_user = $this->addMenuItem('Manage User', $mn_admin, null, 'app/manage/user');
			$mn_manage_gudang = $this->addMenuItem('Manage Grup Gudang', $mn_admin, null, 'app/manage/gudang');
		}

		// kalo punya otoritas terkait dokap, buat root menunya
		/*if ($privCode & (DOKAP_CREATE|DOKAP_READ|DOKAP_ACCEPT|DOKAP_EDIT|DOKAP_REQUEST_COMPLETION|DOKAP_DELETE)) {
			$mn_dokap = $this->addMenuItem('Dokap', null, 'normal', null);

		
				$mn_dokap_browse	= $this->addMenuItem('Browse', $mn_dokap, null, 'dokap/browse');

				// kalo bisa upload, buat menu upload
				if (in_array('DOKAP_CREATE', $privilege)) {
					$mn_dokap_upload	= $this->addMenuItem('Upload', $mn_dokap, null, 'dokap/upload');
				}
		}*/
		

		// menu dokap
		$this->setupMenuHierarchy();

		return $this->menuItems;
	}

	/*
	<ul>
					<li><a href="<?php echo base_url('');?>">Home</a></li>
					<li><a href="#">Browse &amp; Cetak</a>
						<ul>
							<li><a href="<?php echo base_url('app/browse');?>">Browse Pengajuan</a></li>
							<li><a href="#">Tambah Pengajuan</a>
								<ul>
									<li><a href="<?php echo base_url('app/addcarnet');?>">Awal</a></li>
									<li><a href="#">Closing</a></li>
								</ul>
							</li>
						</ul>
					</li>
					<li><a href="#">Laporan</a>
						<ul>
							<li><a href="#">Pengajuan CARNET</a></li>
						</ul>
					</li>
				</ul>
	*/

	function generateNodeHTML($node) {
		$htmlString	= '<li '.(isset($node['style']) ? 'class="'.$node['style'].'"': '').'>';
			$htmlString	.= '<a href="'. (isset($node['target']) ? base_url($node['target']) : '#') . '">' 
						. htmlentities($node['title'])
						.'</a>';
			// generate child here
			if (isset($node['child'])) {
				$htmlString	.= '<ul>';

				foreach ($node['child'] as $id => $menuItem) {
					$htmlString	.= $this->generateNodeHTML($menuItem);
				}

				$htmlString	.= '</ul>';
			}
		$htmlString.= "</li>";

		return $htmlString;
	}

	function generateHTML($scheme) {
		$htmlString	= "<ul>";

		foreach ($scheme as $id => $menuItem) {
			$htmlString	.= $this->generateNodeHTML($menuItem);
		}		

		$htmlString	.= "</ul>";

		return $htmlString;
	}
}
?>