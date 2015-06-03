<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
class Project_progress_tableModel extends RelationModel{
	protected $_link = array(
		'comment'=>array(
			'mapping_type'=>HAS_MANY,
			'mapping_name'=>'comment',
			'class_name'=>'project_progress_table',
			'parent_key'=>'status',
		),
		'project'=>array(
			'mapping_type'=>BELONGS_TO,
			'mapping_name'=>'project',
			'class_name'=>'project_table',
			'foreign_key'=>'pro_id',
			'as_fields'=>'name:proname',
		),
	);
}