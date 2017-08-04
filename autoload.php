<?php
date_default_timezone_set('Europe/Moscow');
chdir(dirname(__FILE__));
// set_time_limit(3600);
//define(REQUEST_PARAMETER_NAME,"_xg_u");
//define(HTACCESS_REPLACEMENT,"#g_");
function __autoload($className){
	$sourceDir = "src";
	$vendorDir = "vendor";
	$appDir = "app";
	$classmap = [
		"FPDF" => $vendorDir."/fpdf181/fpdf.php",
		"phpQuery" => $vendorDir."/phpquery/phpQuery/phpQuery.php",
		"simple_html_dom" => $vendorDir."/simple_html_dom.php",
		"simple_html_dom_node" => $vendorDir."/simple_html_dom.php"
	];
	if(isset($classmap[$className])){
		require_once $classmap[$className];
		return true;
	}
	$file = str_replace('\\','/',$className);
	$filename = $vendorDir.'/'.preg_replace("/([^\/]+)$/i","src/$1",$file).'.php';
	if (file_exists($filename) && is_readable($filename)) {
		require $filename;
		return true;
	}
	$filename = $sourceDir.'/'.$file.'.php';
	if (file_exists($filename) && is_readable($filename)) {
		require $filename;
		return true;
	}
	$filename = $appDir.'/'.$file.'.php';
	if (file_exists($filename) && is_readable($filename)) {
		require $filename;
		return true;
	}
	$filename = $vendorDir.'/'.$file.'.php';
	if (file_exists($filename) && is_readable($filename)) {
		require $filename;
		return true;
	}

}
include("config.php");
// $GLOBALS['cbConfig'] = $cbConfig;
?>
