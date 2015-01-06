<?php

return array (
		'THEME_NAME' => 'stv1',
		// 数据库常用配置
		'DB_TYPE' => 'mysql', // 数据库类型
		
		'DB_HOST' => '127.0.0.1', // 数据库服务器地址
		'DB_NAME' => 'dreambox', // 数据库名
		'DB_USER' => 'root', // 数据库用户名
		'DB_PWD' => 'root', // 数据库密码
		
		'DB_PORT' => 3306, // 数据库端口
		'DB_PREFIX' => 'ts_', // 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
		'DB_CHARSET' => 'utf8', // 数据库编码
		'SECURE_CODE' => '29771538ee8c5b73bd', // 数据加密密钥
		'COOKIE_PREFIX' => 'T3_', // 数据加密密钥
// 		'APP_DEBUG'=>'TRUE'
);