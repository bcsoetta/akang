<?php
class Base_Controller{
	function __construct(){
		$this->data=array();
	}	//base ctor
	//private $data=array();	//used to send data to view. USING ARRAY, BECAUSE OBJECTS ARE FOR LOADED MODELS
	function index(){}	//all controller must have this function (called when no argument is given)
	//this is to load model
	public function load_model($model_name){
		$classname=$model_name;
		if(!class_exists($classname))return false;
		//succeed
		$this->{$model_name} = new $classname;
		return true;
	}
	function load_view($view_name, $viewdata, $astext=false){
		//gotta spawn data		
		if(isset($viewdata)){
			foreach($viewdata as $name=>$val){
				eval('$'."$name=".var_export($val, true).';');
			}
		}
		//the config
		global $config;
		$viewfilename=$config['path']['views']."/v_".$view_name.'.php';
		if(file_exists($viewfilename)){
			if($astext){
				ob_start();
				include "$viewfilename";
				$ret=ob_get_contents();
				ob_end_clean();
				return $ret;
			}else
				include $viewfilename;
				return true;
		}
	}
}
?>