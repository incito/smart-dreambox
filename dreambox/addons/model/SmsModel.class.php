<?php
/**
 * SMS模型
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class SmsModel extends Model {
	protected $tableName='verified_code';
	protected $fields=array('0'=>'id','1'=>'userkey','2'=>'code','3'=>'msgkey','4'=>'used','5'=>'ctime');
	private $userid;
	private $pwd;
	private $host;
	private $port;
	public static $ERR_TIMEOUT='error_timeout';
	// 测试相同内容群发
	// testMultiMt();
	// 测试不同内容群发
	// testMultiXMt();
	public function _initialize() {
		$this->userid = C ( 'SMS_USER' );
		$this->pwd = C ( 'SMS_PWD' );
		$this->host = C ( 'SMS_HOST' );
		$this->port = C ( 'SMS_PORT' );
	}
	/**
	 * 相同内容群发
	 *
	 * @return String
	 */
	public function sendMultiMt($phone, $msg) {
		$msg=iconv('UTF-8', 'GBK', $msg);
		// 预定义参数，参数说明见文档
		$msgdc = "15";
		// 拼接URI
		$request = "/sms/esmsmt";
		$request .= "?command=MT_REQ&userid=" . $this->userid . "&pwd=" . $this->pwd;
		$request .= "&das=" . $phone . "&msgdc=" . $msgdc . "&msg=";
		$request .= $this->encodeHexStr ( $msgdc, $msg ); // 下发内容转换HEX编码
		$content = $this->doPostRequest ( $this->host, $this->port, $request ); // 调用发送方法发送
		//如果发送超时，发送三次
		if($content==self::$ERR_TIMEOUT){
			$content = $this->doPostRequest ( $this->host, $this->port, $request ); // 调用发送方法发送
		}
		if($content==self::$ERR_TIMEOUT){
			$content = $this->doPostRequest ( $this->host, $this->port, $request ); // 调用发送方法发送
		}
		$arr=array();
		parse_str($content,$arr);
		return $arr;
	}
	
	// /**
	// * 不同内容群发示例
	// *
	// * @return String
	// */
	// function testMultiXMt() {
	// // 预定义参数，参数说明见文档
	// $userid = "1234";
	// $sc = "00";
	// $pwd = "1234";
	// $sa = "10";
	// $dasmsg = "8613900000000/c4e3bac332303038,8613000000000/c4e3bac332303038,8613400000000/c4e3bac332303038";
	// $msgdc = "15";
	// $host = "61.*.*.*:9002";
	// // 发送端口，默认80.
	// $port = 80;
	// // 拼接URI
	// $request = "/sms/esmsmt";
	// $request .= "?command=MTX_REQ&userid=" . $userid . "&sc=" . $sc . "&pwd=" . $pwd;
	// $request .= "&sa=" . $sa . "&msgdc=" . $msgdc . "&dasmsg=";
	// $instances = explode ( ",", $dasmsg ); // 拆分下发号码与内容
	// $i = 0;
	// foreach ( $instances as $value ) {
	// $i ++;
	// if ($i > 100) {
	// break;
	// }
	// list ( $da, $msg ) = explode ( "/", $value, 2 );
	// $msg = encodeHexStr ( $msgdc, $msg ); // 下发内容转换HEX编码
	// $request .= $da . "/" . $msg . ",";
	// // echo "[da$i=".$da."|sm$i=".$sm."]";
	// }
	// $content = doPostRequest ( $host, $port, $request ); // 调用发送方法发送,只能使用POST方式
	// return $content;
	// }
	private function doGetRequest($host, $port, $request) {
		$method = "GET";
		return $this->httpSend ( $host, $port, $method, $request );
	}
	private function doPostRequest($host, $port, $request) {
		$method = "POST";
		return $this->httpSend ( $host, $port, $method, $request );
	}
	/**
	 * 使用http协议发送消息
	 *
	 * @param string $host        	
	 * @param int $port        	
	 * @param string $method        	
	 * @param string $request        	
	 * @return string
	 */
	private function httpSend($host, $port, $method, $request) {
		$httpHeader = $method . " " . $request . " HTTP/1.1\r\n";
		$httpHeader .= "Host: $host\r\n";
		$httpHeader .= "Connection: Close\r\n";
		// $httpHeader .= "User-Agent: Mozilla/4.0(compatible;MSIE 7.0;Windows NT 5.1)\r\n";
		$httpHeader .= "Content-type: text/plain\r\n";
		$httpHeader .= "Content-length: " . strlen ( $request ) . "\r\n";
		$httpHeader .= "\r\n";
		$httpHeader .= $request;
		$httpHeader .= "\r\n\r\n";
		$fp = @fsockopen ( $host, $port, $errno, $errstr, 5 );
		$result = "";
		if ($fp) {
			fwrite ( $fp, $httpHeader );
			while ( ! feof ( $fp ) ) { // 读取get的结果
				$result .= fread ( $fp, 1024 );
			}
			fclose ( $fp );
		} else {
			return self::ERR_TIMEOUT; // 超时标志
		}
		list ( $header, $foo ) = explode ( "\r\n\r\n", $result );
		list ( $foo, $content ) = explode ( $header, $result );
		$content = str_replace ( "\r\n", "", $content );
		// 返回调用结果
		return $content;
	}
	/**
	 * decode Hex String
	 *
	 * @param string $dataCoding
	 *        	charset
	 * @param string $hexStr
	 *        	convert a hex string to binary string
	 * @return string binary string
	 */
	private function decodeHexStr($dataCoding, $hexStr) {
		$hexLenght = strlen ( $hexStr );
		// only hex numbers is allowed
		if ($hexLenght % 2 != 0 || preg_match ( "/[^\da-fA-F]/", $hexStr )) {
			return FALSE;
		}
		unset ( $binString );
		$binString = '';
		for($x = 1; $x <= $hexLenght / 2; $x ++) {
			$binString .= chr ( hexdec ( substr ( $hexStr, 2 * $x - 2, 2 ) ) );
		}
		if ($dataCoding == 0 || ! empty ( $dataCoding )) {
			if ($dataCoding == 15) {
				return $binString;
			} else if ($dataCoding == 8) {
				return iconv ( 'UTF-16BE', 'GBK', $binString );
			} else {
				return iconv ( 'ISO8859-1', 'GBK', $binString );
			}
		}
		return $binString;
	}
	
	/**
	 * encode Hex String
	 *
	 * @param string $dataCoding        	
	 * @param string $realStr        	
	 * @return string hex string
	 */
	private function encodeHexStr($dataCoding, $realStr) {
		return bin2hex ( $realStr );
	}
	private function encodeHexStr2($dataCoding, $realStr) {
		// return bin2hex($realStr);
		$str = iconv ( GBK, 'UCS-2', $realStr );
		$dec = bin2hex ( $str );
		$dec = strtoupper ( $dec );
		return $dec;
	}
	public function saveCode($phone,$code,$msgkey){
		$map['userkey']=$phone;
		$map['code']=$code;
		$map['msgkey']=$msgkey;
		$map['ctime']=time();
		$this->add($map);
	}
}