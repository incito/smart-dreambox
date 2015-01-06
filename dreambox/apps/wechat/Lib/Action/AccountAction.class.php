<?php
class AccountAction extends WechatAction {
	/**
	 * 登陆
	 */
	public function login() {
		exit ( json_encode ( $this->checkLogin ( $this->postData ) ) );
	}
	/**
	 * 引导页
	 */
	public function index() {
		$uid = $this->getUid ();
		if (! $uid) {
			$result ['code'] = 2;
			$result ['msg'] = "您还没登陆";
			exit ( json_encode ( $result ) );
		}
		$userInfo = M ( 'User' )->getUserShortInfo ( $uid );
		// 获取签到课程数目
		$courseModel = M ( 'Course', 'dreambox' );
		$signData = $courseModel->queryNeedSignCourse ( $uid );
		$signCount = $signData ? count ( $signData ) : 0;
		// 查询积分信息
		$inteInfo = M ( 'Integral' )->getIntegralRankByUid ( $uid );
		
		if ($inteInfo) {
			$integral = $inteInfo ['integral'];
			$over = $inteInfo ['count'] ? ceil ( $inteInfo ['less'] * 100 / $inteInfo ['count'] ) : 0;
		}
		$sumIntegral = $sumIntegral ? $sumIntegral : 0;
		// 查询红包信息
		$money = $this->queryMoney ( $uid );
		//是否需要绑定手机
		$phone=M('user_verified_info')->where('uid='.$uid)->getField('phone');
		$result = array (
				'code' => 1,
				'msg' => '登陆成功',
				'data' => array (
						'userid' => $uid,
						'uname' => $userInfo ['uname'],
						'avatar_url' => $userInfo ['avatar_big'],
						'sign_num' => $signCount,
						'integral' => intval ( $integral ),
						'over' => intval ( $over ),
						'money' => $money ['money'] ? $money ['money'] : 0,
						'money_over' => $money ['money_over'] ? $money ['money_over'] : 0 ,
						'needBind'=>$phone?false:true
				) 
		);
		exit ( json_encode ( $result ) );
	}
	/**
	 * 获得用户信息
	 */
	public function userInfo() {
		$uid = $this->getUid ();
		$data = array ();
		if (property_exists ( $this->postData, 'type' )) {
			$type = $this->postData->type;
			$uname = strpos ( $type, 'uname' );
			$avatar = strpos ( $type, 'avatar_url' );
			$money = strpos ( $type, 'money' );
		}
		if (empty ( $type ) || $uname !== false || $avatar !== false) {
			$userInfo = M ( 'User' )->getUserShortInfo ( $uid );
			if (empty ( $type ) || $uname !== false) {
				$data ['uname'] = $userInfo ['uname'];
			}
			if (empty ( $type ) || $avatar !== false) {
				$data ['avatar_url'] = $userInfo ['avatar_small'];
			}
		}
		if (empty ( $type ) || $money !== false) {
			$data ['money'] = M ( 'db_coupon' )->where ( 'userid=' . $uid )->getField ( 'c_balance' );
		}
		exit ( json_encode ( array (
				'code' => 1,
				'data' => $data 
		) ) );
	}
	/**
	 * 判断是否需要登录
	 */
	public function needLogin() {
		$openId = $_REQUEST ['openId'];
		// 绑定openId
		$map ['uid'] = getUidByOid ( $openId );
		// 网页字符编码
		header ( "Content-Type:text/html; charset=utf-8" );
		header ( "Cache-control: private" );
		if ($map ['uid']) {
			$path = SITE_URL . '/html/wechat/#!/intro?mask=1&openId=' . $openId;
		} else {
			$path = SITE_URL . '/html/wechat/#!/login?openId=' . $openId;
		}
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache"); 
		// 重定向浏览器
		header ( 'Location:' . $path );
		// 确保重定向后，后续代码不会被执行
		exit ();
	}
	
	/**
	 * 解绑
	 */
	public function unLogin() {
		$openId = $_REQUEST ['openId'];
		// 绑定openId
		$map ['uid'] = getUidByOid ( $openId );
		
		// 判断微信号是否绑定其他用户
		M ( 'user_bind' )->where ( $map )->delete ();
		// 清理openId->uid缓存
		M ( 'Cache' )->rm ( 'oid_' . $openId );
		// 网页字符编码
		header ( "Content-Type:text/html; charset=utf-8" );
		header ( "Cache-control: private" ); // 支持页面回跳
		$path = SITE_URL . '/html/wechat/#!/login?openId=' . $openId;
		// 重定向浏览器
		header ( 'Location:' . $path );
		// 确保重定向后，后续代码不会被执行
		exit ();
	}
	/**
	 * 注册
	 */
	public function register() {
		$data ['openid'] = t ( $this->postData->openId );
		$data ['phone'] = t ( $this->postData->phone );
		$data ['password'] = $this->postData->password;
		$data ['code'] = t ( $this->postData->code );
		$data ['role'] = intval ( $this->postData->role );
		exit ( json_encode ( $this->_register ( $data ) ) );
	}
	private function _register($data) {
		$ret ['code'] = 1;
		if (empty ( $data ['openid'] )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '信息不完整';
			return $ret;
		}
		// 手机校验
		if (! isPhoneNum ( $data ['phone'] )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '请输入11位手机号';
			return $ret;
		}
		// 校验手机是否已经注册过
		if (! isBindablePhone ( $data ['phone'] )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '该手机已经被注册';
			return $ret;
		}
		// 校验验证码
		$ret = $this->checkCode ( $data ['phone'], $data ['code'], 'SMS_REGISTER_CODE' );
		if ($ret) {
			return $ret;
		}
		// 密码校验
		$regModel = M ( 'Register' );
		if (! $regModel->isValidPassword ( $data ['password'], $data ['password'] )) {
			$ret ['code'] = 0;
			$ret ['msg'] = $regModel->getLastError ();
			return $ret;
		}
		$login_salt = rand ( 11111, 99999 );
		$map ['uname'] = $this->createUname ( $data ['phone'] );
		$map ['login_salt'] = $login_salt;
		$map ['password'] = md5 ( md5 ( $data ['password'] ) . $login_salt );
		$map ['login'] = $data ['phone'];
		$map ['utype'] = $data ['role'];
		$map ['reg_ip'] = get_client_ip ();
		$map ['ctime'] = time ();
		$map ['is_audit'] = '1';
		$map ['is_active'] = '1';
		$map ['reg_source'] = '2'; // 微信端注册
		$userModel = M ( 'User' );
		$add = $userModel->add ( $map );
		if ($add) {
			$uid = $userModel->getLastInsID ();
			$verified ['uid'] = $uid;
			$verified ['phone'] = $data ['phone'];
			$verified ['phone_code'] = $data ['code']; // 被驳回状态
			$verified ['phone_time'] = time ();
			$add = M ( 'user_verified_info' )->add ( $verified );
			// 绑定微信和账号
			$userBind = M ( 'user_bind' );
			$userBind->where ( 'bindid=\'' . $data ['openid'] . '\'' )->delete ();
			$add && $add = $userBind->add ( array (
					'uid' => $uid,
					'bindId' => $data ['openid'],
					'ctime' => time () 
			) );
			if ($add) {
				$ret ['code'] = 1;
				$ret ['data'] = array (
						'text' => '注册成功',
						'url' => 'intro' 
				);
				// 清理openId->uid缓存
				M ( 'Cache' )->rm ( 'oid_' . $data ['openid'] );
				//设置验证码已使用状态
				$upmap ['userkey'] = $data ['phone'];
				$upmap ['msgkey'] = 'SMS_REGISTER_CODE';
				M ( 'verified_code' )->where ( $upmap )->setField ( 'used', '1' );
			} else {
				$ret ['code'] = 0;
				$ret ['msg'] = '注册失败！';
			}
		} else {
			$ret ['code'] = 0;
			$ret ['msg'] = '注册失败！';
		}
		return $ret;
	}
	private function checkLogin($param) {
		$openId = trim ( $param->openId );
		$userId = trim ( $param->userid );
		$userpassword = $param->password ;
		
		if (empty ( $openId )) {
			return array (
					'code' => 0,
					'msg' => 'openid不能为空' 
			);
		}
		$model = M ();
		
		if ($this->isValidEmail ( $userId )) {
			$sql = "SELECT u.uid,u.uname,u.login_salt,u.password,u.is_active FROM ts_user u where u.login='$userId' or u.email='$userId'";
		} else {
			$sql = "SELECT u.uid, u.uname, u.login_salt, u.password, u.is_active FROM ts_user u WHERE u.uname = '$userId' union SELECT u.uid, u.uname, u.login_salt, u.password, u.is_active FROM ts_user u WHERE uid = ( SELECT uid FROM ts_user_verified_info WHERE phone = '$userId' LIMIT 1 )";
		} 
		$userData = $model->query ( $sql );
		$msg='';
		// 存在当前用户
		if ($userData) {
			foreach ( $userData as $user ) {
				// 判断密码
				$password = md5 ( md5 ( $userpassword ) . $user ['login_salt'] );
				if ($password ===$user ['password']) {
					// 判断邮箱是否已经激活
					if (! $user ['is_active']) {
						$msg= '邮箱未激活,请先通过网页版登陆激活邮箱' ;
						continue;
					}
					// 绑定openId
					$map ['bindId'] = $openId;
					// 判断微信号是否绑定其他用户
					$bind = M ( 'user_bind' );
					$otherUser = $bind->where ( $map )->find ();
					$map ['uid'] =$user ['uid'];
					$map ['ctime'] = time ();
					if ($otherUser) {
						// 绑定账号
						$bind->where ( 'id=' . $otherUser ['id'] )->save ( $map );
						// 清理openId->uid缓存
						M ( 'Cache' )->rm ( 'oid_' . $openId );
					} else {
						// 绑定账号
						$bind->add ( $map );
					}
					return array (
							'code' => 1,
							'msg' => '登陆成功' 
					);
				} else {
					$msg= '密码错误' ;
					continue;
				}
			}
		} else {
			return array (
					'code' => 0,
					'msg' => '该用户不存在' 
			);
		}
		return array (
				'code' => 0,
				'msg' => $msg
		);
	}
	/**
	 * 发送手机验证码
	 */
	public function sendCode() {
		// // 短信发不出去，临时调整
		// $ret ['code'] = 1;
		// exit ( json_encode ( $ret ) );
		$openid = t ( $this->postData->openId );
		$phone = t ( $this->postData->phone );
		// 检验手机格式
		if (! isPhoneNum ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '请输入11位手机号';
			exit ( json_encode ( $ret ) );
		}
		// 校验手机是否已经注册过
		if (! isBindablePhone ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '该手机已经被注册';
			exit ( json_encode ( $ret ) );
		}
		// 发送验证码
		if ($openid && $phone) {
			$this->_sendCode ( $phone, 'SMS_REGISTER_CODE' );
		}
		$ret ['code'] = 0;
		$ret ['msg'] = '缺少参数';
		exit ( json_encode ( $ret ) );
	}
	/**
	 * 找回密码手机验证码
	 */
	public function sendFindCode() {
		// // 短信发不出去，临时调整
		// $ret ['code'] = 1;
		// exit ( json_encode ( $ret ) );
		$openid = t ( $this->postData->openId );
		$phone = t ( $this->postData->phone );
		// 检验手机格式
		if (! isPhoneNum ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '请输入11位手机号';
			exit ( json_encode ( $ret ) );
		}
		// 校验手机是否已经注册过
		if (isBindablePhone ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '手机尚未注册或绑定';
			exit ( json_encode ( $ret ) );
		}
		if ($openid && $phone) {
			$this->_sendCode ( $phone, 'SMS_FINDPWD_CODE' );
		}
		$ret ['code'] = 0;
		$ret ['msg'] = '缺少参数';
		exit ( json_encode ( $ret ) );
	}
	/**
	 * 手机绑定发送验证码
	 */
	public function sendBindCode() {
		$openid = t ( $this->postData->openId );
		$phone = t ( $this->postData->phone );
		// 检验手机格式
		if (! isPhoneNum ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '请输入11位手机号';
			exit ( json_encode ( $ret ) );
		}
		$oldphone=M('user_verified_info')->where('uid='.$this->getUid())->getField('phone');
		if($oldphone){
			$oldphone=substr($oldphone, 0,3).'******'.substr($oldphone, 9);
			$ret ['code'] = 0;
			$ret ['msg'] = '您已绑定手机'.$oldphone;
			exit ( json_encode ( $ret ) );
		}
		// 校验手机是否已经注册过
		if (! isBindablePhone ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '该手机已经被绑定';
			exit ( json_encode ( $ret ) );
		}
		if ($openid && $phone) {
			$this->_sendCode ( $phone, 'SMS_BIND_CODE' );
		}
		$ret ['code'] = 0;
		$ret ['msg'] = '缺少参数';
		exit ( json_encode ( $ret ) );
	}
	/**
	 * 重置密码
	 *
	 * @return string
	 */
	public function resetPwd() {
		$code = t ( $this->postData->code );
		$phone = t ( $this->postData->phone );
		$uid = M('user_verified_info')->where('phone=\''.$phone.'\'')->getField('uid');
		// 验证账号是否存在
		if (! $uid || ! $phone) {
			$ret ['code'] = 0;
			$ret ['msg'] = '账号不存在或手机未绑定';
			exit ( json_encode ( $ret ) );
		}
		// 传入的手机和该opendid绑定的手机是否一致
		// if ($phoneget != $phone) {
		// $ret ['code'] = 0;
		// $ret ['msg'] = '绑定信息不一致';
		// exit ( json_encode ( $ret ) );
		// }
		// 校验验证码
		$ret = $this->checkCode ( $phone, $code, 'SMS_FINDPWD_CODE' );
		if ($ret) {
			exit ( json_encode ( $ret ) );
		}
		// 密码校验
		$pwd = $this->postData->password;
		$regModel = M ( 'Register' );
		if (! $regModel->isValidPassword ( $pwd, $pwd )) {
			$ret ['code'] = 0;
			$ret ['msg'] = $regModel->getLastError ();
			exit ( json_encode ( $ret ) );
		}
		
		$map ['uid'] = $uid;
		$data ['login_salt'] = rand ( 10000, 99999 );
		$data ['password'] = md5 ( md5 ( $pwd ) . $data ['login_salt'] );
		$res = model ( 'User' )->where ( $map )->save ( $data );
		if ($res) {
			$ret ['code'] = 1;
			$ret ['data'] = array (
					'text' => '重置密码成功',
					'url' => 'login' 
			);
			$upmap ['userkey'] = $data ['phone'];
			$upmap ['msgkey'] = 'SMS_FINDPWD_CODE';
			M ( 'verified_code' )->where ( $upmap )->setField ( 'used', '1' );
		} else {
			$ret ['code'] = 0;
			$ret ['msg'] = '重置密码失败';
		}
		exit ( json_encode ( $ret ) );
	}
	/**
	 * 绑定手机号
	 */
	public function bind() {
		$openid = $this->getOpenId ();
		$phone = t ( $this->postData->phone );
		$code = t ( $this->postData->code );
		$uid = $this->getUid ();
		$ret ['code'] = 1;
		if (empty ( $uid )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '你还没登陆';
			exit ( json_encode ( $ret ) );
		}
		// 手机校验
		if (! isPhoneNum ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '请输入11位手机号';
			exit ( json_encode ( $ret ) );
		}
		
		// 短信验证码校验
		$ret = $this->checkCode ( $phone, $code, 'SMS_BIND_CODE' );
		if ($ret) {
			exit ( json_encode ( $ret ) );
		}
		$oldphone=M('user_verified_info')->where('uid='.$this->getUid())->getField('phone');
		if($oldphone){
			$oldphone=substr($oldphone, 0,3).'******'.substr($oldphone, 9);
			$ret ['code'] = 0;
			$ret ['msg'] = '您已绑定手机'.$oldphone;
			exit ( json_encode ( $ret ) );
		}
		// 校验手机是否已经注册过
		if (! isBindablePhone ( $phone )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '该手机已经被绑定';
			exit ( json_encode ( $ret ) );
		}
		
		// 绑定手机
		$verified ['uid'] = $this->getUid ();
		$verified ['phone'] = $phone;
		$verified ['phone_code'] = $code;
		$verified ['phone_time'] = time ();
		$add = M ( 'user_verified_info' )->add ( $verified );
		
		// 绑定成功后废弃验证码
		if ($add) {
			$ret ['code'] = 1;
			$ret ['msg'] = '绑定成功！';
			$upmap ['userkey'] = $phone;
			$upmap ['msgkey'] = 'SMS_BIND_CODE';
			M ( 'verified_code' )->where ( $upmap )->setField ( 'used', '1' );
		} else {
			$ret ['code'] = 0;
			$ret ['msg'] = '绑定失败！';
		}
		exit ( json_encode ( $ret ) );
	}
	/**
	 * 获取注册协议
	 */
	public function regProtocol() {
		$content = M ( 'db_register_protocol' )->getField ( 'content' );
		$result ['code'] = 1;
		$result ['msg'] = 'ok';
		$result ['data'] = $content;
		exit ( json_encode ( $result ) );
	}
	/**
	 * 校验验证码
	 *
	 * @param unknown $phone        	
	 * @param unknown $code        	
	 * @param unknown $type        	
	 */
	private function checkCode($phone, $code, $type) {
		// 短信验证码校验
		if (! $code) {
			$ret ['code'] = 0;
			$ret ['msg'] = '请输入验证码';
			return $ret;
		}
		$map ['code'] = $code;
		$map ['userkey'] = $phone;
		$map ['used'] = '0';
		$map ['msgkey'] = $type;
		$vcode = M ( 'verified_code' )->where ( $map )->field ( 'code,ctime' )->find ();
		if (empty ( $vcode )) {
			$ret ['code'] = 0;
			$ret ['msg'] = '无效的验证码';
			return $ret;
		}
		// 短信验证码有效时间30分钟
		if ($vcode ['ctime'] + 1800 < time ()) {
			$ret ['code'] = 0;
			$ret ['msg'] = '验证码已过期';
			return $ret;
		}
		return false;
	}
	/**
	 * 获得不重复的昵称
	 *
	 * @param unknown $uname        	
	 * @return string
	 */
	private function createUname($uname) {
		$m = M ( 'User' );
		while ( true ) {
			$map ['uname'] = $uname;
			if ($m->where ( $map )->find ()) {
				$c = rand ( 65, 90 );
				$uname = chr ( $c ) . $uname;
			} else {
				return $uname;
			}
		}
	}
	private function queryMoney($uid) {
		$sql = 'SELECT c.c_sum, ( SELECT count(1) FROM ts_db_coupon c1 WHERE c_sum < c.c_sum ) over, ( SELECT count(1) FROM ts_db_coupon c2 ) AS count FROM ts_db_coupon c WHERE c.userid = ' . $uid . ' LIMIT 1';
		$result = M ()->query ( $sql );
		if ($result) {
			$result = $result [0];
			return array (
					'money' => $result ['c_sum'] / 100,
					'money_over' => floor ( $result ['over'] * 100 / $result ['count'] ) 
			);
		}
		return array (
				'money' => '0',
				'money_over' => 0 
		);
	}
	private function _sendCode($phone, $type) {
		// 短信频繁发送验证，发送间隔60秒
		$smsModel = M ( 'Sms' );
		$map ['userkey'] = $phone;
		$map ['used'] = 0;
		$map ['msgkey'] = $type;
		$map ['ctime'] = array (
				'gt',
				time () - 60 
		);
		if ($smsModel->where ( $map )->find ()) {
			$ret ['code'] = 0;
			$ret ['msg'] = '发送太频繁啦';
			exit ( json_encode ( $ret ) );
		}
		// send
		$code = rand ( 11111, 99999 );
		$rsp = $smsModel->sendMultiMt ( $phone, '梦想盒子的验证码：' . $code . '，30分钟内有效，请勿泄露' );
		if ($rsp [mtstat] == 'ACCEPTD0') {
			$smsModel->saveCode ( $phone, $code, $type );
			$ret ['code'] = 1;
			exit ( json_encode ( $ret ) );
		} else {
			$ret ['code'] = 0;
			$ret ['msg'] = '短信发送失败，请稍后重试.';
			exit ( json_encode ( $ret ) );
		}
	}
	private function isValidEmail($email) {
		return preg_match ( "/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email ) !== 0;
	}
}

?>