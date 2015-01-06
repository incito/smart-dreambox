<?php
/**
 * 文本消息
 */
function getTextTpl($toUser, $fromUser, $createTime, $content) {
	return '<xml>
						<ToUserName><![CDATA[' . $toUser . ']]></ToUserName>
						<FromUserName><![CDATA[' . $fromUser . ']]></FromUserName>
						<CreateTime>' . $createTime . '</CreateTime>
						<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[' . $content . ']]></Content>
					</xml>';
}

/**
 * 图片消息
 */
function getImageTpl($toUser, $fromUser, $createTime, $mediaId) {
	$tpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[image]]></MsgType>
						<Image>
						<MediaId><![CDATA[%s]]></MediaId>
						</Image>
					</xml>";
	return sprintf ( $tpl, $toUser, $fromUser, $createTime, $mediaId );
}

/**
 * 语音消息
 */
function getVoiceTpl($toUser, $fromUser, $createTime, $mediaId) {
	$tpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[voice]]></MsgType>
						<Voice>
						<MediaId><![CDATA[%s]]></MediaId>
						</Voice>
					</xml>";
	return sprintf ( $tpl, $toUser, $fromUser, $createTime, $mediaId );
}

/**
 * 视频消息
 */
function getVideoTpl($toUser, $fromUser, $createTime, $mediaId, $title = "", $description = "") {
	$tpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[video]]></MsgType>
						<Video>
						<MediaId><![CDATA[%s]]></MediaId>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						</Video>
				</xml>";
	return sprintf ( $tpl, $toUser, $fromUser, $createTime, $mediaId, $title, $description );
}

/**
 * 音乐消息
 */
function getMusicTpl($toUser, $fromUser, $createTime, $mediaId, $title = "", $description = "", $musicUrl = "", $hQMusicUrl = "") {
	$tpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[music]]></MsgType>
						<Music>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<MusicUrl><![CDATA[%s]]></MusicUrl>
						<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
						<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
						</Music>
					</xml>";
	return sprintf ( $tpl, $toUser, $fromUser, $createTime, $title, $description, $musicUrl, $hQMusicUrl, $mediaId );
}
/**
 * 图文消息
 */
function getNewsTpl($toUser, $fromUser, $createTime, $articleCount, $data) {
	$tplTop = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<ArticleCount>%s</ArticleCount>
					<Articles>
					";
	$tplTop = sprintf ( $tplTop, $toUser, $fromUser, $createTime, $articleCount );
	$tplItems = "";
	$tplItem = "<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>";
	if (! empty ( $data )) {
		foreach ( $data as $value ) {
			if (is_array ( $value )) {
				$tplItems .= sprintf ( $tplItem, $value ['title'], $value ['description'], $value ['picUrl'], $value ['url'] );
			} else {
				$tplItems = sprintf ( $tplItem, $data ['title'], $data ['description'], $data ['picUrl'], $data ['url'] );
				break;
			}
		}
	}
	$tplEnd = "</Articles></xml>";
	return $tplTop . $tplItems . $tplEnd;
}
/**
 * 调用API
 *
 * @param unknown $handle        	
 * @param unknown $post        	
 * @param string $method        	
 * @return string boolean
 */
function get_data_api($handle, $post = array (), $method = 'post') {
	$url = C ( 'api_url.' . $handle );
	if ($url) {
		$cache = M ( 'Cache' );
		$access_token = $cache->get ( 'wc_access_token' );
		// 获取access_token
		if (! $access_token) {
			$data = sendGet ( C ( 'api_url.access_token' ) );
// 			$data = json_decode ( $data );
			// access_token获取失败
			if ($data->errcode) {
				return $data->errcode;
			}
			$access_token = $data->access_token;
			$cache->set ( 'wc_access_token', $access_token, 3600 );
		}
		
		$rurl = $url . '?access_token=' . $access_token;
		$ret = $method == 'post' ? sendPost ( $rurl, $post ) : sendGet ( $rurl, $post );
		// access_token异常时，重新获取access_token后再请求
		if ($ret->errcode == '40001' || $ret->errcode == '40014' || $ret->errcode == '41001' || $ret->errcode == '42001') {
			$access_token = resetAccessToken ();
			$rurl = $url . '?access_token=' . $access_token;
			$ret = $method == 'post' ? sendPost ( $rurl, $post ) : sendGet ( $rurl, $post );
		}
		return $ret;
	} else {
		return false;
	}
}
function sendRequest($url, $post, $method = 'post') {
	is_array ( $post ) && $post = json_encode ( $post );
	header ( "Content-Type: text/html; charset=UTF-8" );
	// $content = http_build_query ( $post );
	$content = $post;
	$content_length = strlen ( $content );
	
	$options = array (
			'https' => array (
					'method' => $method,
					'header' => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-length: $content_length\r\n",
					'content' => $content 
			) 
	);
	return file_get_contents ( $url, false, stream_context_create ( $options ) );
}
function resetAccessToken() {
	$data = sendGet ( C ( 'api_url.access_token' ) );
	$access_token = $data->access_token;
	M ( 'Cache' )->set ( 'wc_access_token', $access_token, 3600 );
	return $access_token;
}
function sendGet($url, $post) {
	is_array ( $post ) && $post = http_build_query ( $post );
	$rurl = $url;
	if ($post) {
		$rurl = $url . '&' . $post;
	}
	return json_decode ( file_get_contents ( $rurl ) );
}
function sendPost($url, $post) {
	is_array ( $post ) && $post = json_encode ( $post );
	
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
	curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	
	$info = json_decode ( curl_exec ( $ch ) );
	// 异常
	$flag = 1;
	if (curl_errno ( $ch )) {
		$flag = 0;
	}
	curl_close ( $ch );
	return $flag ? $info : 0;
}
function getObjValue($obj, $name) {
	return trim ( $obj->$name );
}
function isPhoneNum($phone) {
	if (empty ( $phone ) || strlen ( $phone ) != 11) {
		return false;
	}
	$phone_reg = '/^\d+$/i';
	return preg_match ( $phone_reg, $phone );
}
/**
 * 根据openid获得uid
 *
 * @param unknown $openid        	
 */
function getUidByOid($openid) {
	$cache = M ( 'Cache' );
	$key = 'oid_' . $openid;
	$uid = $cache->get ( $key );
	if(!$uid){
		$uid = M ( 'user_bind' )->where ( array (
				'bindId' => $openid 
		) )->getField ( 'uid' );
		if ($uid) {
			$cache->set ( $key, $uid, 86400 );
		}
	}
	return $uid;
}
function is_weixin() {
	return strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MicroMessenger' );
}