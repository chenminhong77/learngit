<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
 
//数组编码转换函数
/*
return Array
$in_charset		原字符串编码
$out_charset	输出字符串编码
$arr			传入的数组
*/
function array_iconv($in_charset,$out_charset,$arr){   
	eval('$resArr = '.iconv($in_charset,$out_charset."//IGNORE",var_export($arr,true)).' ;');
	return $resArr;
}  