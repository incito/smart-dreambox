<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 */
return array (
		"access" => array (
				
				// 搜索
				'public/Search/*' => true,
				
				// 网站公告
				'public/Index/announcement' => true,
				'public/School/detail' => true,//学校主页-更多资料
				
				// 微博话题
				'public/Topic/index' => true,
				
				// 微博排行榜
				'public/Rank/*' => true,
				
				'public/Feed/addDigg' => true,
				'public/Feed/delDigg' => true,
				'public/Index/getIndexBlog' => true 
		) 
)
;