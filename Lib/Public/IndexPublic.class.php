<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */

class IndexPublic extends Action {
	//检查登录状态
    public function check($mode,$role){
		$user = D('User_table');								//实例化用户表
		$ison = C('CFG_ON');									//获取程序运行状态
		$sess['user'] = $_SESSION['login']['se_user'];			//用户名session
		$sess['id'] = $_SESSION['login']['se_id'];				//用户IDseeeion
		$sess['role'] = $role;									//接受到的权限
		$sess['mode'] = $mode;									//对应控制器识别ID
		$sess['comy'] = C('MORE_COMY');							//子公司开启状态
		$sess['path'] = CONF_PATH.'Role/'.$mode.'Role.php';		//权限控制配置文件路径
		
		//检查用户登录及权限并返回值
		$info = $user->checkUser($sess);
		//dump($info);exit;
		if($info!='all' && !C('CFG_ON')){
			echo $this->success('系统正在维护中...',U(GROUP_NAME.'/Index/login'));
		}
		if($info===0){
			echo "<script>$.messager.alert('提示','此页面需要登录后才能访问！','warning',function(){window.location='".U(GROUP_NAME.'/Index/login')."'});</script>";exit;																		//验证出没有登陆状态
		}elseif($info==-1){
			echo "<script>$.messager.alert('提示','您没有访问权限！','warning');</script>";exit;			//没有查看权限
		}elseif($info==-2){
			return -2;																					//没有新增权限
		}elseif($info==-3){
			return -3;																					//没有修改权限
		}elseif($info==-4){
			return -4;																					//没有删除权限
		}elseif($info==-5){
			return -5;																					//没有审核权限
		}elseif($info==-6){
			echo "<script>$.messager.alert('提示','找不到账户所属公司的权限！','warning');</script>";exit;	//找不到所属公司
		}elseif($info==-7){
			echo "<script>$.messager.alert('提示','找不到账户所属部门的权限！','warning');</script>";exit;	//找不到所属部门
		}elseif($info==-98){
			$this->success('此页面需要登录后才能访问！',U(GROUP_NAME.'/Index/login'));		//主页登陆失败，THINKPHP返回登陆页
		}elseif($info==-99){
			echo "<script>$.messager.alert('提示','此页面需要登录后才能访问！','warning',function(){window.location='".U(GROUP_NAME.'/Index/login')."'});</script>";exit;																		//主页登陆失败，EASYUI返回登陆页
		}elseif(is_array($info) || $info=='all' || $info=='pass'){
			return $info;																				//验证成功
		}
    }
	
	//权限获取
	static public function GS($mode,$id,$field='access'){
		$result = M($mode);
		$access = $result->cache(true)->where('id="'.$id.'"')->getField($field);
		return $access;
		unset($access,$result);
	}
	
	//获取对应的邮件服务器
	public function MC(){
		$userid = $_SESSION['login']['se_id'];
		$user = D('User_table');
		$comy = D('User_company_table');
		$info = $user->cache(true)->field('id,username,email,MailPwd,smtp,ssl,port')->where('id='.$userid)->relation('user_main')->find();
		$cinfo = $comy->cache(true)->where('id='.$info['user_main']['company_id'])->find();
		if(!C('MAIL_OF_USER')){
			if($userid>1 || ($userid==1 && !$info['smtp'])){
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
		$mail_cfg = array(
			'username'=>$info['username'],
			'email'=>$info['email'],
			'pwd'=>$info['MailPwd'],
			'smtp'=>$info['smtp'],
			'ssl'=>$info['ssl'],
			'port'=>$info['port'],
		);
		return $mail_cfg;
	}
}