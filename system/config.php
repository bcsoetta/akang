<?php
//path directivess
$config=array();
$config['path']['base']='';	//used to exclude base_path from REQUEST_URI
$config['path']['models']='models';	//where the models are stored
$config['path']['views']='views';	//where the views are stored
$config['path']['controllers']='controllers';	//where the controllers are stored
$config['path']['assets']='assets';	//where to store assets

//databases
$config['db']['hostname']='192.168.146.248';
$config['db']['username']='data_miner';
$config['db']['password']='thel0newolf';
$config['db']['database']='sapi';

//session name
$session['name']='S4p1535510n';

ini_set('date.timezone', 'Asia/Jakarta');
?>
