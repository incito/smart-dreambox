<?php
if (!defined('SITE_PATH')) exit();

header('Content-Type: text/html; charset=utf-8');

$sql_file  = APPS_PATH.'/photo/Appinfo/install.sql';
//执行sql文件
