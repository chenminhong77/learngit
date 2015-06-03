<?php
/*
 * @varsion		Dream项目管理系统 1.1var
 * @package		程序设计由梦赢科技设计开发
 * @copyright	Copyright (c) 2010 - 2014, d-winner, Inc.
 * @link		http://www.d-winner.com
 */
if(file_exists('lock.txt')){
    echo '系统已安装，请不要重复安装！如需安装，请删除install文件夹下的lock.txt文件。';
    exit();
}else{
	header('Location:install.php');
    exit();
}
