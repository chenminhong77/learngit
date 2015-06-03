<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */

//附加类
class AppPublic extends Action {
	//转换Json数据
	/*
	return Object/Array
	$code		要获取json数据的对象文档名称，_data之前的字母
	$path		json文档在RunTime/Data/Json下是否还有下级文件夹，有"/对应的文件夹"、没有“/”
	$type		返回的类型，obj为返回对象、native返回数组、arr为返回一维数组
	*/
	static public function getJson($code,$path='/',$type='obj',$tag=NULL,$quote=NULL){
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
		
		//main
		$basepath = RUNTIME_PATH.'Data/Json/'.$path;
		$filename = '/'.$code.'_data.json';
		$json = $sys->getFile($basepath.$filename);
		if($type=='obj'){
			return json_decode($json);
		}elseif($type=='arr'){
			$arr = array();
			$json = json_decode($json);
			if($tag==NULL){
				if($quote==NULL){
					foreach($json as $t){
						$arr[$t->id] = $t->text;
					}
				}else{
					foreach($json as $t){
						$arr[$t->id] = "'".$t->text."'";
					}
				}
				
			}else{
				if($quote==NULL){
					foreach($json as $t){
						$arr[$t->id] = "<span class=pro-status-".$t->id.">".$t->text."</span>";
					}
				}else{
					foreach($json as $t){
						$arr[$t->id] = "'<span class=pro-status-".$t->id.">".$t->text."</span>'";
					}
				}
			}
			return $arr;
		}elseif($type=='native'){
			$json = json_decode($json,true);
			return $json;
		}
	}
	
	//获取记录数
	/*
	$mode		要获取记录数的模型
	*/
	public function getTotal($mode,$where=NULL){
		$result = M($mode);
		if($where){
			$count = $result->where($where)->count();
		}else{
			$count = $result->count();
		}
		return $count;
	}
}