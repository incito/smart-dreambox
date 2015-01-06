<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 * 应用的游客配置，转移到apps/app_name/Conf/access.inc.php下
 * 此处只配置不能后台修改的项目
 */
return array (
	"access" => array (
		'public/Profile/*' => true, // 个人主页
		'photo/Index/*' => true, // 相册
		'public/Register/*' => true, // 注册
		'public/Passport/*' => true, // 登录
		'public/Widget/*'	=> true, // 插件
		'public/Index/index'	=> true, // 首页
		'dreambox/Course/previewCourse'	=> true, // 预览课表
		'jijinhui/Index/index'	=> true, // 公告
		'jijinhui/Index/descindex'	=> true, // 公告
		'public/Index/card'	=> true, // 插件
		'page/Index/index'	=> true, // 自定义页面
		'public/Tool/*' 	=> true, // 升级查询
		'api/*/*' 			=> true, // API
// 		'wap/*/*' 			=> true, // wap版
// 		'w3g/*/*' 			=> true, // 3G版
	)
);