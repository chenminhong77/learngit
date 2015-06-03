<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
class LinkageAction extends Action {
	/**
		* 联动列表
		*@param $json    为NULL输出模板。为1时输出列表数据到前端，格式为Json
		*@examlpe 
	*/
    public function index($json=NULL){
		$Public = A('Index','Public');
		$Public->check('Linkage',array('r'));
		
		//main
		if(!is_int((int)$json)){
			$json = NULL;
		}
		if($json==1){
			$linkage = M('Linkage');
			
			if(cookie('cLinkage')){
				$str_map = cookie('cLinkage');
				$map = unserialize($str_map);
			}
			//dump($map);
			if(!isset($map['_string'])){
				//$state = ',\'closed\' as state';
			}else{
				//$state = ',\'open\' as state';
			}
			$info = $linkage->field('*'.$state)->order('sort,id')->where($map)->select();
			//dump($map);
			$new_info = array();
			foreach($info as $t){
				if($t['status']==1){
					$t['status'] = '开启';
				}else{
					$t['status'] = '关闭';
				}
				if($t['_parentId']==0){
					unset($t['_parentId']);
					$new_info[] = $t;
				}else{
					$new_info[] = $t;
				}
			}
			
			echo '{"rows":'.json_encode($new_info).'}';
			unset($info,$new_info,$linkage,$str_map);
		}else{
			$this->display();
		}
		unset($Public);
    }
	
	/**
		* 新增与更新数据
		*@param $act add为新增、edit为编辑
		*@param $go  为1时，获取post
		*@param $id  传人数据id
		*@examlpe 
	*/
	public function add($act=NULL,$go=false,$id=NULL){		
		//main
		$linkage = M('Linkage');
		if($go==false){
			$this->assign('uniqid',uniqid());
			if($act=='add'){
				$this->assign('act','add');
				$this->display();
			}else{
				if(!is_int((int)$id)){
					$id = NULL;
					$this->show('无法获取ID');
				}else{
					$map['id'] = array('eq',$id);
					$info = $linkage->where($map)->find();
					if($info['_parentId']==0){
						$info['_parentId'] = '';
					}
					unset($map);
					$this->assign('id',$id);
					$this->assign('act','edit');
					$this->assign('info',$info);
					$this->display();
					unset($info);
				}
			}	
		}else{
			$data = $linkage->create();
			if($data['val']==''){
				$data['val'] = $data['text'];
			}
			if($act=='add'){
				$Public = A('Index','Public');
				$role = $Public->check('Linkage',array('c'));
				if($role<0){
					echo $role; exit;
				}
				
				if($data['code']==''){
					$data['code'] = pinyin($data['text']);
				}
				$add = $linkage->add($data);
				if($add>0){
					$this->json(NULL);
					echo 1;
				}else{
					echo 0;
				}
			}elseif($act=='edit'){
				$Public = A('Index','Public');
				$role = $Public->check('Linkage',array('u'));
				if($role<0){
					echo $role; exit;
				}
				
				if(!is_int((int)$id)){
					echo 0;
				}else{
					$map['id'] = array('eq',$id);
					if($data['code']==''){
						$data['code'] = pinyin($data['text']);
					}
					$edit = $linkage->where($map)->save($data);
					unset($map);
					if($edit!==false){
						$this->json(NULL);
						echo 1;
					}else{
						echo 0;
					}
				}
			}
			unset($data,$Public);
		}
		unset($linkage);
	}
	
	/**
		* 删除数据
		*@param $id 数据ID
		*@examlpe 
	*/
	public function del($id){
		$Public = A('Index','Public');
		$role = $Public->check('Linkage',array('d'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		if(!is_int((int)$id)){
			echo 0;
		}else{
			$linkage = M('Linkage');
			$map['id'] = array('eq',$id);
			$del = $linkage->where($map)->delete();
			unset($map);
			if($del){
				$this->json(NULL);
				echo 1;
			}else{
				echo 0;
			}
			unset($linkage);
		}
		unset($Public);
	}
	
	/**
		* 工具栏搜索控制
		*@param $act  传入的字段名
		*@examlpe 
	*/
	public function change(){	
		//main
		$id = strval(I('id'));
		$map['_string'] = "_parentId=".$id." or id=".$id;
		cookie('cLinkage',serialize($map));
		unset($map);
		echo $id;
	}
	
	
	/**
		* 生成json文件
		*@param $back  为1时，返回数据
		*@examlpe 
	*/
	public function json($back=1){
		$Loop = A('Loop','Public');
		$Loop->table = 'Linkage';
		$Loop->field = 'id,text,code,sort';
		$Loop->order = 'sort';
		$Loop->isparnet = true;
		$Loop->where = '`status`=1';
		$Loop->mode = 'noson';
		$Write = A('Write','Public');
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
	
		//main
    	$temp_path = RUNTIME_PATH.'/Temp/';
		if(file_exists($temp_path)){
			$dt = $sys->delFile($temp_path);
		}
		$linkage = M('Linkage');
		$info = $Loop->rowLevel(0);
		$json_data = json_encode($info);
		$path = RUNTIME_PATH.'Data/Json';
		$put_json = $Write->write($path,$json_data,'Linkage_data');
		
		$sele = $linkage->field('id,text,code,val')->where('_parentId=0')->select();
		$Loop->field = 'val as id,text,sort';
		$path = RUNTIME_PATH.'Data/Json/Linkage';
		foreach($sele as $t){
			$sinfo = $Loop->rowLevel($t['id']);
			//dump($sinfo);exit;
			$json_datas = json_encode($sinfo);
			$Write->write($path,$json_datas,$t['code'].'_data');
		}
		
		if($back==1){
			if($put_json){
				echo 1;
			}
		}
		
		unset($info,$json_datas,$path,$linkage,$Loop,$Write,$sele,$sinfo,$sys);
	}
	
	/**
		* 清空所以搜索产生的cookies
		*@examlpe 
	*/
	public function clear(){
		cookie('cLinkage',NULL);
	}
}