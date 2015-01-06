<?php
class WechatAction extends Action {
	protected $postData;
	public function _initialize() {
// 		if(!is_weixin()){
// 			exit('sorry');
// 		}
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$this->postData = json_decode ( $raw_post_data );
	}
	/**
	 * 跳转到微信中指定静态页面
	 *
	 * @param unknown $template        	
	 */
	protected function fetchTo($template) {
		// 网页字符编码
		header ( "Content-Type:text/html; charset=utf-8" );
		header ( "Cache-control: private" ); // 支持页面回跳
		$path = SITE_URL . '/html/wechat/' . $template;
		if (! file_exists ( $path )) {
			echo '模板文件不存在！';
		}
		echo file_get_contents ( $path );
	}
	protected function getOpenId() {
		$param = $this->postData;
		return trim ( $param->openId );
	}
	protected function getUid() {
		return getUidByOid ( $this->getOpenId () );
	}
}

?>