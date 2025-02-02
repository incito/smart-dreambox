<?php
/**
 * 通行证模型 - 业务逻辑模型
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class PassportModel {

	protected $error = null;		// 错误信息
	protected $success = null;		// 成功信息
	protected $rel = array();		// 判断是否是第一次登录

	/**
	 * 返回最后的错误信息
	 * @return string 最后的错误信息
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 返回最后的错误信息
	 * @return string 最后的错误信息
	 */
	public function getSuccess() {
		return $this->success;
	}

	/**
	 * 验证后台登录
	 * @return boolean 是否已经登录后台
	 */
	public function checkAdminLogin() {
		if($_SESSION['adminLogin']) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 登录后台
	 * @return boolean 登录后台是否成功
	 */
	public function adminLogin() {
		if(is_numeric($_POST['uid'])){
			$map['uid'] = intval($_POST['uid']);
		}else{
			$map['email'] = t($_POST['email']);
		}
		$login = M('User')->where($map)->find();
		if($this->loginLocal($login['login'], $_POST['password'])) {
			$GLOBALS['ts']['mid'] = $_SESSION['adminLogin'] = intval($_SESSION['mid']); 
			return true;			
		} else {
			return false;
		}
	}
	
	/**
	 * 退出后台
	 * @return void
	 */
	public function adminLogout() {
		unset($_SESSION['adminLogin']);
		session_destroy($_SESSION['adminLogin']);
	}

	/**
	 * 验证用户是否需要登录
	 * @return boolean 登陆成功是返回true, 否则返回false
	 */
	public function needLogin() {
		// 验证本地系统登录
		if($this->isLogged()) {
			return false;
		} else {
			$acl = S('system_access');
			if (empty($acl)) {
			    // 匿名访问控制
			    $acl = C('access');
			    // public下的访问控制
			    $publicAccess = include APPS_PATH.'/public/Conf/access.inc.php';
			    $publicAccess = $publicAccess['access'];
			    $publicAccess && $acl = array_merge($acl, $publicAccess);
			    // 应用的访问控制
			    $guestaccess = model ( 'Xdata' )->get('guestConfig');
			    if ( !$guestaccess ){
			    	$guestaccess = model( 'App' )->getAccess();
			    }
			   	$guestaccess && $acl = array_merge( $acl , $guestaccess );
			   	S('system_access', $acl);
			}
			return !($acl[APP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME] === true
				|| $acl[APP_NAME.'/'.MODULE_NAME.'/*'] === true
				|| $acl[APP_NAME.'/*/*'] === true);

			//ACL判断
			if(MODULE_CODE != 'public/Passport' && MODULE_CODE != 'public/Register'){
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * 验证用户是否已登录
	 * 按照session -> cookie的顺序检查是否登陆
	 * @return boolean 登陆成功是返回true, 否则返回false
	 */
	public function isLogged() {
		// 验证本地系统登录
		if(intval($_SESSION['mid']) > 0 && $_SESSION['SITE_KEY']==getSiteKey()) {
			return true;
		} else if($uid = $this->getCookieUid()) {
			return $this->_recordLogin($uid); 
		} else {
			unset($_SESSION['mid']);
			unset($_SESSION['SITE_KEY']);
			return false;
		}
	}

	/**
	 * 根据标示符（email或uid）和未加密的密码获取本地用户（密码为null时不参与验证）
	 * @param string $login 标示符内容（为数字时：标示符类型为uid，其他：标示符类型为email）
	 * @param string|boolean $password 未加密的密码
	 * @return array|boolean 成功获取用户数据时返回用户信息数组，否则返回false
	 */
	public function getLocalUser($login, $password) {
		
		$login = addslashes($login);
		$password = addslashes($password);

		if(empty($login) || empty($password)) {
			$this->error = L('PUBLIC_ACCOUNT_EMPTY');			// 帐号或密码不能为空
			return false;
		}

		if($this->isValidEmail($login)){
			$map = "(login = '{$login}' or email='{$login}') AND is_del=0";
			$users = model('User')->where($map)->find();
			$users&&$users=array($users);
		}else{
			$sql="SELECT u.* FROM ts_user u WHERE u.uname = '{$login}' union SELECT u.* FROM ts_user u WHERE uid = ( SELECT uid FROM ts_user_verified_info WHERE phone = '{$login}' LIMIT 1 )";
			$users=M()->query($sql);
		}
		
		if(!$users) {
			$this->error = L('PUBLIC_ACCOUNT_NOEXIST');			// 帐号不存在
			return false;
		}
		$ucount=count($users);
		$i=0;
		foreach ($users as $user){
			$i++;
			$uid  = $user['uid'];
			// 记录登陆日志，首次登陆判断
			$this->rel = D('LoginRecord')->where("uid = ".$uid)->field('locktime')->find();
	
			$login_error_time = cookie('login_error_time');
	
			if($this->rel['locktime'] > time()) {
				$this->error = L('PUBLIC_ACCOUNT_LOCKED');			// 您的帐号已经被锁定，请稍后再登录
				continue;
			}
			
			if($password && md5(md5($password).$user['login_salt']) != $user['password']) {
				//只锁定查找到的最后一个用户
				if($i<$ucount){
					continue;
				}
				$login_error_time = intval($login_error_time) + 1;
				cookie('login_error_time', $login_error_time);
	
				$this->error = '密码输入错误，您还可以输入'.(6 - $login_error_time).'次';			// 密码错误
	
				if($login_error_time >=6) {
					// 记录锁定账号时间
					$save['locktime'] = time() + 60 * 60;
					$save['ip'] = get_client_ip();
					$save['ctime'] = time();
					$m['uid'] = $save['uid'] = $uid;
	
					$this->error = L('PUBLIC_ACCOUNT_LOCK');		// 您输入的密码错误次数过多，帐号将被锁定1小时
					// 发送锁定通知
					model('Notify')->sendNotify($uid, 'user_lock');
	
					cookie('login_error_time', null);
	
					if(empty($this->rel)) {
						D('')->table(C('DB_PREFIX').'login_record')->add($save);
					} else {
						D('')->table(C('DB_PREFIX').'login_record')->where($m)->save($save);
					}
				}
				return false;
			} else {
				$logData['uid'] = $uid;
				$logData['ip'] = get_client_ip();
				$logData['ctime'] = time();
				D('')->table(C('DB_PREFIX').'login_logs')->add($logData);
				return $user;
			}
		}
	}

	/**
	 * 使用本地帐号登陆（密码为null时不参与验证）
	 * @param string $login 登录名称，邮箱或用户名
	 * @param string $password 密码
	 * @param boolean $is_remember_me 是否记录登录状态，默认为false
	 * @return boolean 是否登录成功
	 */
	public function loginLocal($login, $password = null, $is_remember_me = false) {		
		$res = false;
		$user = $this->getLocalUser($login, $password);
		return $user['uid']>0 ? $this->_recordLogin($user['uid'], $is_remember_me) : false;
	}

	/**
	 * 使用本地帐号登陆，无密码
	 * @param string $login 登录名称，邮箱或用户名
	 * @param boolean $is_remember_me 是否记录登录状态，默认为false
	 * @return boolean 是否登录成功
	 */
	public function loginLocalWithoutPassword($login, $is_remember_me = false) {
		$login = addslashes($login);
		
		if(empty($login)) {
			$this->error = L('PUBLIC_ACCOUNT_NOTEMPTY');			// 帐号不能为空
			return false;
		}

		if($this->isValidEmail($login)){
			$map = " (login='{$login}' OR email='{$login}' ) AND is_del=0 ";
		}else{
			$map = " (login='{$login}' OR uname='{$login}' ) AND is_del=0 ";
		}

		$user = M('User')->where($map)->find();

		if(!$user) {
			$this->error = L('PUBLIC_ACCOUNT_NOEXIST');				// 帐号不存在
			return false;
		}

		return $user['uid']>0 ? $this->_recordLogin($user['uid'], $is_remeber_me) : false;
	}

	//兼容旧版错误
	public function loginLocalWhitoutPassword($login, $is_remember_me = false){
		return $this->loginLocalWithoutPassword($login, $is_remember_me);
	}

	/**
	 * 设置登录状态、记录登录日志
	 * @param integer $uid 用户ID
	 * @param boolean $is_remember_me 是否记录登录状态，默认为false
	 * @return boolean 操作是否成功
	 */
	private function _recordLogin($uid, $is_remember_me = false) {

		// 注册cookie
		if(!$this->getCookieUid() && $is_remember_me ) {
			$expire = 3600 * 24 * 30;
			cookie('TSV3_LOGGED_USER', $this->jiami(C('SECURE_CODE').".{$uid}"), $expire);
		}

		// 记住活跃时间
		cookie('TSV3_ACTIVE_TIME',time() + 60 * 30);
		cookie('login_error_time', null);

		// 更新登陆时间
		model('User')->setField('last_login_time', $_SERVER['REQUEST_TIME'], 'uid='.$uid );
		
		// 记录登陆日志，首次登陆判断
		empty($this->rel) && $this->rel	= D('')->table(C('DB_PREFIX').'login_record')->where("uid = ".$uid)->getField('login_record_id');
		
		$credit_map['uid'] = $uid;
		$credit_map['ctime'] = array('EGT', strtotime(date('Y-m-d', time())));
// 		$firstTime = D('')->table(C('DB_PREFIX').'login_record')->where($credit_map)->count();
// 		if ($firstTime == 0) {
// 			//添加积分
// 			model('Credit')->setUserCredit($uid,'user_login');
// 		}
		// 注册session
		$_SESSION['mid'] = intval($uid);
		$_SESSION['SITE_KEY']=getSiteKey();
		
		$inviterInfo = model('User')->getUserInfo($uid);
	
		$map['ip'] = get_client_ip();
		$map['ctime'] = time();
		$map['locktime'] = 0;

		$this->success = '登录成功，努力加载中。。';

		if($this->rel) {
			D('')->table(C('DB_PREFIX').'login_record')->where("uid = ".$uid)->save($map);
		} else {
			$map['uid'] = $uid;
			D('')->table(C('DB_PREFIX').'login_record')->add($map);
		}
		
		return true;
	}

	/**
	 * 注销本地登录
	 * @return void
	 */
	public function logoutLocal() {
// 	    $_SESSION['SHOW_FEEDBACK_POP'] = 'YES';
// 	    $_SESSION['NEEDNOTIFYSCHOOLADMIN'] = 'YES';
		cookie('TSV3_LOGGED_USER', NULL);	// 注销cookie
		setcookie(ini_get("session.name"),'',time()-8000000,C('COOKIE_PATH'),C('COOKIE_DOMAIN'),false,true);//清除cookie中的sessionid
		session_unset();			//清空session
		session_destroy();			//删除session文件
		if (isset($_SESSION)) {
			unset($_SESSIONI);	//注销$_SESSION
		}
		return TRUE;
// 		unset($_SESSION['mid'],$_SESSION['SITE_KEY']); // 注销session
// 		//UC同步退出
// 		if(UC_SYNC){
// 			echo $this->ucLogout();
// 		}
	}

	/**
	 * 获取cookie中记录的用户ID
	 * @return integer cookie中记录的用户ID
	 */
	public function getCookieUid() {
		static $cookie_uid = null;
		if(isset($cookie_uid) && $cookie_uid !== null) {
			return $cookie_uid;
		}

		$cookie = cookie('TSV3_LOGGED_USER');
		
		$cookie = explode(".", $this->jiemi($cookie));

		$cookie_uid = ($cookie[0] != C('SECURE_CODE')) ? false : $cookie[1];
		
		return $cookie_uid;
	}

	/**
	 * 判断email地址是否合法
	 * @param string $email 邮件地址
	 * @return boolean 邮件地址是否合法
	 */
	public function isValidEmail($email) {
		return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
	}

	/**
	 * 加密函数
	 * @param string $txt 需加密的字符串
	 * @param string $key 加密密钥，默认读取SECURE_CODE配置
	 * @return string 加密后的字符串
	 */
	private function jiami($txt, $key = null) {
		empty($key) && $key = C('SECURE_CODE');
		//有mcrypt扩展时
		if(function_exists('mcrypt_module_open')){
			return desencrypt($txt, $key);
		}
		//无mcrypt扩展时
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
		$nh = rand(0, 64);
		$ch = $chars[$nh];
		$mdKey = md5($key.$ch);
		$mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
		$txt = base64_encode($txt);
		$tmp = '';
		$i = 0;
		$j = 0;
		$k = 0;
		for($i = 0; $i < strlen($txt); $i++) {
			$k = $k == strlen($mdKey) ? 0 : $k;
			$j = ($nh + strpos($chars, $txt [$i]) + ord($mdKey[$k++])) % 64;
			$tmp .= $chars[$j];
		}
		return $ch.$tmp;
	}

	/**
	 * 解密函数
	 * @param string $txt 待解密的字符串
	 * @param string $key 解密密钥，默认读取SECURE_CODE配置
	 * @return string 解密后的字符串
	 */
	private function jiemi($txt, $key = null) {
		empty($key) && $key = C('SECURE_CODE');
		//有mcrypt扩展时
		if(function_exists('mcrypt_module_open')){
			return desdecrypt($txt, $key);
		}
		//无mcrypt扩展时
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
		$ch = $txt[0];
		$nh = strpos($chars, $ch);
		$mdKey = md5($key.$ch);
		$mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
		$txt = substr($txt, 1);
		$tmp = '';
		$i = 0;
		$j = 0;
		$k = 0;
		for($i = 0; $i < strlen($txt); $i++) {
			$k = $k == strlen($mdKey) ? 0 : $k;
			$j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
			while($j < 0) {
				$j += 64;
			}
			$tmp .= $chars[$j];
		}
		return base64_decode($tmp);
	}

	/**
	 * UC登录或者注册
	 * @param string $username
	 * @param string $password
	 * @param string $is_remember_me 是否记住登录
	 * @return bool 
	 */
	private function ucLogin($username, $password, $is_remember_me) {

		//载入UC客户端SDK
		include_once SITE_PATH.'/api/uc_client/client.php';
		
		//1. 获取UC信息.
		if($this->isValidEmail($username)){
			$use_email = true;
			$uc_login_type = 2;
		}else{
			$use_email = false;
			$uc_login_type = 0;
		}

		$uc_user = uc_user_login($username,$password,$uc_login_type);

		//2. 已经同步过的直接登录
		$uc_user_ref = ts_get_ucenter_user_ref('',$uc_user['0'],'');
		
		if($uc_user_ref['uid'] && $uc_user_ref['uc_uid'] && $uc_user[0] > 0 ){
			//登录本地帐号
			$result = $uc_user_ref['uid']>0 ? $this->_recordLogin($uc_user_ref['uid'], $is_remeber_me) : false;
			if($result){
				$this->success .= uc_user_synlogin($uc_user[0]);
				return true;
			}else{
				$this->error = '登录失败，请重试';
				return false;
			}
		}

		//3. 关联表无、获取本地帐号信息.
		$ts_user = $this->getLocalUser($username,$password);


		// 调试用-写log
		// $log_message = "============================ \n "
		// 				.date('Y-m-d H:i:s')." \n ".$_SERVER['REQUEST_URI']." \n "
		// 				.var_export($uc_user,true)." \n "
		// 				.var_export($ts_user,true)." \n "
		// 				.var_export($uc_user_ref,true)." \n ";

		// $log_file = SITE_PATH."/ts_uc_log.txt";
		// $result = error_log($log_message,3,$log_file);

		//4. 关联表无、UC有、本地有的
		if( $uc_user[0] > 0 && $ts_user['uid'] > 0 ){
			$result = ts_add_ucenter_user_ref($ts_user['uid'],$uc_user[0],$uc_user[1],$uc_user[3]);
			if(!$result){
				$this->error = '用户不存在或密码错误';
				return false;
			}
			//登录本地帐号
			$result = $this->_recordLogin($ts_user['uid'], $is_remeber_me);
			if($result){
				$this->success .= uc_user_synlogin($uc_user[0]);
				return true;
			}else{
				$this->error = '登录失败，请重试';
				return false;
			}
		}

		//5. 关联表无、UC有、本地无的
		if( $uc_user[0] > 0 && !$ts_user['uid'] ){
			//写入本地系统
			$login_salt = rand(11111, 99999);
			$map['uname'] = $uc_user[1];
			$map['sex'] = 1;
			$map['login_salt'] = $login_salt;
			$map['password'] = md5(md5($uc_user[2]).$login_salt);
			$map['login'] = $map['email'] = $uc_user[3];
			$map['reg_ip'] = get_client_ip();
			$map['ctime'] = time();
			$map['is_audit'] = 1;
			$map['is_active'] = 1;
			$map['first_letter'] = getFirstLetter($uname);
			//如果包含中文将中文翻译成拼音
			if ( preg_match('/[\x7f-\xff]+/', $map['uname'] ) ){
				//昵称和呢称拼音保存到搜索字段
				$map['search_key'] = $map['uname'].' '.model('PinYin')->Pinyin( $map['uname'] );
			} else {
				$map['search_key'] = $map['uname'];
			}
			$ts_uid = model('User')->add($map);
			if(!$ts_uid){
				$this->error = '本地用户注册失败，请联系管理员';
				return false;
			}
			
			//写入关联表
			$result = ts_add_ucenter_user_ref($ts_uid,$uc_user[0],$uc_user[1],$uc_user[3]);
			if(!$result){
				$this->error = '用户不存在或密码错误';
				return false;
			}
			
			// 添加至默认的用户组
			$registerConfig = model('Xdata')->get('admin_Config:register');
			$userGroup = empty($registerConfig['default_user_group']) ? C('DEFAULT_GROUP_ID') : $registerConfig['default_user_group'];
			model('UserGroupLink')->domoveUsergroup($ts_uid, implode(',', $userGroup));

			// 添加双向关注用户
			$eachFollow = $registerConfig['each_follow'];
			if(!empty($eachFollow)) {
				model('Follow')->eachDoFollow($ts_uid, $eachFollow);
			}
			
			// 添加默认关注用户
			$defaultFollow = $registerConfig['default_follow'];
			$defaultFollow = array_diff(explode(',', $defaultFollow), explode(',', $eachFollow));
			if(!empty($defaultFollow)) {
				model('Follow')->bulkDoFollow($ts_uid, $defaultFollow);
			}

			//登录本地帐号
			$result = $this->_recordLogin($ts_uid, $is_remeber_me);
			if($result){
				$this->success .= uc_user_synlogin($uc_user[0]);
				return true;
			}else{
				$this->error = '登录失败，请重试';
				return false;
			}
		}

		//6. 关联表无、UC无、本地有
		if( $uc_user[0] < 0 && $ts_user['uid'] > 0 ){
			//写入UC
			$uc_uid = uc_user_register($ts_user['uname'], $password, $ts_user['email'],'','', get_client_ip());
			if($uc_uid > 0 ){
				$this->error = 'UC帐号注册失败，请联系管理员';
				return false;
			}
			//写入关联表
			$result = ts_add_ucenter_user_ref($ts_user['uid'],$uc_uid,$ts_user['uname'],$ts_user['email']);
			if(!$result){
				$this->error = '用户不存在或密码错误';
				return false;
			}
			//登录本地帐号
			$result = $this->_recordLogin($ts_user['uid'], $is_remeber_me);
			if($result){
				$this->success .= uc_user_synlogin($uc_uid);
				return true;
			}else{
				$this->error = '登录失败，请重试';
				return false;
			}
		}

		//7. 关联表无、UC无、本地无的
		$this->error = '用户不存在';
		return false;
	}

	/**
	 * UC注销登录
	 * @param int $uid
	 * @return string 退出登录的返回信息 
	 */
	private function ucLogout($uid){
		include_once SITE_PATH.'/api/uc_client/client.php';
		return uc_user_synlogout();
	}
}