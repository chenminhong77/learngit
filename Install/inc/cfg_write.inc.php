<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
function cfg_write($data){
	if(!is_writeable(CONF)){
		show_msg(0,'Conf目录不可写');
	}
	$fp = fopen(CONF.'/appcfg.php','wb');
	flock($fp,3);
	fwrite($fp,"<"."?php\r\n");
	fwrite($fp,"return array(\r\n");
	foreach($data as $fval){
		$fval['vals'] = htmlspecialchars_decode($fval['vals']);
		if($fval['types']=='int' || $fval['types']=='bool'){
			if($fval['vals']==""){
				$fval['vals']=0;
			}
			fwrite($fp,"\t'".$fval['keyword']."' => ".addslashes($fval['vals']).",\r\n");
		}elseif($fval['types']=='select' || $fval['types']=='more'){
			list($key,$val) = explode('>>',$fval['vals']);
			if($key=='none'){
				fwrite($fp,"\t'".$fval['keyword']."' => '',\r\n");
			}else{
				fwrite($fp,"\t'".$fval['keyword']."' => '".addslashes($key)."',\r\n");
			}
		}else{
			fwrite($fp,"\t'".$fval['keyword']."' => '".addslashes($fval['vals'])."',\r\n");
		}
	}
	fwrite($fp,");");
	fclose($fp);
}