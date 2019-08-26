<?php
//this function copy clean exploded urls
function copy_clean_urls($urls, $forbidden){
	global $config;
	
	if(!isset($forbidden))
		$forbidden='';
	$ret=array();
	$cleaned=false;
	foreach ($urls as $value) {
		//skip base url
		if(!$cleaned){
			if($value == $config['path']['base']){
				$cleaned=true;
				continue;
			}
		}
		if($value!=$forbidden && strlen($value)>0)
			$ret[]=$value;
	}
	return $ret;
}
//this function create a nice linkable url
function base_url($target){
	global $config;
	$url_part=array();
	$scheme='http://';
	if(isset($_SERVER['HTTPS']))
		$scheme='https://';
	$scheme.=$_SERVER['SERVER_NAME'];
	if($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443')
		$scheme.=':'.$_SERVER['SERVER_PORT'];
	if(isset($config['path']['base']) && strlen($config['path']['base'])>0)
		$scheme.='/'.$config['path']['base'];
	$scheme.='/'.$target;
	return $scheme;
}
//get css. filename relative to /assets/css
function link_css($css_filename){
	global $config;
	echo '<link rel="stylesheet" type="text/css" href="'.base_url($config['path']['assets']."/css/$css_filename").'">'."\n";
}

function link_js($js_filename){
	global $config;
	echo '<script src="'.base_url($config['path']['assets']."/js/$js_filename").'"></script>'."\n";
}

function get_img_path($imgpath){
	global $config;
	return base_url($config['path']['assets'].'/img/'.$imgpath);
}

function link_img($imgpath){
	global $config;
	echo base_url($config['path']['assets'].'/img/'.$imgpath);
}

//used to include all stuffs in a directory
function include_all($path){
	foreach(glob("$path/*.php") as $filename){
		include $filename;
	}
}

function forbid() {
	header('HTTP/1.0 403 Forbidden');
	echo("You're forbidden to access this page, nigga! Might wanna go <a href=\"" . base_url('') . "\">home?</a>");
}
?>