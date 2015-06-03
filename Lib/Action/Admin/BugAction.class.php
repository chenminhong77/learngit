<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
class BugAction extends Action {
	/**
		* 问题交流列表
		*@param $json    为NULL输出模板。为1时输出列表数据到前端，格式为Json
		*@param $method  为1时，单独输出记录数
		*@examlpe 
	*/
    public function index($json=NULL,$method=NULL){
		$Public = A('Index','Public');
		$App = A('App','Public');
		$role = $Public->check('Bug',array('r'));
		
		//main
		if(!is_int((int)$json)){
			$json = NULL;
		}
		$view = C('DATAGRID_VIEW');
		$page_row = C('PAGE_ROW');
		$cid = $_SESSION['login']['se_comyID'];
		$nowdate = date("Y-m-d",time());
		$nowyear = '0000-00-00';
		if($json==1){
			$Bug = M('Bug_table');
			$result = M();
			$get_sort = $this->_get('sort');
			$get_order = $this->_get('order');
			$sort = isset($get_sort) ? strval($get_sort) : 'addtime';    
			$order = isset($get_order) ? strval($get_order) : 'desc';
			cookie('sort_Bug',$sort);
			cookie('order_Bug',$order);
			
			$Bug_table = C('DB_PREFIX').'bug_table';
			$Bug_baseinfo = C('DB_PREFIX').'bug_baseinfo_table';
			$Bug_fulltext = C('DB_PREFIX').'bug_fulltext_table';
			$user = C('DB_PREFIX').'user_table';
						
			$map = array();
			if(cookie('Bug') || cookie('aBug')){
				if(cookie('Bug')){
					$str_map = cookie('Bug');
					$map = unserialize($str_map);
				}else{
					$str_map = cookie('aBug');
					$map = unserialize($str_map);
				}
				unset($str_map);
			}else{
				$map['id'] ='id>0';
				cookie('Bug',serialize($map));
			}
			//dump(unserialize(cookie('aBug')));
			$map = implode($map,' ');
			
			$get_page = $this->_get('page'); 
			$get_rows = $this->_get('rows');
			$page = isset($get_page) ? intval($get_page) : 1;    
			$rows = isset($get_rows) ? intval($get_rows) : $page_row; 
			$now_page = $page-1;
			$offset = $now_page*$rows;
			
			
			
			$arr_flelds = array(
				'id' => 't1.id as id',
				'title' => 't1.title as title',
				'username' => 't2.username as username',
				'type' => 't1.type as type',
				'project' => 'if(t1.project<>\'\',t1.project,\'无\') as project',
				'os' => 'if(t1.os<>\'\',t1.os,\'无\') as os',
				'addtime' => 't1.addtime as addtime',
				'content' => 't3.content as content',
				'solution' => 't3.solution as solution',
				//'content_index' => "match (t4.content_index) against ('yong') as content_index",
			);
			$fields = implode(',',$arr_flelds);
			unset($arr_flelds);
			
			if(!$view){
				$info_sql = $result->table($Bug_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Bug_baseinfo.' as t3 on t3.bug_id = t1.id')->join(' '.$Bug_fulltext.' as t4 on t4.bug_id = t1.id')->select(false);
				$info = $result->query('select id,title,type,project,os,username,addtime from '.$info_sql.' as rows where '.$map.' order by convert('.$sort.' using gbk) '.$order.' limit '.$offset.','.$rows);
				//$count_sql = $result->table($Bug_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Bug_baseinfo.' as t3 on t3.bug_id = t1.id')->join(' '.$Bug_fulltext.' as t4 on t4.bug_id = t1.id')->select(false);
				$count = $result->query('select count(*) as total from '.$info_sql.' as rows where '.$map);
				$count = $count[0]['total'];	
			}else{		
				$info_sql = $result->table($Bug_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Bug_baseinfo.' as t3 on t3.bug_id = t1.id')->join(' '.$Bug_fulltext.' as t4 on t4.bug_id = t1.id')->select(false);
				$info = $result->query('select * from '.$info_sql.' as rows where '.$map.' order by convert('.$sort.' using gbk) '.$order);
				$count = count($info);				
			}
			//dump($info);exit;
			$new_info = array();
			$items = array();
			$new_info['total'] = $count;
			if($method=='total'){
				echo  json_encode($new_info); exit;
			}
			//$items = array_sort($items,$sort,$mode='nokeep',$type=$order);
			$arr_type = $App->getJson('wentileixing','Linkage/','arr');
			foreach($info as $t){
				$t['type'] = $arr_type[$t['type']];
				$items[] = $t;
			}
			
			$new_info['rows'] = $items;
			//$new_info['footer'] = '';
			//dump($new_info);
			echo json_encode($new_info);
			
			unset($new_info,$info,$comy,$order,$sort,$count,$items,$fields,$result,$arr_status);
		}else{
			$tinfo = $App->getJson('wentileixing','/Linkage','native');
			$this->assign('tinfo',$tinfo);
			$this->assign('page_row',$page_row);
			$this->assign('nowdate',$nowdate);
			$this->assign('nowyear',$nowyear);
			$this->display();
			unset($Public);
		}
    }
	
	/**
		* 新增与更新数据
		*@param $act add为新增、edit为编辑
		*@param $go  为1时，获取post
		*@param $id  传人数据id
		*@examlpe 
	*/
	public function add($act=NULL,$go=false,$id=NULL){
		$app = A('App','Public');
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
		import('ORG.Net.UploadFile');
		$up = new UploadFile();
		$up->allowExts = array('rar','zip');
		$upload = C('TMPL_PARSE_STRING.__UPLOAD__');
		$up->savePath = ROOT.'/'.$upload.'/';
		$up->maxSize = C('UPLOAD_SIZE');
		$up->charset = 'UTF-8';
		$up->autoSub = true;
		$up->allowNull = true;
			
		//main
		$Bug = D('Bug_table');
		$userid = $_SESSION['login']['se_id'];
		$username = $_SESSION['login']['se_user'];
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
					$info = $Bug->relation(true)->where($map)->find();
					$this->assign('constr',$constr);
					$constr = '';
					$this->assign('id',$id);
					$this->assign('act','edit');
					$this->assign('info',$info);
					$this->display();
					unset($info,$uinfo,$map);
				}
			}	
		}else{
			$data = $Bug->create();
			$content = $_POST['content'];
			$fulltext = strip_tags($content);
			$fulltext = preg_replace("/[\n\r\t\v]/iu","",$fulltext);
			$fulltext = pinyin($fulltext,' ');
			$fulltext = preg_replace("/\s+/u"," ",$fulltext);
			$fulltext = trim($fulltext);
			$solution = $_POST['solution'];
			$data['baseinfo'] = array(
				'content'=>$content,
				'solution'=>$solution,
			);
			$data['fulltext'] = array(
				'content_index'=>$fulltext,
			);
			$ischg = 0;
			if($up->upload()){
				$info = $up->getUploadFileInfo();
				$data['files'] = $info[0]['savename'];
				$ischg = 1;
			}else{
				$errorNo = $up->getErrorNo();
				if($errorNo!=2){
					echo $info = $up->getErrorMsg();
					exit;
				}
			}
			if($act=='add'){
				$Public = A('Index','Public');
				$role = $Public->check('Bug',array('c'));
				if($role<0){
					echo $role; exit;
				}
				$data['user_id'] = $userid;
				$data['addtime'] = date("Y-m-d H:i:s",time());
				$add = $Bug->relation(true)->add($data);
				if($add>0){
					echo 1;
				}else{
					$path = ROOT.'/'.$upload.'/'.$info[0]['savename'];
					if($info[0]['savename'] && file_exists($path)){
						$sys->delFile($path);
					}
					echo 0;
				}
				unset($data,$Public);
			}elseif($act=='edit'){
				$Public = A('Index','Public');
				$role = $Public->check('Bug',array('u'));
				if($role<0){
					echo $role; exit;
				}
				if(!is_int((int)$id)){
					echo 0;
				}else{
					if($role!='all' && !in_array('a',$role) ){
						$user_id = $Bug->where('id='.$id)->getField('user_id');
						if($userid!=$user_id){
							echo 2; exit;
						}
					}
					
					if($ischg==1){
						$oldfile = I('oldfile');
						$path = ROOT.'/'.$upload.'/'.$oldfile;
						if($oldfile && file_exists($path)){
							$sys->delFile($path);
						}	
					}
					$map['id'] = array('eq',$id);
					$edit = $Bug->relation(true)->where($map)->save($data);
					unset($map);
					if($edit !== false){
						echo 1;
					}else{
						echo 0;
					}
					unset($data,$Public);
				}
			}
		}
		unset($Bug);
	}
	
	/**
		* 删除数据
		*@examlpe 
	*/
	public function del(){
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
		$Public = A('Index','Public');
		$role = $Public->check('Bug',array('d'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$str_id = I('id');
		$str_id = strval($str_id);
		$str_id = substr($str_id,0,-1);
		$arr_id = explode(',',$str_id);
		$Bug = D('Bug_table');
		$upload = C('TMPL_PARSE_STRING.__UPLOAD__');
		$pass = 0;$fail = 0;
		$uid = $_SESSION['login']['se_id'];
		$arr_ids = $arr_id;
		if($role!='all' && !in_array('a',$role) ){
			foreach($arr_ids as $k=>$id){
				$user_id = $Bug->where('id='.$id)->getField('user_id');
				if($uid!=$user_id){
					echo 2; exit;
				}
			}
		}
		foreach($arr_id as $id){
			$files = $Bug->where('id='.$id)->getField('files');
			$del = $Bug->relation(true)->delete($id);
			if($del){
				$path = ROOT.'/'.$upload.'/'.$files;
				if($files && file_exists($path)){
					$sys->delFile($path);
				}
				$pass++;
			}else{
				$fail++;
			}
		}
		unset($str_id,$arr_id,$arr_ids,$arr_creator);
		if($pass==0){
			echo 0;
		}else{
			echo 1;
		}
		$pass = 0; $fail = 0;
		unset($Bug,$Public);
	}
	
	/**
		* 右键快速搜索
		*@param $field  传入搜索字段
		*@param $json 为1时，输出json数据
		*@param $act   为1时，获取post
		*@examlpe 
	*/
	public function search($field=NULL,$json=NULL,$act=NULL){	
		$App = A('App','Public');
	
		//main
		$field = strval($field);
		$result = M();
		$Bug_table = C('DB_PREFIX').'bug_table';
		$Bug_baseinfo = C('DB_PREFIX').'bug_baseinfo_table';
		$Bug_fulltext = C('DB_PREFIX').'bug_fulltext_table';
		$user = C('DB_PREFIX').'user_table';
		
		if($act==1){
			$keyfield = I('field');
			$mod = I('mod');
			$keyword = I('keybug');
			
			if($mod=='like' || $mod=='notlike'){
				$keyword = '%'.$keyword.'%';
			}	
			
			$map = array();
			if(cookie('Bug')){
				$str_map = cookie('Bug');
				$map = unserialize($str_map);		
			}
			$mod = htmlspecialchars_decode($mod);
			$map[$keyfield] = " and ".$keyfield." ".$mod." '".$keyword."'";	
			if($keyword=='clearThisValue'){
				unset($map[$keyfield]);
			}
			cookie('aBug',NULL);
			cookie('Bug',serialize($map));
			unset($str_map,$map,$keyfield,$keyword,$mod,$table);
			echo 1;
		}else{
			if($json==1){
				$map = array();
				if(cookie('Bug') || cookie('aBug')){
					if(cookie('Bug')){
						$str_map = cookie('Bug');
						$map = unserialize($str_map);
					}else{
						$str_map = cookie('aBug');
						$map = unserialize($str_map);
					}
					unset($str_map);
				}else{
					$map['id'] ='id>0';
				}
				
				$map[$field] = ' and '.$field.'<>\'\'';
				$map = implode($map,' ');
				$arr_type = $App->getJson('wentileixing','Linkage/','arr',NULL,1);
				
				$arr_flelds = array(
					'id' => 't1.id as id',
					'title' => 't1.title as title',
					'username' => 't2.username as username',
					'type' => 't1.type as type',
					'project' => 'if(t1.project<>\'\',t1.project,\'无\') as project',
					'os' => 'if(t1.os<>\'\',t1.os,\'无\') as os',
					'addtime' => 't1.addtime as addtime',
					'content' => 't3.content as content',
					'solution' => 't3.solution as solution',
					//'content_index' => "match (t4.content_index) against ('yong') as content_index",
				);
				$fields = implode(',',$arr_flelds);
				unset($arr_flelds);
				
				$info_sql = $result->table($Bug_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Bug_baseinfo.' as t3 on t3.bug_id = t1.id')->join(' '.$Bug_fulltext.' as t4 on t4.bug_id = t1.id')->select(false);
				
				if($field=='type'){
					$text = 'ELT('.$field.','.implode(',',$arr_type).') as text';
				}else{
					$text = $field.' as text';
				}
				$info = $result->query('select '.$field.' as id,'.$text.' from '.$info_sql.' as rows where '.$map.' group by text order by convert('.$field.' using gbk) asc');
				
				//dump($info);
				array_unshift($info,array('id'=>"clearThisValue",'text'=>"所有"));
				echo json_encode($info);
				unset($info,$result,$field,$map);
			}else{
				$this->assign('uniqid',uniqid());
				$this->assign('field',$field);
				$this->display();
			}	
		}
	}
	
	/**
		* 高级搜索
		*@param $act   为1时，获取post
		*@examlpe 
	*/
	public function advsearch($act=NULL){
		$App = A('App','Public');
			
		//main
		$field = strval($field);
		if($act==1){
			$field = I('field');
			$mod = I('mod');
			$keyword = I('keys');	
			$type = I('type');
			array_pop($field); array_pop($mod); array_pop($keyword); array_pop($type);
			
			$del = array_pop($type);
			
			$arr = array();
			$num = 0;
			$map['id'] ='id>0';
			foreach($field as $key=>$val){
				$arr_type = $App->getJson('wentileixing','Linkage/','arr');
				if($val=='type'){
					$keyword[$key] = array_search($keyword[$key],$arr_type);
				}
				
				if($mod[$key]=='like' || $mod[$key]=='notlike'){
					$keyword[$key] = '%'.$keyword[$key].'%';
				}
				$tt = trim($type[$key]);
				$n = $key+1;
				$l = $key-1;
				$nt = trim($type[$n]);
				$lt = trim($type[$l]);
				
				if($tt=='OR'){
					if($keyword[$key]){
						$mod[$key] = htmlspecialchars_decode($mod[$key]);
						$arr[$num]['k'][] = $val;
						$arr[$num]['v'][] = $val." ".$mod[$key]." '".$keyword[$key]."'";
					}
					if($nt=='AND'){
						$mod[$n] = htmlspecialchars_decode($mod[$n]);
						if($mod[$n]=='like' || $mod[$n]=='notlike'){
							$keyword[$n] = '%'.$keyword[$n].'%';
						}
						if($keyword[$n]){
							$arr[$num]['k'][] = $val;
							$arr[$num]['v'][] = $val." ".$mod[$n]." '".$keyword[$n]."'";
						}
						$num++;
					}
				}else{
					if($lt!='OR' && $tt=='AND'){
						$mod[$key] = htmlspecialchars_decode($mod[$key]);
						if($keyword[$key]){
							$map[$val] = ' and '.$val." ".$mod[$key]." '".$keyword[$key]."'";
						}
					}
				}
				
				if(!isset($type[$key]) && $lt=='OR'){
					$mod[$key] = htmlspecialchars_decode($mod[$key]);
					if($keyword[$key]){
						$arr[$num]['k'][] = $val;
						$arr[$num]['v'][] = $val." ".$mod[$key]." '".$keyword[$key]."'";
					}
				}else{
					if(!isset($type[$key]) && $lt!='OR'){
						$mod[$key] = htmlspecialchars_decode($mod[$key]);
						if($keyword[$key]){
							$map[$val] = ' and '.$val." ".$mod[$key]." '".$keyword[$key]."'";
						}
					}
				}
			}
			$num = 0;
			unset($key,$val,$ntable,$table,$field,$mod,$type,$keyword);
			
			foreach($arr as $key=>$val){
				$str = $val['k'][0];
				for($i=0;$i<count($val['v']);$i++){
					if($i==0){
						$map[$str] .= ' and ('.$val['v'][$i];
					}elseif($i==count($val['v'])-1){
						$map[$str] .= ' or '.$val['v'][$i].')';
					}else{
						$map[$str] .= ' or '.$val['v'][$i];
					}
				}	
			}
			unset($arr);
			
			cookie('Bug',NULL);
			cookie('aBug',serialize($map));
			echo 1;
			unset($map);
		}else{
			$this->assign('uniqid',uniqid());
			$this->assign('field',$field);
			$this->display();
		}	
	}
	
	/**
		* 清空所以搜索产生的cookies
		*@examlpe 
	*/
	public function clear(){
    	cookie('Bug',NULL);
		cookie('aBug',NULL);
		cookie('tBug',NULL);
	}
	
	/**
		* 查看详情
		*@param $id  数据id值
		*@examlpe 
	*/
	public function detail($id){
		$Public = A('Index','Public');
		$App = A('App','Public');
		$role = $Public->check('Bug',array('r'));
		$Bug = D('Bug_table');
		
		$uid = $_SESSION['login']['se_id'];
		$map['id'] = array('eq',$id);
		$this->assign('uniqid',uniqid());
		$info = $Bug->relation(true)->where($map)->find();
		$arr_type = $App->getJson('wentileixing','Linkage/','arr');
		$info['type'] = $arr_type[$info['type']];
		if($role=='all' || in_array('a',$role) ){
			$isadmin = 1;
		}else{
			$isadmin = 0;
		}
		//dump($info);
		$this->assign('isadmin',$isadmin);
		$this->assign('uid',$uid);
		$this->assign('id',$id);
		$this->assign('info',$info);
		$this->display();
		unset($Public,$role,$Bug);
	}
	
	/**
		* 获取用户名称
		*@param $uid  用户ID
		*@param $act  1时返回数组，NULL时返回字符串
		*@examlpe 
	*/
	public function getUserStr($uid,$act=NULL){
		$user = M('User_table');
		if($act==1){
			$info = $user->field('id,username')->where('id in('.$uid.')')->select();
			return $info;
		}else{
			$info = $user->field('GROUP_CONCAT(username) as username')->where('id in('.$uid.')')->select();
			return $info[0]['username'];
		}
		
		unset($user,$info);
	}
	
	/**
		* 工具栏搜索控制
		*@param $act  传入的字段名
		*@param $mode  为like时，模糊搜索
		*@examlpe 
	*/
	public function change($act,$mode=NULL){
		if(cookie('Bug')){
			$str_map = cookie('Bug');
			$map = unserialize($str_map);
		}
		unset($str_map);
		$id = strval(I('val'));
		switch($act){
			case 'type':
				$map['type'] = " and type='".$id."'";
				if(!$id){
					unset($map['type']);
				}
			break;
			
			case 'addtime':
				$arr_date = explode('|',$id);
				$map['addtime'] = " and (addtime between '".$arr_date[0]." 00:00:00' and '".$arr_date[1]." 23:59:59')";
			break;
			
			case 'content_index':
				/*$Bug_fulltext = M('Bug_fulltext_table');
				$count = $Bug_fulltext->count();			
				$ids = strip_tags($id);
				$ids = pinyin($ids,' ');
				$ids = preg_replace("/\s+/u"," ",$ids);
				$ids = trim($ids);
				$len = strlen($ids);
				if($len>3 && $count>6){
					$map['content_index'] = " and MATCH(content_index) AGAINST('".$ids."' IN BOOLEAN MODE)";
				}else{*/
				$map['content'] = " and content like '%".$id."%'";
				//}
			break;
			
			default:
				if($mode=='like'){
					$map[$act] = " and ".$act." like '%".$id."%'";
				}else{
					$map[$act] = " and ".$act." = '".$id."'";
				}
			break;
		}
		cookie('Bug',serialize($map));
	}
	
	/**
		* 附件下载
		*@param $filename  文件路径
		*@examlpe 
	*/
	public function down($filename){
		load("@.download");
		
		//main
		$filename = urldecode($filename);
		$upload = C('TMPL_PARSE_STRING.__UPLOAD__');
		$path = ROOT.'/'.$upload.'/'.$filename;
		if($filename){
			download($path,$filename);
		}
	}
	
	/**
		* 新增回复
		*@param $id  数据id值
		*@examlpe 
	*/
	public function reply($id){
		$Public = A('Index','Public');
		$role = $Public->check('Bug',array('c'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$bug_id =  intval($id);
		$description = addcslashes($_POST['description'],"'");
		$reply = M('Bug_reply_table');
		$userid = $_SESSION['login']['se_id'];
		$username = $_SESSION['login']['se_user'];
		$data = array(
			'bug_id'=>$bug_id,
			'user_id'=>$userid,
			'username'=>$username,
			'description'=>$description,
			'replytime'=>date("Y-m-d H:i:s"),
		);
		$add = $reply->add($data);
		if($add>0){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
		* 删除回复
		*@param $id  数据id值
		*@examlpe 
	*/
	public function delreply(){
		$Public = A('Index','Public');
		$role = $Public->check('Bug',array('d'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$id = intval(I('val'));
		$reply = M('Bug_reply_table');
		
		$uid = $_SESSION['login']['se_id'];
		if($role!='all' && !in_array('a',$role) ){
			$user_id = $reply->where('id='.$id)->getField('user_id');
			if($uid!=$user_id){
				echo 2; exit;
			}
		}
		$del = $reply->delete($id);
		if($del>0){
			echo 1;
		}else{
			echo 0;
		}
	}
}