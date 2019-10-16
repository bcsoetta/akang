<?php
class C_index extends Base_Controller{
	public function __construct(){
		parent::__construct();	//call parent's ctor
		//load all model used
		$this->load_model('user');
		$this->load_model('menu');
		$this->load_model('app');
		$this->load_model('pemeriksa');
	}
	
	function index(){
		//if we're not expired, keep alive
		$this->user->forceLogin();
		//echo "shit";
		$data = array();
		$data['pagetitle'] = $this->app->getTitle();
		$data['user'] = $this->user->getData();
		$data['menu'] = $this->menu->generateHTML($this->menu->generateMenuScheme(
			$this->user->getData()['id'],
			$this->user->getData()['role_code']
			));

		if ($this->user->hasRole(R_PEMERIKSA)) {
			$pemeriksa = $this->pemeriksa->getPemeriksa(pemeriksa::ROLE_ALL, pemeriksa::STATUS_ALL, pemeriksa::LOKASI_ALL);

			$viewData = array(
				'pemeriksa' => null
				);

			if (isset($pemeriksa['pemeriksa'][$this->user->getData()['id']])) {
				$viewData['pemeriksa'] = $pemeriksa['pemeriksa'][$this->user->getData()['id']];
			}

			$data['mainContent'] = $this->load_view('pemeriksa_update_status', $viewData, true);
		} else
			$data['mainContent'] = $this->load_view('test', [], true);

		$this->load_view('index', $data);
		// print_r($data['user']);
	}
}
?>