<?php

class C_test extends Base_Controller{
	public function __construct(){
		parent::__construct();	//call parent's ctor
	}

	public function testread() {
		$inputFile = 'D:\testbed\test1.csv';

		$this->load_model('excel');

		$data = $this->excel->readRawData($inputFile);
			
		if (!$data) {
			die("Error reading data");
		}
		// print_r($data);
		foreach ($data as $row) {
			foreach ($row as $key => $value) {
				echo $value . " <> ";
			}
			echo "<br>";
		}
	}

	public function statistics() {
		$this->load_model('app');

		$data = $this->app->queryOutstanding('GDHL GIMP APRI GBDL GAJT');

		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode($data);
	}

	public function status() {
		$this->load_model('pemeriksa');

		$data = $this->pemeriksa->getPemeriksa(pemeriksa::ROLE_ALL, pemeriksa::STATUS_ALL, pemeriksa::LOKASI_ALL);

		print_r($data);
	}
}
?>