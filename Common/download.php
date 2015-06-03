<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */ 
 
//下载函数
/*
$filename		下载文件地址或路径
*/
function download($filename){
	if(preg_match("/^http\:\/\//i",$filename)){
		header("location:$filename"); 
	}else{
		header('Content-Description: File Transfer'); 
		header('Content-Type: application/octet-stream'); 
		$simplename = basename($filename);
		header('Content-Disposition: attachment; filename='.$simplename); 
		header('Content-Transfer-Encoding: binary'); 
		header('Expires: 0'); 
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
		header('Pragma: public'); 
		header('Content-Length: ' . filesize($filename)); 
		ob_clean(); 
		flush(); 
		readfile($filename); 
	}	
}