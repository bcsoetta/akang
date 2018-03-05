<?php
//this will bootstart all the nitty gritty fuzzy detail
$rawuri = explode('?', $_SERVER['REQUEST_URI']);
$rawurl = $rawuri[0];
if(count($rawuri)>1){
	$query = utf8_decode( urldecode( $rawuri[1] ) );
	$query = explode('&', $query);
	foreach ($query as $q) {
		# code...
		$p=explode('=', $q);
		$_GET[$p[0]]=$p[1];
	}
}
//echo $rawurl;
//start to blast nice url
$urls=copy_clean_urls(explode('/', $rawurl), $config['path']['base']);
//only the first path usually need to be trimmed
$urls=array_values($urls);
if(!count($urls))
	$urls[]="index";	//must not be empty. in this case force load index controller
//not enough..we must call index method if no function is called
if(count($urls)<2)
	$urls[]="index";	//index again :p

//now we have our urls...let's use it 
//for now simply deploy the right object

//first, check if such controller exists
$controller_name='C_'.$urls[0];
if(class_exists($controller_name)){
	$controller=new $controller_name;
	//check if it has the function
	$method_name=$urls[1];
	if(method_exists($controller, $method_name)){
		//it does...prepare for calling with parameters
		unset($urls[0]);	//remove class name
		unset($urls[1]);	//remove method name
		$urls=array_values($urls);	//now it's reordered
		//before calling, better check if it's safe to call it
		//first, we match number of arguments with function's spec
		//$numarg=((new ReflectionMethod($controller_name, $method_name))->getParameters());
		$method=new ReflectionMethod($controller_name, $method_name);
		//we strip - and replace with space
		for($i=0; $i<count($urls); $i++)
			//$urls[$i]=str_replace('-',' ',$urls[$i]);
			$urls[$i] = urldecode($urls[$i]);	//we use urldecode so space is replaced by +
		//we add null until it matches function's arg count
		while(count($urls)<count($method->getParameters()))
			$urls[]=null;
		//now we can safely call the function
		call_user_func_array(array($controller, $method_name), $urls);
		die();	//stop right here
	}
}
//well shit. Better to show a better 404 Page
header("HTTP/1.0 404 Not Found");
echo "404. Page not found";
die();
?>