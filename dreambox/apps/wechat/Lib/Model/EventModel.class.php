<?php

/**
 * 事件处理
 * @author zjj
 *
 */
class EventModel extends Model {
	public function subscribe($post) {
		$openId = trim ( $post->FromUserName );
		$sql = 'SELECT b.uid, u.uname FROM ts_user u RIGHT JOIN ts_user_bind b ON u.uid = b.uid WHERE b.type = 1 AND b.bindId=\'' . $openId . '\'';
		$user = M ()->query ( $sql );
		// 如果已经绑定
		if ($user) {
			// $inteModel = M ( 'Integral' );
			// // 查询积分信息
			// $inteInfo = $inteModel->getIntegralRankByUid ( $user [0] ['uid'] );
			// // 计算比例
			// $less = ceil ( $inteInfo ['less'] * 100 / $inteInfo ['count'] );
			// $content = "尊敬的 " . $user [0] ['uname'] . "老师！\n您的可用积分为：" . $inteInfo ['integral'] . "\n累积积分为：" . $inteInfo ['sum_integral'] . "\n您的积分超越了" . $less . "%的老师\n\n<a href=\"www.adreambox.net\">点击查询积分账户明细</a>";
			$content = "欢迎您，" . $user [0] ['uname'] . "老师";
		} else {
			// $login = SITE_URL . '/html/wechat/#!/login?openId=' . $openId;
			$login = SITE_URL . '?app=wechat&mod=Account&act=needLogin&openId=' . $openId;
			$regist = SITE_URL . '/html/wechat/#!/regist?openId=' . $openId;
			$content = "您好，欢迎关注梦想盒子！\n请先登录/注册梦想盒子！\n\n<a href=\"" . $login . "\">已有盒子账号登录</a>\n\n<a href=\"" . $regist . "\">注册盒子账号</a>";
		}
		exit ( getTextTpl ( trim ( $post->FromUserName ), trim ( $post->ToUserName ), time (), $content ) );
	}
	public function click_query($post) {
		// $menu=get_data_api('query_menu');
		// log_wx(trim ( $post->FromUserName ).' '. trim ( $post->ToUserName ) );
		// exit ( getTextTpl ( trim ( $post->FromUserName ), trim ( $post->ToUserName ), time (), json_encode($menu) ) );
		$param = array (
				'expire_seconds' => '60',
				'action_name' => 'QR_SCENE',
				'action_info' => array (
						'scene_id' => 111 
				) 
		);
		$data = get_data_api ( 'get_ticket', $param );
		// log_wx(var_export($post))
		// log_wx(getTextTpl ( trim ( $post->FromUserName ), trim ( $post->ToUserName ), time (), json_encode($data) ));
		exit ( getTextTpl ( trim ( $post->FromUserName ), trim ( $post->ToUserName ), time (), json_encode ( $data ) ) );
	}
}
?>