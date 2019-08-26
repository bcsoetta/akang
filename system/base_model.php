<?php
class Base_Model{
	protected $errMsg='';

	function __construct(){
	}
	
	function load_db($database='',$hostname='',$username='',$password=''){
		//fill default variable
		global $config;
		if(!$hostname)$hostname=$config['db']['hostname'];
		if(!$database)$database=$config['db']['database'];
		if(!$username)$username=$config['db']['username'];
		if(!$password)$password=$config['db']['password'];
		//by default we use mysql db
		try{
			$this->db=new PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password, array(PDO::ATTR_EMULATE_PREPARES=>false, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
		}catch (PDOException $e){
			echo $e->getMessage();
		}
		return $this->db;
	}

	function setLastError($msg) {
		$this->errMsg = $msg;
	}

	function getLastError() {
		return $this->errMsg;
	}
};
?>