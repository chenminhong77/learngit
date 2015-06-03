<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
 
//生成随机数
/*
return String
$num		生成位数
$mode		模式，1为生成大写与数字，2为生成大小写与数字
*/
function randnum($num,$mode=1){
	if($mode==1){
		$str_abc = '1234567890ABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		$str = '';
		for($i=0;$i<$num;$i++){
			$str .= $str_abc{mt_rand(0,35)};
		}
		return $str;
		$str = '';
	}else{
		$str_abc = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		$str = '';
		for($i=0;$i<$num;$i++){
			$str .= $str_abc{mt_rand(0,61)};
		}
		return $str;
		$str = '';
	}
}