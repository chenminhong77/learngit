<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */

class UserAction extends Action {
	/**
		* 用户列表
		*@param $json    为NULL输出模板。为1时输出列表数据到前端，格式为Json
		*@param $method  为1时，单独输出记录数
		*@examlpe 
	*/
    public function index($json=NULL,$method=NULL){
		$Public = A('Index','Public');		//加载IndexPublic类
		$Public->check('User',array('r'));	//用户检查
		
		//main
		if(!is_int((int)$json)){
			$json = NULL;
		}
		$view = C('DATAGRID_VIEW');		//获取试图状态
		$page_row = C('PAGE_ROW');		//获取默认显示条数
		if($json==1){
			$user = M('User_table');
			$get_sort = $this->_get('sort');
			$get_order = $this->_get('order');
			$sort = isset($get_sort) ? strval($get_sort) :  'username'; //，默认排序字段 
			$order = isset($get_order) ? strval($get_order) : 'asc';  //默认排序
			$user_table = C('DB_PREFIX').'user_table';
			$user_main = C('DB_PREFIX').'user_main_table';
			$part_table = C('DB_PREFIX').'user_part_table';
			$comy_table = C('DB_PREFIX').'user_company_table';
			$group_table = C('DB_PREFIX').'user_group_table';
			if(strstr($sort,'union_part')){
				$ta = $part_table;
				$fi = str_replace('union_part_','',$sort);
				$sort = $ta.'.'.$fi;
			}elseif(strstr($sort,'union_comy')){
				$ta = $comy_table;
				$fi = str_replace('union_comy_','',$sort);
				$sort = $ta.'.'.$fi;
			}elseif(strstr($sort,'union_group')){
				$ta = $group_table;
				$fi = str_replace('union_group_','',$sort);
				$sort = $ta.'.'.$fi;
			}else{
				$sort = $user_table.'.'.$sort;
			}
			unset($ta,$fi);
			cookie('sort_user',$sort);
			cookie('order_user',$order);
			
			$map = array();
			if(cookie('user') || cookie('auser')){
				if(cookie('user')){
					$str_map = cookie('user');
					$map = unserialize($str_map);
				}else{
					$str_map = cookie('auser');
					$map = unserialize($str_map);
				}
				unset($str_map);
			}else{
				$map[$user_table.'.id'] = array('neq',0);
				cookie('user',serialize($map));
			}
			
			//dump(unserialize(cookie('user')));
			$get_page = $this->_get('page');
			$get_rows = $this->_get('rows');
			$page = isset($get_page) ? intval($get_page) : 1;    
			$rows = isset($get_rows) ? intval($get_rows) : $page_row; 
			$now_page = $page-1;
			$offset = $now_page*$rows;
			
			if(strstr($sort,'login_count') || strstr($sort,'id')){
				$new_order = $sort.' '.$order;
			}else{
				$new_order = 'convert('.$sort.' using gbk) '.$order;
			}
			if(!$view){//不开启视图
				$info = $user->field($user_table.'.id,'.$user_table.'.username,'.$user_table.'.email,'.$user_table.'.login_count,'.$user_table.'.last_visit,'.$user_table.'.status,'.$user_table.'.report,'.$group_table.'.name as union_group_name,'.$part_table.'.name as union_part_name,'.$comy_table.'.name as union_comy_name')->join(' '.$user_main.' on '.$user_main.'.user_id = '.$user_table.'.id')->join(' '.$part_table.' on '.$part_table.'.id = '.$user_main.'.part_id')->join(' '.$comy_table.' on '.$comy_table.'.id = '.$user_main.'.company_id')->join(' '.$group_table.' on '.$group_table.'.id = '.$user_main.'.group_id')->where($map)->limit($offset,$rows)->order($new_order)->select();
				$count = $user->field($user_table.'.id,'.$user_table.'.username,'.$user_table.'.email,'.$user_table.'.login_count,'.$user_table.'.last_visit,'.$user_table.'.status,'.$user_table.'.report,'.$group_table.'.name as union_group_name,'.$part_table.'.name as union_part_name,'.$comy_table.'.name as union_comy_name')->join(' '.$user_main.' on '.$user_main.'.user_id = '.$user_table.'.id')->join(' '.$part_table.' on '.$part_table.'.id = '.$user_main.'.part_id')->join(' '.$comy_table.' on '.$comy_table.'.id = '.$user_main.'.company_id')->join(' '.$group_table.' on '.$group_table.'.id = '.$user_main.'.group_id')->where($map)->count();
			}else{//开启视图
				$info = $user->field($user_table.'.id,'.$user_table.'.username,'.$user_table.'.email,'.$user_table.'.login_count,'.$user_table.'.last_visit,'.$user_table.'.status,'.$user_table.'.report,'.$group_table.'.name as union_group_name,'.$part_table.'.name as union_part_name,'.$comy_table.'.name as union_comy_name')->join(' '.$user_main.' on '.$user_main.'.user_id = '.$user_table.'.id')->join(' '.$part_table.' on '.$part_table.'.id = '.$user_main.'.part_id')->join(' '.$comy_table.' on '.$comy_table.'.id = '.$user_main.'.company_id')->join(' '.$group_table.' on '.$group_table.'.id = '.$user_main.'.group_id')->where($map)->order($new_order)->select();
				$count = count($info);
			}
			//dump($info);exit;
			$new_info = array();
			$items = array();
			$new_info['total'] = $count;
			if($method=='total'){
				echo  json_encode($new_info); exit;
			}
			foreach($info as $t){
				if($t['last_visit']==0){
					$t['last_visit'] = '0000-00-00 00:00:00';
				}else{
					$t['last_visit'] = date("Y-m-d H:i:s",$t['last_visit']);
				}
				
				if($t['status']==1){
					$t['status'] = '开启';
				}else{
					$t['status'] = '关闭';
				}
				
				if($t['report']==1){
					$t['report'] = '否';
				}else{
					$t['report'] = '是';
				}
				$items[] = $t;
			}
			
			//$items = array_sort($items,$sort,$mode='nokeep',$type=$order);
			
			$new_info['rows'] = $items;
			//dump($new_info);
			echo json_encode($new_info);
			
			unset($new_info,$info,$comy,$order,$sort,$count,$items);
		}else{
			$this->assign('page_row',$page_row);
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
		//main
		$user = D('User_table');
		if($go==false){
			$this->assign('uniqid',uniqid());
			if($act=='add'){
				$this->assign('act','add');
				$this->display();
			}else{
				$userid = $_SESSION['login']['se_id'];
				$userid = intval($userid);
				if(!is_int((int)$id)){
					$id = NULL;
					$this->show('无法获取ID');
				}else{
					$map['id'] = array('eq',$id);
					$info = $user->relation('user_main')->where($map)->find();
					unset($map);
					//dump($info);
					$this->assign('userid',$userid);
					$this->assign('id',$id);
					$this->assign('act','edit');
					$this->assign('info',$info);
					$this->display();
					unset($info);
				}
			}	
		}else{
			$data = $user->create();
			$data['date_created'] = time();
			if($data['realname']==''){
				$data['realname'] = $data['username'];
			}
			$data['user_main'] = array(
				'part_id'=>I('part_id'),
				'company_id'=>I('company_id'),
				'group_id'=>I('group_id'),
			);
			//dump($data);exit;
			if($act=='add'){
				$Public = A('Index','Public');
				$role = $Public->check('User',array('c'));
				if($role<0){
					echo $role; exit;
				}
				
				if($data['password']){
					$oldpwd = $data['password'];
					$data['password'] = md5($data['password']);
				}else{
					$rand_pwd = randnum(6);
					$oldpwd = $rand_pwd;
					$data['password'] = md5($rand_pwd);
				}
				
				if(C('USER_TO_MAIL')){
					$Mailer = A('Mail','Public');
					$to = $data['email'];
					$title = '帐号分派通知';
					$name = $data['username'];
					$notes = $data['username'];
					$m_cfg = array(
						'server'=>C('MAIL_SMTP_SEAVER'),
						'ssl'=>C('MAIL_SMTP_SSL'),
						'port'=>C('MAIL_SMTP_PORT'),
						'user'=>C('MAIL_SMTP_USER'),
						'pwd'=>C('MAIL_SMTP_PWD'),
						'name'=>C('MAIL_SMTP_NAME'),
					);
					$host = C('CFG_HOST');
					$contents = '<p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">您好：</span></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">管理员已为你分派了一个新的的帐号</span></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">帐号：'.$name.' &nbsp; &nbsp; &nbsp; &nbsp;密码：'.$oldpwd.'</span></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">登录地址：</span><a target="_blank" href="'.$host.'">'.$host.'</a></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">此邮件由系统自动发送，请不要回复，如有问题请联系系统管理员！</span></p>';
					$send = $Mailer->set($title,$contents,$m_cfg);
					$Mailer->mailObj->AddAddress($to, $notes);
					$send = $Mailer->mailObj->send();
					$Mailer->mailObj->ClearAddresses();
					if($send==1){
						$mail = 1;
					}else{
						$mail = $Mailer->mailObj->ErrorInfo;
					}
					$Mailer->mailObj->ClearAddresses();
				}else{
					$mail = 1;
				}
				if($mail==1){
					$add = $user->relation(true)->add($data);
					if($add>0){
						echo 1;
						$this->json(NULL);
					}else{
						echo 0;
					}
				}else{
					echo 2;
				}
				unset($data,$Public);
			}elseif($act=='edit'){
				$Public = A('Index','Public');
				$role = $Public->check('User',array('u'));
				if($role<0){
					echo $role; exit;
				}
				
				if(!is_int((int)$id)){
					echo 0;
				}else{
					if($data['password']){
						$data['password'] = md5($data['password']);
					}else{
						unset($data['password']);
					}
					$map['id'] = array('eq',$id);
					$edit = $user->relation(true)->where($map)->save($data);
					unset($map);
					if($edit !== false){
						$this->json(NULL);
						echo 1;
					}else{
						echo 0;
					}
					unset($data,$Public);
				}
			}
		}
		unset($user);
	}
	
	
	/**
		* 删除数据
		*@examlpe 
	*/
	public function del(){
		$Public = A('Index','Public');
		$role = $Public->check('User',array('d'));
		if($role<0){
			echo $role; exit;
		}
		
		//main
		$str_id = I('id');
		$str_id = strval($str_id);
		$str_id = substr($str_id,0,-1);
		$arr_id = explode(',',$str_id);
		$user = M('User_table');
		$pass = 0;$fail = 0;
		foreach($arr_id as $id){
			$map['id'] = array('eq',$id);
			$del = $user->where($map)->delete();
			if($del){
				$pass++;
			}else{
				$fail++;
			}
		}
		unset($map,$str_id,$arr_id);
		if($pass==0){
			echo 0;
		}else{
			$this->json(NULL);
			echo 1;
		}
		$pass = 0; $fail = 0;
		unset($user,$Public);
	}
	
	/**
		* 更改用户密码
		*@param $go  为1时，获取post
		*@param $id  传人数据id
		*@examlpe 
	*/
	public function repwd($id,$go=false){		
		//main
		$user = D('User_table');
		if(!$go){
			if(!is_int((int)$id)){
				$id = NULL;
				$this->show('无法获取ID');
			}else{
				$map['id'] = array('eq',$id);
				$info = $user->relation(true)->where($map)->find();
				unset($map);
				$this->assign('id',$id);
				$this->assign('info',$info);
				$this->display();
				unset($info);
			}		
		}else{
			$data = $user->create();
			if(!is_int((int)$id)){
				echo 0;
			}else{
				$pwd2 = I('password2');
				if($data['password']!=$pwd2){
					echo -1;
				}else{
					$data['password'] = md5($data['password']);
					$map['id'] = array('eq',$id);
					$edit = $user->where($map)->save($data);
					unset($map);
					if($edit !== false){
						echo 1;
					}else{
						echo 0;
					}
				}
			}
			unset($data);
		}
		unset($user);	
	}
	
	/**
		* 设置邮箱密码
		*@param $go  为1时，获取post
		*@param $id  传人数据id
		*@examlpe 
	*/
	public function setpwd($id,$go=false){		
		//main
		$user = D('User_table');
		$comy = D('User_company_table');
		if(!$go){
			if(!is_int((int)$id)){
				$id = NULL;
				$this->show('无法获取ID');
			}else{
				$map['id'] = array('eq',$id);
				$info = $user->relation(true)->where($map)->find();
				$cinfo = $comy->where('id='.$info['user_main']['company_id'])->find();
				if(!C('MAIL_OF_USER')){
					if($id>1 || ($id==1 && !$info['smtp'])){
						if(C('MORE_COMY') && $cinfo['smtp']){
							$info['smtp'] = $cinfo['smtp'];
							$info['ssl'] = $cinfo['ssl'];
							$info['port'] = $cinfo['port'];
						}else{
							$info['smtp'] = C('MAIL_SMTP_SEAVER');
							$info['ssl'] = C('MAIL_SMTP_SSL');
							$info['port'] = C('MAIL_SMTP_PORT');
						}
					}
				}else{
					if(!$info['smtp']){
						if(C('MORE_COMY') && $cinfo['smtp']){
							$info['smtp'] = $cinfo['smtp'];
							$info['ssl'] = $cinfo['ssl'];
							$info['port'] = $cinfo['port'];
						}else{
							$info['smtp'] = C('MAIL_SMTP_SEAVER');
							$info['ssl'] = C('MAIL_SMTP_SSL');
							$info['port'] = C('MAIL_SMTP_PORT');
						}
					}
				}
				unset($map);
				$this->assign('id',$id);
				$this->assign('info',$info);
				$this->display();
				unset($info);
			}		
		}else{
			if(!is_int((int)$id)){
				echo 0;
			}else{
				$email = I('email');
				if(C('MAIL_OF_USER')){
					$smtp = I('smtp');
					$ssl = I('ssl');
					$port = I('port');
				}
				$MailPwd2 = I('MailPwd2');
				$MailPwd = I('MailPwd');
				if($MailPwd!=$MailPwd2){
					echo -1;
				}else{
					$map['id'] = array('eq',$id);
					$data['email'] = $email;
					$data['MailPwd'] = $MailPwd;
					if(C('MAIL_OF_USER')){			
						$data['smtp'] = $smtp;
						$data['ssl'] = $ssl;
						$data['port'] = $port;
					}else{
						if($id==1){
							$data['smtp'] = $smtp;
							$data['ssl'] = $ssl;
							$data['port'] = $port;
						}
					}
					
					$edit = $user->where($map)->save($data);
					unset($map);
					if($edit !== false){
						echo 1;
					}else{
						echo 0;
					}
				}
			}
			unset($data);
		}
		unset($user);	
	}
	
	//无效方法
	public function setmail(){		
		//main
		$user = D('User_table');
		$mailpwd = I('mailpwd');
		$userid = I('id');
		$mailpwd = strval($mailpwd);
		$data = array(
			'MailPwd'=>$mailpwd
		);
		$edit = $user->where('id='.$userid)->save($data);
		if($edit==1){
			echo 1;
		}else{
			echo 0;
		}
		unset($user,$data);	
	}
	
	
	/**
		* 重置用户密码
		*@param $id  传人数据id
		*@examlpe 
	*/
	public function rspwd($id){	
		$Mailer = A('Mail','Public');
			
		//main
		$user = D('User_table');
		if(!is_int((int)$id)){
			echo 0;
		}else{
			$rand_pwd = randnum(6);
			$data['password'] = md5($rand_pwd);
			$map['id'] = array('eq',$id);
			$info = $user->where($map)->find();
			$edit = $user->where($map)->save($data);
			
			if($edit !== false){
				$to = $info['email'];
				$title = '重置密码通知';
				$name = $info['username'];
				$notes = 'Dear '.$info['username'];
				$m_cfg = array(
					'server'=>C('MAIL_SMTP_SEAVER'),
					'ssl'=>C('MAIL_SMTP_SSL'),
					'port'=>C('MAIL_SMTP_PORT'),
					'user'=>C('MAIL_SMTP_USER'),
					'pwd'=>C('MAIL_SMTP_PWD'),
					'name'=>C('MAIL_SMTP_NAME'),
				);
				$host = C('CFG_HOST');
				$contents = '<p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">您好：</span></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">天启科技协同办公管理系统的密码已被重置</span></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">帐号：'.$name.' &nbsp; &nbsp; &nbsp; &nbsp;密码：'.$rand_pwd.'</span></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">登录地址：</span><a target="_blank" href="'.$host.'">'.$host.'</a></p><p><span style="color: rgb(51, 51, 51); font-family: verdana, Tahoma, Arial, 宋体, sans-serif; font-size: 14px; ">此邮件由系统自动发送，请不要回复，如有问题请联系系统管理员！</span></p>';
				$send = $Mailer->set($title,$contents,$m_cfg);
				$Mailer->mailObj->AddAddress($to, $notes);
				$send = $Mailer->mailObj->send();
				$Mailer->mailObj->ClearAddresses();
				unset($Mailer,$m_cfg,$notes,$name,$to,$title,$contents,$data);
				if($send==1){
					echo 1;
				}else{
					$data['password'] = $info['password'];
					$map['id'] = array('eq',$id);
					$edit = $user->where($map)->save($data);
					echo 2;
				}
			}else{
				echo 0;
			}
		}
		unset($data,$user,$map,$info);
	}
	
	
	/**
		* 右键快速搜索
		*@param $field  传入搜索字段
		*@param $json 为1时，输出json数据
		*@param $act   为1时，获取post
		*@examlpe 
	*/
	public function search($field=NULL,$json=NULL,$act=NULL){	
		//main
		$field = strval($field);
		$user = M('user_table');
		$user_table = C('DB_PREFIX').'user_table';
		$user_main = C('DB_PREFIX').'user_main_table';
		$part_table = C('DB_PREFIX').'user_part_table';
		$comy_table = C('DB_PREFIX').'user_company_table';
		$group_table = C('DB_PREFIX').'user_group_table';
		
		if($act==1){
			$keyfield = I('field');
			$mod = I('mod');
			$keyword = I('keyuser');	
			if(strstr($keyfield,'union_part')){
				$table = $part_table;
				$keyfield = str_replace('union_part_','',$keyfield);
			}elseif(strstr($keyfield,'union_comy')){
				$table = $comy_table;
				$keyfield = str_replace('union_comy_','',$keyfield);
			}elseif(strstr($keyfield,'union_group')){
				$table = $group_table;
				$keyfield = str_replace('union_group_','',$keyfield);
			}else{
				$table = $user_table;
			}
			
			if($mod=='like' || $mod=='notlike'){
				$keyword = '%'.$keyword.'%';
			}
			
			$map = array();
			if(cookie('user')){
				$str_map = cookie('user');
				$map = unserialize($str_map);	
			}else{
				$map[$user_table.'.id'] = array('neq',0);
			}
			if($keyword=='clearThisValue'){
				unset($map[$table.'.'.$keyfield]);
			}else{
				$map[$table.'.'.$keyfield] = array($mod,$keyword);
			}
			cookie('auser',NULL);
			cookie('user',serialize($map));
			unset($str_map,$map,$keyfield,$keyword,$mod,$table);
			echo 1;
		}else{
			if($json==1){
				if(strstr($field,'union_part')){
					$table = $part_table;
					$field = str_replace('union_part_','',$field);
				}elseif(strstr($field,'union_comy')){
					$table = $comy_table;
					$field = str_replace('union_comy_','',$field);
				}elseif(strstr($field,'union_group')){
					$table = $group_table;
					$field = str_replace('union_group_','',$field);
				}else{
					$table = $user_table;
				}
				
				$map = array();
				if(cookie('user') || cookie('auser')){
					if(cookie('user')){
						$str_map = cookie('user');
						$map = unserialize($str_map);
					}else{
						$str_map = cookie('auser');
						$map = unserialize($str_map);
					}
					unset($str_map);
				}else{
					$map[$user_table.'.id'] = array('neq',0);
				}
				$map[$table.'.'.$field] = array('neq','');
				
				$info = $user->field($table.'.'.$field.' as id,'.$table.'.'.$field.' as text')->join(' '.$user_main.' on '.$user_main.'.user_id = '.$user_table.'.id')->join(' '.$part_table.' on '.$part_table.'.id = '.$user_main.'.part_id')->join(' '.$comy_table.' on '.$comy_table.'.id = '.$user_main.'.company_id')->join(' '.$group_table.' on '.$group_table.'.id = '.$user_main.'.group_id')->where($map)->order('convert('.$table.'.'.$field.' using gbk)')->group($table.'.'.$field)->select();
				//dump($info);
				if($field=='status'){
					for($i=0;$i<count($info);$i++){
						if($info[$i]['id']=='1'){
							$info[$i]['text'] = '开启';
						}else{
							$info[$i]['text'] = '关闭';
						}
					}
				}
				
				if($field=='report'){
					for($i=0;$i<count($info);$i++){
						if($info[$i]['id']=='1'){
							$info[$i]['text'] = '否';
						}else{
							$info[$i]['text'] = '是';
						}
					}
				}
				array_unshift($info,array('id'=>"clearThisValue",'text'=>"所有"));
				echo json_encode($info);
				unset($info,$user,$field,$map);
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
		//main
		$field = strval($field);
		if($act==1){
			$field = I('field');
			$mod = I('mod');
			$keyword = I('keys');	
			$type = I('type');
			array_pop($field); array_pop($mod); array_pop($keyword); array_pop($type);
			
			$user_table = C('DB_PREFIX').'user_table';
			$user_main = C('DB_PREFIX').'user_main_table';
			$part_table = C('DB_PREFIX').'user_part_table';
			$comy_table = C('DB_PREFIX').'user_company_table';
			$group_table = C('DB_PREFIX').'user_group_table';
			
			$del = array_pop($type);
			
			$arr = array();
			$num = 0;
			foreach($field as $key=>$val){
				if($mod[$key]=='like' || $mod[$key]=='notlike'){
					$keyword[$key] = '%'.$keyword[$key].'%';
				}
				$tt = trim($type[$key]);
				$n = $key+1;
				$l = $key-1;
				$nt = trim($type[$n]);
				$lt = trim($type[$l]);
				if(strstr($val,'union_part')){
					$table = $part_table;
					$val = str_replace('union_part_','',$val);
				}elseif(strstr($val,'union_comy')){
					$table = $comy_table;
					$val = str_replace('union_comy_','',$val);
				}elseif(strstr($val,'union_group')){
					$table = $group_table;
					$val = str_replace('union_group_','',$val);
				}else{
					$table = $user_table;
				}
				
				if($tt=='OR'){
					if($keyword[$key]){
						$arr[$num]['k'][] = $table.'.'.$val;
						$arr[$num]['v'][] = array($mod[$key],$keyword[$key]);
					}
					if($nt=='AND'){
						if($mod[$n]=='like' || $mod[$n]=='notlike'){
							$keyword[$n] = '%'.$keyword[$n].'%';
						}
						if(strstr($field[$n],'union_part')){
							$ntable = $part_table;
							$nval = str_replace('union_part_','',$field[$n]);
						}elseif(strstr($field[$n],'union_comy')){
							$ntable = $comy_table;
							$nval = str_replace('union_comy_','',$field[$n]);
						}elseif(strstr($field[$n],'union_group')){
							$ntable = $group_table;
							$nval = str_replace('union_group_','',$field[$n]);
						}else{
							$ntable = $user_table;
						}
						if($keyword[$n]){
							$arr[$num]['k'][] = $ntable.'.'.$nval;
							$arr[$num]['v'][] = array($mod[$n],$keyword[$n]);
						}
						$num++;
					}
				}else{
					if($lt!='OR' && $tt=='AND'){
						if($keyword[$key]){
							$map[$table.'.'.$val] = array($mod[$key],$keyword[$key]);
						}
					}
				}
				
				if(!isset($type[$key]) && $lt=='OR'){
					if($keyword[$key]){
						$arr[$num]['k'][] = $table.'.'.$val;
						$arr[$num]['v'][] = array($mod[$key],$keyword[$key]);
					}
				}else{
					if(!isset($type[$key]) && $lt!='OR'){
						if($keyword[$key]){
							$map[$table.'.'.$val] = array($mod[$key],$keyword[$key]);
						}
					}
				}
			}
			$num = 0;
			unset($key,$val,$ntable,$table,$field,$mod,$type,$keyword);
			
			foreach($arr as $key=>$val){
				$str = implode('|',$val['k']);
				$map[$str] = $val['v'];
				$map[$str]['_multi'] = true;
			}
			unset($arr);
			cookie('user',NULL);
			cookie('auser',serialize($map));
			unset($map);
			echo 1;
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
    	cookie('user',NULL);
		cookie('auser',NULL);
	}
	
	/**
		* 导出数据为excel表格
		*@param $data    一个二维数组,结构如同从数据库查出来的数组
		*@param $title   excel的第一行标题,一个数组,如果为空则没有标题
		*@param $filename 下载的文件名
		*@examlpe 
		exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
	*/
	public function excel(){
		
		$char = C('CFG_CHARSET');
		$filename = '用户列表-'.date("Ymd",time());
		if($char=='UTF-8'){
			$filename = iconv("UTF-8", "GB2312",$filename);
		}
		
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel");  
		header("Content-Disposition:attachment;filename=".$filename.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		//导出xls 开始
		$title = array('角色','帐号','公司','部门','邮箱','登录次数','最后登录时间','状态');
		$title = array_iconv("UTF-8", "GB2312",$title);
		$title= implode("\t", $title);
		echo "$title\n";
		unset($title);
		
		$user = M('User_table');
		$user_table = C('DB_PREFIX').'user_table';
		$user_main = C('DB_PREFIX').'user_main_table';
		$part_table = C('DB_PREFIX').'user_part_table';
		$comy_table = C('DB_PREFIX').'user_company_table';
		$group_table = C('DB_PREFIX').'user_group_table';
		$sort = cookie('sort_user');
		$order = cookie('order_user');
		$map = array();
		if(cookie('user') || cookie('auser')){
			if(cookie('user')){
				$str_map = cookie('user');
				$map = unserialize($str_map);
			}else{
				$str_map = cookie('auser');
				$map = unserialize($str_map);
			}
			unset($str_map);
		}else{
			$map[$user_table.'.id'] = array('neq',0);
		}
		
		if(strstr($sort,'login_count') || strstr($sort,'id')){
			$new_order = $sort.' '.$order;
		}else{
			$new_order = 'convert('.$sort.' using gbk) '.$order;
		}
		$data = $user->field($group_table.'.name as union_group_name,'.$user_table.'.username,'.$part_table.'.name as union_part_name,'.$comy_table.'.name as union_comy_name,'.$user_table.'.email,'.$user_table.'.login_count,'.$user_table.'.last_visit,'.$user_table.'.status')->join(' '.$user_main.' on '.$user_main.'.user_id = '.$user_table.'.id')->join(' '.$part_table.' on '.$part_table.'.id = '.$user_main.'.part_id')->join(' '.$comy_table.' on '.$comy_table.'.id = '.$user_main.'.company_id')->join(' '.$group_table.' on '.$group_table.'.id = '.$user_main.'.group_id')->where($map)->order($new_order)->select();
		//dump($data);exit;
		
		foreach($data as $key=>$t){
			if($t['last_visit']==0){
				$t['last_visit'] = '0000-00-00';
			}else{
				$t['last_visit'] = date("Y-m-d",$t['last_visit']);
			}
			
			if($t['status']==1){
				$t['status'] = '开启';
			}else{
				$t['status'] = '关闭';
			}
			$data[$key]=implode("\t", array_iconv("UTF-8", "GB2312",$t));
		}
		echo implode("\n",$data);
		unset($user,$user_main,$user_table,$part_table,$comy_table,$group_table,$data);
	}
	
	
	/**
		* 生成json文件
		*@param $back  为1时，返回数据
		*@examlpe 
	*/
	public function json($back=1){
		$Write = A('Write','Public');
		import('ORG.Net.FileSystem');
		$sys = new FileSystem();
	
		//main
    	$temp_path = RUNTIME_PATH.'/Temp/';
		if(file_exists($temp_path)){
			$dt = $sys->delFile($temp_path);
		}
		$user = M('User_table');
		$info = $user->field('id,username as text')->where('status=1')->order('convert(text using gbk) asc')->select();
		//array_unshift($info,$head);
		$json_data = json_encode($info);
		//dump($info);
		$path = RUNTIME_PATH.'Data/Json';
		$put_json = $Write->write($path,$json_data,'User_data');
		
		$info = $user->field('id as id,username as text')->where('status=1')->order('convert(text using gbk) asc')->select();
		$head = array(
			'id'=>0,
			'text'=>'无'
		);
		array_unshift($info,$head);
		$json_data = json_encode($info);
		$put_json2 = $Write->write($path,$json_data,'User_2_data');
		
		$info = $user->field('username as id,username as text')->where('status=1')->order('convert(text using gbk) asc')->select();
		$json_data = json_encode($info);
		$put_json4 = $Write->write($path,$json_data,'User_name_data');
		
		$info = $user->field('id,username as text')->where('status=1 and id<>1')->order('convert(text using gbk) asc')->select();
		//array_unshift($info,$head);
		$json_data = json_encode($info);
		$put_json3 = $Write->write($path,$json_data,'User_noadmin_data');
		
		if($back==1){
			if($put_json && $put_json2 && $put_json3 && $put_json4){
				echo 1;
			}
		}
		unset($info,$json_data,$path,$Loop,$Write,$sys);
	}
	
	/**
		* 工具栏搜索控制
		*@param $act  传入的字段名
		*@examlpe 
	*/
	public function change($act){
		$val = I('val');
		$user_table = C('DB_PREFIX').'user_table';
		switch($act){
			case 'username':
				$map[$user_table.'.username'] = array('eq',$val);
				cookie('user',serialize($map));
			break;
		}
	}
}