<?php
define ( 'TOKEN', 'adreambox' );
// 测试环境
// define ( 'WX_ID', 'gh_08af83df391a' );
// define ( 'APPID', 'wx3e755cbdca08428f' );
// define ( 'APPSECRET', '9a1d323f15b314ca6118d96cbd0c36d2' );
// 梦想盒子
define ( 'WX_ID', 'gh_384357884652' );
define ( 'APPID', 'wx8e07f3dae08c1071' );
define ( 'APPSECRET', '2d90e42192c75039ccce8725e31d9dfe' );
return array (
		'api_url' => array (
				'access_token' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . APPID . '&secret=' . APPSECRET,
				'create_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/create',
				'delete_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/delete',
				'query_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/get',
				'query_user_info' => 'https://api.weixin.qq.com/cgi-bin/user/info',
				'get_ticket' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create',
				'oauth2_access_token' => 'https://api.weixin.qq.com/sns/oauth2/access_token' 
		),
		'html_uri' => array (
				'sign' => '/html/wechat/#!/selectCourse?',
				'index' => '/html/wechat/#!/intro?',
				'changeAccount' => '?app=wechat&mod=Account&act=unLogin&',
				'sendBlog' => '/html/wechat/#!/share?',
				'queryMoney' => '/html/wechat/#!/redEnvelopes?' 
		) 
);