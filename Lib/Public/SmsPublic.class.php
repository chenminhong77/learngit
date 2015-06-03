<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */

//附加类
class SmsPublic extends Action {
	/*
	发送站内信
	return String
	$data		传入的内容数据，传入数组  array(user_id=>'发信人ID',title=>'标题',description=>'摘要');
	$uid		接收信息的用户ID，传入数组
	*/
	public function sendsms($data,$uid){
		if($uid){
			$sms = D('Sms_table');
			$receive = M('Sms_receive_table');
			$data['baseinfo'] = array(
				'description'=>$data['description']
			);
			$data['sendtime'] = date("Y-m-d H:i:s",time());
			unset($data['description']);
			$add = $sms->relation('baseinfo')->add($data);
			if($add>0){
				if(is_array($uid)){
					foreach($uid as $t){
						$datas = array(
							'sms_id'=>$add,
							'user_id'=>$t['user_id']
						);
						$receive->add($datas);
					}
				}else{
					$datas = array(
						'sms_id'=>$add,
						'user_id'=>$uid
					);
					$receive->add($datas);
				}
				return $add;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}
}