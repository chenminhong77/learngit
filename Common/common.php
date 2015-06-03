<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
 

C('TMPL_PARSE_STRING.__INDEX__',INDEX);		//当前站点根路劲，不带域名，不带盘符
C('TMPL_PARSE_STRING.__ITEM__',ITEM);		//当前项目路劲，不带域名，不带盘符
C('TMPL_PARSE_STRING.__DOMAIN__',DOMAIN);		//当前项目所在域名
C('COOKIE_PREFIX','map');					//设置cookie前缀
session(array('path'=>CONF_PATH.'/Session','prefix'=>'map'));			//设置session前缀