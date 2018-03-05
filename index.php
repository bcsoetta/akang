<?php
//add include path
ini_set('include_path', ini_get('include_path').';'.dirname(__FILE__));
//output all errors
error_reporting(E_ALL);
//log errors
ini_set('log_errors', 1);
//some pre-defined shit
include 'system/config.php';
//helper
include 'system/helper.php';
//here we should include base class of everything
include 'system/base_model.php';
include 'system/base_controller.php';
//include all models
include_all($config['path']['models']);
//then we include every controller and model we've got
include_all($config['path']['controllers']);
//start it
require_once 'system/boot.php';
?>