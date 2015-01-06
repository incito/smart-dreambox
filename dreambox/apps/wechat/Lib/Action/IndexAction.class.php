<?php
class IndexAction extends Action {
	// 平台主动消息对象
	protected $postObj;
	// 响应分发
	public function index() {
		if (isset ( $_GET ['echostr'] )) {
			// $this->valid();
			echo $_GET ['echostr'];
			exit ();
		} else {
			$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
			if (! empty ( $postStr )) {
				$this->postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA );
			}
		}
		//防盗链
		if(WX_ID!==trim($this->postObj->ToUserName)){
			exit('sorry');
		}
		if ($this->postObj) {
			// $this->trimAll($this->postObj);
			$msgType = $this->postObj->MsgType;
			// 处理事件和消息
			if ($msgType) {
				// 处理事件推送
				if ($msgType == 'event') {
					$model = M ( 'Event' );
					$event = trim ( $this->postObj->Event );
					// 处理非VIEW事件
					if ($event != 'VIEW') {
						$eventKey = trim ( $this->postObj->EventKey );
						$method = empty ( $eventKey ) ? $event : ($event . '_' . $eventKey);
						call_user_func ( array (
								&$model,
								$method 
						), $this->postObj );
						// $model->$method ( $this->postObj );
					} else {
						// log_wx(getTextTpl ( trim ( $this->postObj->FromUserName ), trim ( $this->postObj->ToUserName ), time (), 'aaa' ));
						// echo getTextTpl ( trim ( $this->postObj->FromUserName ), trim ( $this->postObj->ToUserName ), time (), 'aaa' );
					}
					// 处理消息推送
				} else {
					$model = M ( 'Event' );
					$model->subscribe($this->postObj);
				}
				// 处理其他响应
			} else {
			}
		}
	}
	public function fetch() {
		$code = $_REQUEST ['code'];
		$state = $_REQUEST ['state'];
		$param = 'appid=' . APPID . '&secret=' . APPSECRET . '&code=' . $code . '&grant_type=authorization_code';
		$result = get_data_api ( 'oauth2_access_token', $param, 'get' );
		$this->fetchTo ( C ( 'html_uri.' . $state ) . 'openId=' . $result->openid );
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
		$path = SITE_URL . $template;
		// if (! file_exists ( $path )) {
		// echo '模板文件不存在！';
		// }
		// 重定向浏览器
		header ( 'Location:' . $path );
		// 确保重定向后，后续代码不会被执行
		exit ();
	}
	public function test() {
		echo 'data:';
		var_dump ( $_GET );
		var_dump ( $_POST );
	}
	protected function valid() {
		$echoStr = $_GET ["echostr"];
		if ($this->checkSignature ()) {
			echo $echoStr;
			exit ();
		}
	}
	private function checkSignature() {
		$signature = $_GET ["signature"];
		$timestamp = $_GET ["timestamp"];
		$nonce = $_GET ["nonce"];
		
		$token = TOKEN;
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		sort ( $tmpArr );
		$tmpStr = implode ( $tmpArr );
		$tmpStr = sha1 ( $tmpStr );
		
		return $tmpStr == $signature;
	}
}

?>