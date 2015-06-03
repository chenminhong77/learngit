<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */

class Bug_tableModel extends RelationModel{
	protected $_link = array(
		'baseinfo'=>array(
			'mapping_type'=>HAS_ONE,
			'mapping_name'=>'baseinfo',
			'class_name'=>'bug_baseinfo_table',
			'foreign_key'=>'bug_id',
		),
		'fulltext'=>array(
			'mapping_type'=>HAS_ONE,
			'mapping_name'=>'fulltext',
			'class_name'=>'bug_fulltext_table',
			'foreign_key'=>'bug_id',
		),
		'user'=>array(
			'mapping_type'=>BELONGS_TO,
			'mapping_name'=>'user',
			'class_name'=>'user_table',
			'foreign_key'=>'user_id',
		),
		'reply'=>array(
			'mapping_type'=>HAS_MANY,
			'mapping_name'=>'reply',
			'class_name'=>'bug_reply_table',
			'mapping_order'=>'replytime',
			'foreign_key'=>'bug_id',
		),
	);
}