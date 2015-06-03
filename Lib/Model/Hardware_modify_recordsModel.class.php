<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
class Hardware_modify_recordsModel extends RelationModel{
	protected $_link = array(
		'modify_user'=>array(
			'mapping_type'=>BELONGS_TO,
			'mapping_name'=>'pro_client',
			'class_name'=>'user_table',
			'foreign_key'=>'modify_user',
			'as_fields'=>'username:modify_user',
		),
		'modify_pro'=>array(
			'mapping_type'=>BELONGS_TO,
			'mapping_name'=>'pro_name',
			'class_name'=>'project_table',
			'foreign_key'=>'pro_id',
			'as_fields'=>'name:proname',
		),
		'modify_detail'=>array(
			'mapping_type'=>HAS_MANY,
			'mapping_name'=>'modify_detail',
			'class_name'=>'hardware_modify_details',
			'foreign_key'=>'record_id',
			'mapping_fields'=>'id,detail,reason',
		),
	);
}