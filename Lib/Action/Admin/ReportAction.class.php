<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
class ReportAction extends Action {
	/**
		* 项目问题列表
		*@param $pid  传入所属项目ID
		*@param $json    为NULL输出模板。为1时输出列表数据到前端，格式为Json
		*@param $method  为1时，单独输出记录数
		*@examlpe 
	*/
    public function index($pid=NULL,$json=NULL,$method=NULL,$ord=NULL){
		$Public = A('Index','Public');
		$role = $Public->check('Report',array('r'));
		$App = A('App','Public');
		
		//main
		if(!is_int((int)$json)){
			$json = NULL;
		}
		$view = C('DATAGRID_VIEW');
		$page_row = C('PAGE_ROW');
		$cid = $_SESSION['login']['se_comyID'];
		$userid = $_SESSION['login']['se_id'];
		$pid = intval($pid);
		$nowdate = date("Y-m-d",time());
		$nowyear = '0000-00-00';
		if($json==1){
			$Report = M('Report_table');
			$result = M();
			$get_sort = $this->_get('sort');
			$get_order = $this->_get('order');
			$sort = isset($get_sort) ? strval($get_sort) : 'status';    
			$order = isset($get_order) ? strval($get_order) : 'asc';
			cookie('sort_Report',$sort);
			cookie('order_Report',$order);
			
			$Report_table = C('DB_PREFIX').'report_table';
			$Report_baseinfo = C('DB_PREFIX').'report_baseinfo_table';
			$user = C('DB_PREFIX').'user_table';
						
			$map = array();
			if(cookie('Report') || cookie('aReport')){
				if(cookie('Report')){
					$str_map = cookie('Report');
					$map = unserialize($str_map);
				}else{
					$str_map = cookie('aReport');
					$map = unserialize($str_map);
				}
				unset($str_map);
			}else{
				$map['id'] ='id>0';
				$comy = M('User_company_table');
				$st = $comy->where('id='.$cid)->getField('type');
				if($role!='all' && !in_array('a',$role)){
					if($st==1){
						$map['tcreator'] =' and `tcreator`='.$userid;
					}
				}
				cookie('Report',serialize($map));
			}
			
			if($pid){
				$map['pid'] = ' and pid='.$pid;
			}else{
				unset($map['pid']);
			}
			//dump(unserialize(cookie('Report')));
			if($ord){
				if($ord=='self'){
					$oord = ' and `status`<=4 and (tuser_id='.$userid.' or tcreator='.$userid.')';	
				}elseif($ord=='ing'){
					$oord = ' and `status`<=4';	
				}
			}else{
				$oord = '';	
			}
			$map = implode($map,' ').$oord;
			
			$get_page = $this->_get('page'); 
			$get_rows = $this->_get('rows');
			$page = isset($get_page) ? intval($get_page) : 1;    
			$rows = isset($get_rows) ? intval($get_rows) : $page_row; 
			$now_page = $page-1;
			$offset = $now_page*$rows;
			
			$arr_status = $App->getJson('wentizhuangtai','Linkage/','arr',1,1);
			$arr_type = $App->getJson('chuxianweizhi','Linkage/','arr',NULL,1);
			$arr_priority = $App->getJson('youxianji','Linkage/','arr',NULL,1);
			$arr_flelds = array(
				'id' => 't1.id as id',
				'pid' => 't1.pid as pid',
				'bugno' => 't1.bugno as bugno',
				'status' => 't1.status as status',
				'user_id' => 'if(t2.username<>\'\',t2.username,\'无\') as user_id',
				'type' => 't1.type as type',
				'level' => 't1.level as level',
				'hertz' => 't1.hertz as hertz',
				'priority' => 't1.priority as priority',
				'os' => 'if(t1.os<>\'\',t1.os,\'无\') as os',
				'title' => 't1.title as title',
				'creator' => 't4.username as creator',
				'addtime' => 't1.addtime as addtime',
				'tuser_id' => 't1.user_id as tuser_id',
				'tcreator' => 't1.creator as tcreator',
			);
			$fields = implode(',',$arr_flelds);
			unset($arr_flelds);
			
			if(!$view){
				$info_sql = $result->table($Report_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Report_baseinfo.' as t3 on t3.rid = t1.id')->join(' '.$user.' as t4 on t4.id = t1.creator')->select(false);
				$info = $result->query('select *,ELT(status,'.implode(',',$arr_status).') as status,ELT(type,'.implode(',',$arr_type).') as type,ELT(priority,'.implode(',',$arr_priority).') as priority from '.$info_sql.' as rows where '.$map.' order by convert('.$sort.' using gbk) '.$order.',priority desc,addtime desc limit '.$offset.','.$rows);
				$count = $result->query('select count(*) as total from '.$info_sql.' as rows where '.$map);
				$count = $count[0]['total'];	
			}
			//dump($info);exit;
			$new_info = array();
			//$items = array();
			$new_info['total'] = $count;
			if($method=='total'){
				echo  json_encode($new_info); exit;
			}
			/*foreach($info as $t){
				$items[] = $t;
			}*/
			
			$new_info['rows'] = $info;
			//$new_info['footer'] = '';
			//dump($new_info);
			echo json_encode($new_info);
			
			unset($new_info,$info,$comy,$order,$sort,$count,$items,$fields,$result,$arr_status);
		}
    }
	
	/**
		* 新增与更新数据
		*@param $act add为新增、edit为编辑
		*@param $go  为1时，获取post
		*@param $id  传人数据id
		*@param $pid  传人数据项目id
		*@examlpe 
	*/
	public function add($act=NULL,$go=false,$id=NULL,$pid=NULL){
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
		$Report = D('Report_table');
		$Report_baseinfo = D('Report_baseinfo_table');
		$userid = $_SESSION['login']['se_id'];
		$username = $_SESSION['login']['se_user'];
		if($go==false){
			$this->assign('pid',$pid);
			$this->assign('uniqid',uniqid());
			if($act=='add'){
				$bugno = $Report->Max('id');
				$bugno = $bugno+1;
				$bugno = 'E'.str_pad($bugno,8,'0',STR_PAD_LEFT);
				$this->assign('bugno',$bugno);
				$this->assign('username',$username);
				$this->assign('act','add');
				if($pid){
					$this->display();
				}else{
					$this->display('addfull');
				}
			}else{
				if(!is_int((int)$id)){
					$id = NULL;
					$this->show('无法获取ID');
				}else{
					$map['id'] = array('eq',$id);
					$info = $Report->relation(array('baseinfo','create'))->where($map)->find();
					//dump($info);
					$bugno = 'E'.str_pad($info['id'],8,'0',STR_PAD_LEFT);
					$this->assign('bugno',$bugno);
					$this->assign('id',$id);
					$this->assign('username',$info['createname']);
					$this->assign('act','edit');
					$this->assign('info',$info);
					$this->display();
					unset($info,$uinfo,$map);
				}
			}	
		}else{
			$nowtime = date("Y-m-d H:i:s",time());
			$data = $Report->create();
			$base = $Report_baseinfo->create();
			if(!$data['user_id']){
				$data['status'] = 1;
				$data['user_id'] = 0;
			}else{
				$data['status'] = 2;
			}
			$data['baseinfo'] = $base;
			if($up->upload()){
				$info = $up->getUploadFileInfo();
				$data['files'][] = array(
					'user_id'=>$userid,
					'path'=>$info[0]['savename'],
					'addtime'=>$nowtime,
				);
			}else{
				$errorNo = $up->getErrorNo();
				if($errorNo!=2){
					echo $info = $up->getErrorMsg();
					exit;
				}
			}
			//dump($data);exit;
			if($act=='add'){
				$Public = A('Index','Public');
				$role = $Public->check('Report',array('c'));
				if($role<0){
					echo $role; exit;
				}
				$data['creator'] = $userid;
				$data['addtime'] = $nowtime;
				$data['uptime'] = $nowtime;
				$add = $Report->relation(true)->add($data);
				if($add>0){
					
					//信息提醒功能
					if(C('SMS_AUTO_REPORT')){
						$sms = A('Sms','Public');
						$title = '项目BUG指派提醒信息';
						$proname = $Public->GS('Project_table',$data['pid'],'name');
						$content = $username.'指派了您为关于项目：'.$proname.'，问题编号为：'.$data['bugno'].'的负责人。';
						$sms->sendsms(array(
							'user_id'=>$userid,
							'title'=>$title,
							'description'=>$content,
						),$data['user_id']);
					}
					
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
				$role = $Public->check('Report',array('u'));
				if($role<0){
					echo $role; exit;
				}
				
				if(!is_int((int)$id)){
					echo 0;
				}else{
					if($role!='all' && !in_array('a',$role) ){
						$user_id = $Report->where('id='.$id)->getField('creator');
						if($userid!=$user_id){
							echo 2; exit;
						}
					}
					$data['uptime'] = $nowtime;
					$map['id'] = array('eq',$id);
					$edit = $Report->relation(true)->where($map)->save($data);
					unset($map);
					if($edit !== false){
						
						$olduser = I('olduser');
						if($data['user_id']!=$olduser){
							//信息提醒功能
							if(C('SMS_AUTO_REPORT')){
								$sms = A('Sms','Public');
								$title = '项目BUG指派提醒信息';
								$proname = $Public->GS('Project_table',$data['pid'],'name');
								$content = $username.'指派了您为关于项目：'.$proname.'中问题编号为：'.$data['bugno'].' 的问题负责人。';
								$sms->sendsms(array(
									'user_id'=>$userid,
									'title'=>$title,
									'description'=>$content,
								),$data['user_id']);
							}
						}
						
						echo 1;
					}else{
						echo 0;
					}
					unset($data,$Public);
				}
			}
		}
		unset($Report);
	}
	
	/**
		* 删除数据
		*@examlpe 
	*/
	public function del(){
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
		$Public = A('Index','Public');
		$role = $Public->check('Report',array('d'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$str_id = I('id');
		$str_id = strval($str_id);
		$str_id = substr($str_id,0,-1);
		$arr_id = explode(',',$str_id);
		$Report = D('Report_table');
		$files_report = D('Report_files_table');
		$upload = C('TMPL_PARSE_STRING.__UPLOAD__');
		$pass = 0;$fail = 0;
		$uid = $_SESSION['login']['se_id'];
		$arr_ids = $arr_id;
		if($role!='all' && !in_array('a',$role) ){
			foreach($arr_ids as $k=>$id){
				$user_id = $Report->where('id='.$id)->getField('creator');
				if($uid!=$user_id){
					echo 2; exit;
				}
			}
		}
		foreach($arr_id as $id){
			$files = $files_report->field('path')->where('rid='.$id)->find();
			$del = $Report->relation(true)->delete($id);
			if($del){
				foreach($files as $t){
					$path = ROOT.'/'.$upload.'/'.$t['path'];
					if($t['path'] && file_exists($t['path'])){
						$sys->delFile($path);
					}
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
		unset($Report,$Public);
	}
	
	/**
		* 右键快速搜索
		*@param $pid  传入所属项目ID
		*@param $field  传入搜索字段
		*@param $json 为1时，输出json数据
		*@param $act   为1时，获取post
		*@examlpe 
	*/
	public function search($pid=NULL,$field=NULL,$json=NULL,$act=NULL){	
		$App = A('App','Public');
	
		//main
		$field = strval($field);
		$result = M();
		$Report_table = C('DB_PREFIX').'report_table';
		$Report_baseinfo = C('DB_PREFIX').'report_baseinfo_table';
		$user = C('DB_PREFIX').'user_table';
		
		if($act==1){
			$keyfield = I('field');
			$mod = I('mod');
			$keyword = I('keyreport');
			
			if($mod=='like' || $mod=='notlike'){
				$keyword = '%'.$keyword.'%';
			}	
			
			$map = array();
			if(cookie('Report')){
				$str_map = cookie('Report');
				$map = unserialize($str_map);		
			}
			$mod = htmlspecialchars_decode($mod);
			$map[$keyfield] = " and ".$keyfield." ".$mod." '".$keyword."'";	
			if($keyword=='clearThisValue'){
				unset($map[$keyfield]);
			}
			cookie('aReport',NULL);
			cookie('Report',serialize($map));
			unset($str_map,$map,$keyfield,$keyword,$mod,$table);
			echo 1;
		}else{
			if($json==1){
				$map = array();
				if(cookie('Report') || cookie('aReport')){
					if(cookie('Report')){
						$str_map = cookie('Report');
						$map = unserialize($str_map);
					}else{
						$str_map = cookie('aReport');
						$map = unserialize($str_map);
					}
					unset($str_map);
				}else{
					$map['id'] ='id>0';
				}
				if($pid){
					$map['pid'] =' and pid='.$pid;
				}else{
					unset($map['pid']);
				}
				
				$map[$field] = ' and '.$field.'<>\'\'';
				$map = implode($map,' ');
				$arr_status = $App->getJson('wentizhuangtai','Linkage/','arr',NULL,1);
				$arr_type = $App->getJson('chuxianweizhi','Linkage/','arr',NULL,1);
				$arr_priority = $App->getJson('youxianji','Linkage/','arr',NULL,1);
				
				$arr_flelds = array(
					'id' => 't1.id as id',
					'pid' => 't1.pid as pid',
					'bugno' => 't1.bugno as bugno',
					'status' => 't1.status as status',
					'user_id' => 'if(t2.username<>\'\',t2.username,\'无\') as user_id',
					'type' => 't1.type as type',
					'level' => 't1.level as level',
					'hertz' => 't1.hertz as hertz',
					'priority' => 't1.priority as priority',
					'os' => 'if(t1.os<>\'\',t1.os,\'无\') as os',
					'title' => 't1.title as title',
					'creator' => 't4.username as creator',
					'addtime' => 't1.addtime as addtime',
				);
				$fields = implode(',',$arr_flelds);
				unset($arr_flelds);
				
				$info_sql = $result->table($Report_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Report_baseinfo.' as t3 on t3.report_id = t1.id')->join(' '.$Report_fulltext.' as t4 on t4.report_id = t1.id')->select(false);
				
				switch($field){
					case 'type':
						$text = 'ELT('.$field.','.implode(',',$arr_type).') as text';
					break;		
					case 'status':
						$text = 'ELT('.$field.','.implode(',',$arr_status).') as text';
					break;	
					case 'priority':
						$text = 'ELT('.$field.','.implode(',',$arr_priority).') as text';
					break;	
					default:
						$text = $field.' as text';
					break;
				}
				$info_sql = $result->table($Report_table.' as t1')->field($fields)->join(' '.$user.' as t2 on t2.id = t1.user_id')->join(' '.$Report_baseinfo.' as t3 on t3.rid = t1.id')->join(' '.$user.' as t4 on t4.id = t1.creator')->select(false);
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
		*@param $pid  传入所属项目ID
		*@param $act   为1时，获取post
		*@examlpe 
	*/
	public function advsearch($pid=NULL,$act=NULL){
		$Public = A('Index','Public');
		$role = $Public->check('Report',array('r'));
		$App = A('App','Public');
			
		//main
		$cid = $_SESSION['login']['se_comyID'];
		$userid = $_SESSION['login']['se_id'];
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
			$comy = M('User_company_table');
			$st = $comy->where('id='.$cid)->getField('type');
			if($role!='all' && !in_array('a',$role)){
				if($st==1){
					$map['tcreator'] =' and `tcreator`='.$userid;
				}
			}
			if($pid){
				$map['pid'] =' and pid='.$pid;
			}
			foreach($field as $key=>$val){
				$arr_status = $App->getJson('wentizhuangtai','Linkage/','arr');
				$arr_type = $App->getJson('chuxianweizhi','Linkage/','arr');
				$arr_priority = $App->getJson('youxianji','Linkage/','arr');
				switch($val){
					case 'type':
						$keyword[$key] = array_search($keyword[$key],$arr_type);
					break;		
					case 'status':
						$keyword[$key] = array_search($keyword[$key],$arr_status);
					break;	
					case 'priority':
						$keyword[$key] = array_search($keyword[$key],$arr_priority);
					break;
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
			
			cookie('Report',NULL);
			cookie('aReport',serialize($map));
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
    	cookie('Report',NULL);
		cookie('aReport',NULL);
		cookie('tReport',NULL);
	}
	
	/**
		* 查看详情
		*@param $id  数据id值
		*@examlpe 
	*/
	public function detail($id){
		$Public = A('Index','Public');
		$App = A('App','Public');
		$role = $Public->check('Report',array('r'));
		
		//main
		$Report = D('Report_table');
		$uid = $_SESSION['login']['se_id'];
		$map['id'] = array('eq',$id);
		$this->assign('uniqid',uniqid());
		$info = $Report->relation(true)->where($map)->find();
		$arr_status = $App->getJson('wentizhuangtai','Linkage/','arr');
		$arr_type = $App->getJson('chuxianweizhi','Linkage/','arr');
		$arr_priority = $App->getJson('youxianji','Linkage/','arr');
		$info['type'] = $arr_type[$info['type']];
		$info['priority'] = $arr_priority[$info['priority']];
		$info['statusname'] = $arr_status[$info['status']];
		if($info['comy']>1000){
			$cid = (string)$info['comy'];
			$cid = substr($cid,3,strlen($cid));
			$info['comyname'] = $Public->GS('User_company_table',$cid,'name');
		}else{
			$info['comyname'] = $Public->GS('User_part_table',$info['comy'],'name');
		}
		//dump($info);
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
		unset($Public,$role,$Report);
	}
	
	/**
		* 工具栏搜索控制
		*@param $pid  传入所属项目ID
		*@param $act  传入的字段名
		*@param $mode  为like时，模糊搜索
		*@examlpe 
	*/
	public function change($act,$mode=NULL,$pid=NULL){
		if(cookie('Report')){
			$str_map = cookie('Report');
			$map = unserialize($str_map);
		}
		unset($str_map);
		$id = strval(I('val'));
		switch($act){
			case 'status':
				$map['status'] = " and status='".$id."'";
				if(!$id){
					unset($map['status']);
				}
			break;
			
			default:
				if($mode=='like'){
					$map[$act] = " and ".$act." like '%".$id."%'";
				}else{
					$map[$act] = " and ".$act." = '".$id."'";
				}
			break;
		}
		cookie('Report',serialize($map));
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
		* 新增反馈
		*@param $id  数据id值
		*@examlpe 
	*/
	public function reply($id){
		$Public = A('Index','Public');
		$role = $Public->check('Project',array('gc'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$rid =  intval($id);
		$description = addcslashes($_POST['description'],"'");
		$reply = M('Report_reply_table');
		$userid = $_SESSION['login']['se_id'];
		$username = $_SESSION['login']['se_user'];
		$data = array(
			'rid'=>$rid,
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
		* 上传附件
		*@param $id  数据id值
		*@examlpe 
	*/
	public function upfile($id){
		$Public = A('Index','Public');
		$role = $Public->check('Report',array('c'));
		if($role<0){
			echo $role; exit;
		}
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
		$files_table = M('Report_files_table');
		$rid =  intval($id);
		$uid = $_SESSION['login']['se_id'];
		if($up->upload()){
			$info = $up->getUploadFileInfo();
			$data = array(
				'rid'=>$rid,
				'user_id'=>$uid,
				'path'=>$info[0]['savename'],
				'addtime'=>date("Y-m-d H:i:s",time()),
			);
			$add = $files_table->add($data);
		}else{
			$errorNo = $up->getErrorNo();
			if($errorNo!=2){
				echo $info = $up->getErrorMsg();
				exit;
			}
		}
		if($add>0){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
		* 删除附件
		*@examlpe 
	*/
	public function delfile(){
		$Public = A('Index','Public');
		$role = $Public->check('Report',array('d'));
		if($role<0){
			echo $role; exit;
		}
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
		
		//main
		$upload = C('TMPL_PARSE_STRING.__UPLOAD__');
		$files_table = M('Report_files_table');
		$id =  intval(I('id'));
		$uid =  intval(I('uid'));
		$file =  I('path');
		$path = ROOT.$upload.'/'.$file;
		$userid = $_SESSION['login']['se_id'];
		if($userid!=$uid){
			echo 2;
			exit;
		}
		$del = $files_table->delete($id);
		if($del==1){
			if($file && file_exists($path)){
				$sys->delFile($path);
			}
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
		* 修改指定人
		*@examlpe 
	*/
	public function setuser(){
		$Public = A('Index','Public');
		$role = $Public->check('Report',array('u'));
		
		//main
		$id = intval(I('id'));
		$comy = intval(I('comy'));
		$uid = intval(I('uid'));
		$Report = M('Report_table');
		$data = array(
			'comy'=>$comy,
			'user_id'=>$uid,
		);
		$edit = $Report->where('`id`='.$id)->save($data);
		if($edit !== false){
			$olduser = I('olduser');
			$data = $Report->field('bugno,pid,user_id')->where('id='.$id)->find();
			if($data['user_id']!=$olduser){
				//信息提醒功能
				if(C('SMS_AUTO_REPORT')){
					$sms = A('Sms','Public');
					$userid = $_SESSION['login']['se_id'];
					$username = $_SESSION['login']['se_user'];
					$title = '项目BUG指派提醒信息';
					$proname = $Public->GS('Project_table',$data['pid'],'name');
					$content = $username.'指派了您为关于项目：'.$proname.'中问题编号为：'.$data['bugno'].' 的问题负责人。';
					$sms->sendsms(array(
						'user_id'=>$userid,
						'title'=>$title,
						'description'=>$content,
					),$data['user_id']);
				}
			}
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
		* 修改状态
		*@examlpe 
	*/
	public function setstatus(){
		$Public = A('Index','Public');
		$App = A('App','Public');
		$role = $Public->check('Report',array('u'));
		
		//main
		$id = intval(I('id'));
		$atatus = intval(I('val'));
		$Report = M('Report_table');
		$data = array(
			'status'=>$atatus,
		);
		$edit = $Report->where('id='.$id)->save($data);
		if($edit !== false){
			$oldstatus = I('oldstatus');
			$data = $Report->field('bugno,pid,status,user_id')->where('id='.$id)->find();
			if($data['status']!=$oldstatus){
				//信息提醒功能
				if(C('SMS_AUTO_REPORT')){
					$sms = A('Sms','Public');
					$userid = $_SESSION['login']['se_id'];
					$username = $_SESSION['login']['se_user'];
					$title = '项目问题状态修改提醒信息';
					$proname = $Public->GS('Project_table',$data['pid'],'name');
					$arr_status = $App->getJson('wentizhuangtai','Linkage/','arr');
					$content = $username.'修改了关于项目：'.$proname.'中问题编号为：'.$data['bugno'].' 的问题状态->'.$arr_status[$atatus].'。';
					$sms->sendsms(array(
						'user_id'=>$userid,
						'title'=>$title,
						'description'=>$content,
					),$data['user_id']);
				}
			}
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
		* 删除反馈
		*@param $id  数据id值
		*@examlpe 
	*/
	public function delreply(){
		$Public = A('Index','Public');
		$role = $Public->check('Project',array('gd'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$id = intval(I('val'));
		$reply = M('Report_reply_table');
		
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
	
	/**
		* 动态获取用户列表
		*@param $id  数据id值
		*@examlpe 
	*/
	public function getUser($id){
		$id =intval($id);
		$user = C('DB_PREFIX').'user_table';
		$user_main = C('DB_PREFIX').'user_main_table';
		$result = M();
		
		if($id==0){
			$info = $result->cache(true)->table($user.' as t1')->field('t1.id as id,t1.username as text')->join(' '.$user_main.' as t2 on t1.id=t2.user_id')->where("t1.status=1")->order('convert(text using gbk) asc')->select();
		}else{
			if($id>1000){
				$len = strlen((string)$id);
				$id = substr((string)$id,3,$len);
				$info = $result->table($user.' as t1')->field('t1.id as id,t1.username as text')->join(' '.$user_main.' as t2 on t1.id=t2.user_id')->cache(true)->where("t1.status=1 and t2.company_id=".$id)->order('convert(text using gbk) asc')->select();
			}else{
				$info = $result->cache(true)->table($user.' as t1')->field('t1.id as id,t1.username as text')->join(' '.$user_main.' as t2 on t1.id=t2.user_id')->where("t1.status=1 and t2.part_id=".$id)->order('convert(text using gbk) asc')->select();
			}	
		}
		if(!$info){
			$info = array();
		}
		array_unshift($info,array(
			'id'=>0,
			'text'=>'无'
		));
		echo json_encode($info);
	}
}