<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */

class Trip_dailyModel extends RelationModel{
	protected $_link = array(
		'user'=>array(
			'mapping_type'=>BELONGS_TO,
			'mapping_name'=>'user',
			'class_name'=>'user_table',
			'foreign_key'=>'checker',
			'as_fields'=>'username:checker_name',
		),
	);
}