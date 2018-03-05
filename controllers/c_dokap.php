<?php
class C_dokap extends Base_Controller{
	public function __construct(){
		parent::__construct();

		$this->load_model('user');
		$this->load_model('menu');
	}

	public function browse() {
		// ensure this user is logged in
		$this->user->forceLogin();

		// ensure this user has enough privilege
		if (!$this->user->hasPrivilege($this->user->getData()['id'], DOKAP_READ)) {
			header('location: '.base_url(''));
			return;
		}

		// depending on user's group, this could be anything
	}

	public function upload() {
		$this->user->forceLogin();

		$data = array();
		$data['pagetitle'] = 'Sadap - BC Soetta (Upload Dokap)';
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['privilege'],
			$this->user->getData()['priv_code']
			));
		$data['mainContent'] = 'p_form_dokap_upload.php';
		$this->load_view('index', $data);
	}
}
?>