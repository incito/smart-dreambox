<?php
$menu = array(
    //后台头部TAB配置
	'admin_channel'	=>	array(
		'index'		=>	'首页', //L('PUBLIC_SYSTEM'),
		'system'	=>	'系统',
		'user'		=>	'用户',
		'content'	=>	'内容',
		'task'		=>	'任务',
		'apps'		=>	'应用',
		//'extends'	=>	'插件',//L('PUBLIC_EXPANSION'),
	),
	//后台菜单配置
	'admin_menu'	=> array(
		'index'	=> array(
			'首页'	=> array(
				'基本信息'	=>	U('admin/Home/statistics'),
				'访问统计'	=>	U('admin/Home/visitorCount'),
				//'资源统计'	=>	U('admin/Home/sourcesCount'),
				'管理日志'	=>	U('admin/Home/logs'),
				'群发消息'	=>	U('admin/Home/message'),//L('PUBLIC_MESSAGE_NOTIFY')	=>	U('admin/Home/message'),
				'计划任务'	=>  U('admin/Home/schedule'),
				//'数据字典'	=>	U('admin/Home/systemdata'),	
				'缓存清理'	=>  U('admin/Tool/cleancache'),
// 				'缓存配置'				=> U('admin/Home/cacheConfig'),
				'数据备份'				=> U('admin/Tool/backup'),
// 				'在线升级'				=> U('admin/Update/index'),
				'小工具'				=> U('admin/Tool/index'),					
			)
		),

		'system'	=> array(
			'系统配置'	=>	array(
				'站点配置'	=>	U('admin/Config/site'),
				'导航配置'	=>	U('admin/Config/nav'),
				'注册配置'	=>	U('admin/Config/register'),
				'邀请配置'	=>	U('admin/Config/invite'),
// 				'游客配置'	=> U('admin/Config/guest'),
// 				'微博配置'	=>	U('admin/Config/feed'),
				'邮件配置'	=>	U('admin/Config/email'),
				'附件配置'	=>	U('admin/Config/attach'),
				'过滤配置'	=>	U('admin/Config/audit'),
// 				L('PUBLIC_POINT_SETTING')	=>	U('admin/C/credit'),
				'积分配置'	=>	U('admin/CreditSetting/integralRules'),
				'地区配置'			=>  U('admin/Config/area'),
				'语言配置'	=>	U('admin/Config/lang'),
				'消息配置'	=>	U('admin/Config/notify'),
	    		//L('PUBLIC_POINTS_SETTING')	=>  U('admin/Apps/setCreditNode'),
					'部门配置'					=> U('admin/Department/index'),
	    		'权限节点配置'	=>  U('admin/Apps/setPermNode'),
	    		// L('PUBLIC_WEIBO_TEMPLATE_SETTING')	=>  U('admin/Apps/setFeedNode'),
	    		'SEO配置'	=>  U('admin/Config/setSeo'),
	    		'页面配置同步' => U('admin/Config/updateAdminTab'),
	    		'UCenter配置' => U('admin/Config/setUcenter'),
				'梦想盒子配置' => U('admin/Config/otherConfig'),
				'红包配置' => U('admin/CouponConfig/index'),
			),
		),

    	'user'	=>	array(
    		'用户'			=>	array(

    			'用户管理'	=>	U('admin/User/index'),
    			'用户组管理'	=>	U('admin/UserGroup/index'),
    			'资料配置'	=>	U('admin/User/profile'),
    			'用户标签'	=>	U('admin/User/category'),
    			'用户认证'	=>  U('admin/UserVerified/verifying'),
    			'积分管理'	=>  U('admin/UserIntegral/teacherIntegrals'),
    			'找人配置'	=>  U('admin/User/findPeopleConfig'),
    			'找人推荐'	=>	U('admin/User/officialCategory'),
    		),
    	),
    	
    	'content'	=> array(
    		'内容管理'			=>	array(
    			'公告配置'	=>	U('admin/Config/announcement'),
    			'微博管理'	=>	U('admin/Content/feed'),
    			'话题管理'	=>	U('admin/Content/topic'),
    			'评论管理'	=>	U('admin/Content/comment'),
    			'私信管理'	=>	U('admin/Content/message'),
    			'附件管理'	=>	U('admin/Content/attach'),
    			'举报管理'	=>	U('admin/Content/denounce'),
				'标签管理'	=>  U('admin/Home/tag'),
				'邀请统计'	=>	U('admin/Home/invatecount'),
				'模板管理'	=>	U('admin/Content/template'),
    		    '签到管理'	=>	U('admin/Feedback/index'),
    		    '签到异常统计'	=>	U('admin/Feedback/unusualStat'),
    			'课程管理'	=>	U('admin/CourseAdmin/index'),
    			'梦想中心'	=>	U('admin/DreamCenter/index'),
    			'系统标签'	=>	U('admin/Tag/index'),
    			'运营部报表'	=>	U('admin/Maintain/school_course_stat')
	    	),
    	),
    	'task'	=> array(
			'任务管理'			=> array(
	 			'任务列表'				=> U('admin/Task/index'),
	 			'任务奖励' 				=> U('admin/Task/reward'),
	 			'勋章列表'				=> U('admin/Medal/index'),
	 			'用户勋章'				=> U('admin/Medal/userMedal'),
				'任务配置'				=> U('admin/Task/taskConfig')
	 		)
	 	),
    	'apps'	=> array(
			'应用管理'			=>	array(
	    		'已安装应用列表'	=>	U('admin/Apps/index'),
	    		'未安装应用列表'	=>	U('admin/Apps/install'),
				'在线应用'	=>	U('admin/Apps/onLineApp'),
	    	),
	 	),
	    'extends'		=> array(
	 		'插件管理' => array(
    			'所有插件列表' => U('admin/Addons/index'),
    		),
	 	),
    )
);

$app_list = model('App')->getConfigList();
foreach($app_list as $k=>$v){
	$menu['admin_menu']['apps']['应用管理'][$k] = $v;
}
$plugin_list = model('Addon')->getAddonsAdminUrl();
foreach($plugin_list as $k=>$v){
	$menu['admin_menu']['extends']['插件管理'][$k] = $v;
}

//防护云激活代码
//1.如果防护云库文件存在，但是配置不存在，新注册key
if(!file_exists(DATA_PATH.'/iswaf/config.php') && file_exists(ADDON_PATH.'/library/iswaf/iswaf.php')){
	$dir   =  SITE_PATH.'/data/iswaf';
	function iswaf_create_key() {
	    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	    $hash = '';
	    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	    $max = strlen($chars) - 1;
	    for($i = 0; $i < 128; $i++) {
	        $hash .= $chars[mt_rand(0, $max)];
	    }
	    return md5($hash.rand(1,3000).print_r($_SERVER,1));
	}
	// 目录不存在则创建
	if(!is_dir($dir))  mkdir($dir,0777,true);
	$iswafKey = iswaf_create_key(SITE_URL);
	$iswafConfig = array(
		'iswaf_database' => $dir.'/',
		'iswaf_connenct_key' => $iswafKey,
		'iswaf_status' => 1,
		'defences'=>array(
					'callback_xss'=>'On',
					'upload'=>'On',
					'inject'=>'On',	
					'filemode'=>'On',
					'webshell'=>'On',
					'server_args'=>'On',
					'webserver'=>'On',
					'hotfixs'=>'On',
					)
	);
	//注册ts站点
	$context = stream_context_create(array(
	'http'=>array(
	  'method' => "GET",
	  'timeout' => 10, //超时30秒
	  'user_agent'=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"
	  )));
	$url = 'http://www.fanghuyun.com/api.php?do=tsreg&IDKey='.$iswafKey.'&url='.SITE_URL.'&ip='.get_client_ip();
	$res = file_get_contents($url, false, $context);
	//dump($res);exit;
	file_put_contents($dir.'/config.php',"<?php\nreturn ".var_export($iswafConfig,true).";\n?>");
	$menu['admin_menu']['index']['首页']['安全防护'] = 'http://www.fanghuyun.com/?do=simple&IDKey='.md5($iswafKey);
//2.如果防护云配置文件存在，但是没有关闭，启用防护云
}else if(defined('iswaf_status') && iswaf_status!=0){
	$context = stream_context_create(array(
	'http'=>array(
	  'method' => "GET",
	  'timeout' => 10, //超时30秒
	  'user_agent'=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"
	  )));
	$res = file_get_contents('http://www.fanghuyun.com/api.php?IDKey='.iswaf_connenct_key.'&url='.SITE_URL.'&ip='.get_client_ip(), false, $context);
	//dump($res);exit;
	$menu['admin_menu']['index']['首页']['安全防护'] = 'http://www.fanghuyun.com/?do=simple&IDKey='.md5(iswaf_connenct_key);
}
return $menu;