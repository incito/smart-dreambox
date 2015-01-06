<?php
/**
 * 账号设置控制器
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class AccountAction extends Action {
	private $_profile_model; // 用户档案模型对象字段
	
	/**
	 * 控制器初始化，实例化用户档案模型对象
	 *
	 * @return void
	 */
	protected function _initialize() {
		$this->_profile_model = model ( 'UserProfile' );
		// 从数据库读取
		$profile_category_list = $this->_profile_model->getCategoryList ();
		
		$tab_list [] = array (
				'field_key' => 'register',
				'field_name' => '注册信息' 
		); // 注册信息
		$tab_list [] = array (
				'field_key' => 'avatar',
				'field_name' => '头像设置' 
		); // 头像设置
		$tab_list [] = array (
				'field_key' => 'index',
				'field_name' => '基础信息' 
		); // 基本资料
		   // $tab_list [] = array (
		   // 'field_key' => 'tag',
		   // 'field_name' => L ( 'PUBLIC_PROFILE_TAG' )
		   // ); // 基本资料
		   // $tab_lists = $profile_category_list;
		   
		// foreach ( $tab_lists as $v ) {
		   // $tab_list [] = $v; // 后台添加的资料配置分类
		   // }
		   // $tab_list [] = array (
		   // 'field_key' => 'domain',
		   // 'field_name' => L ( 'PUBLIC_DOMAIN_NAME' )
		   // ); // 个性域名
		$tab_list [] = array (
				'field_key' => 'authenticate',
				'field_name' => '实名信息' 
		); // 申请认证
		$tab_list [] = array (
				'field_key' => 'bank',
				'field_name' => '银行信息' 
		); // 银行信息
		$tab_list [] = array (
				'field_key' => 'security',
				'field_name' => '修改密码' 
		); // 帐号安全
		$tab_list_preference [] = array (
				'field_key' => 'privacy',
				'field_name' => L ( 'PUBLIC_PRIVACY' ) 
		); // 隐私设置
		$tab_list_preference [] = array (
				'field_key' => 'notify',
				'field_name' => '通知设置' 
		); // 通知设置
		$tab_list_preference [] = array (
				'field_key' => 'blacklist',
				'field_name' => '黑名单' 
		); // 黑名单
		$tab_list_security [] = array (
				'field_key' => 'security',
				'field_name' => L ( 'PUBLIC_ACCOUNT_SECURITY' ) 
		); // 帐号安全
		   
		// 插件增加菜单
		$tab_list_security [] = array (
				'field_key' => 'bind',
				'field_name' => '帐号绑定' 
		); // 帐号绑定
		
		$this->assign ( 'tab_list', $tab_list );
		$this->assign ( 'tab_list_preference', $tab_list_preference );
		$this->assign ( 'tab_list_security', $tab_list_security );
	}
	
	/**
	 * 基本设置页面
	 */
	public function index() {
		$user_info = model ( 'User' )->getUserInfo ( $this->mid );
		$data = $this->_getUserProfile ();
		$data ['langType'] = model ( 'Lang' )->getLangType ();
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user_info ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		$this->assign ( 'user_info', $user_info );
		$this->assign ( $data );
		$this->setTitle ( L ( 'PUBLIC_PROFILESET_INDEX' ) ); // 个人设置
		$this->setKeywords ( L ( 'PUBLIC_PROFILESET_INDEX' ) );
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user_info ['category'] . $user_info ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user_info ['intro'] ) );
		// 当前年份
		$year = ( int ) date ( 'Y', time () );
		$this->assign ( 'currentYear', $year );
		// 获取民族
		$nations = M ( 'db_nation' )->select ();
		$this->assign ( 'nations', $nations );
		$this->assign ( 'curNation', $user_info ['nation'] );
		// 区域选项数据
		$areaModel = M ( 'Area' );
		$this->assign ( 'provName', $areaModel->getAreaById ( $user_info ['province'] ) );
		$this->assign ( 'cityName', $areaModel->getAreaById ( $user_info ['city'] ) );
		$this->assign ( 'areaName', $areaModel->getAreaById ( $user_info ['area'] ) );
		// 获取真实姓名
		$verified = M ( 'UserVerified' )->where ( 'uid=' . $user_info ['uid'] )->find ();
		$this->assign ( 'realName', $verified ['realname'] );
		$this->assign ( 'verified', $verified ['verified'] );
		$this->display ();
	}
	
	/**
	 * 注册信息设置页面
	 */
	public function register() {
		$user_info = model ( 'User' )->getUserInfo ( $this->mid );
		$this->assign ( 'user_info', $user_info );
		$this->setTitle ( '注册信息' ); // 个人设置
		$this->setKeywords ( '注册信息' );
		$this->display ();
	}
	
	/**
	 * 展示银行信息
	 */
	public function bank() {
		$user_info = model ( 'UserVerified' )->getUserInfo ( $this->mid );
		$banks = M ( 'bank' )->select ();
		$this->assign ( 'user_info', $user_info );
		$this->assign ( 'banks', $banks );
		$this->display ();
	}
	/**
	 * 扩展信息设置页面
	 *
	 * @param string $extend
	 *        	扩展类目名称(为插件准备)
	 */
	public function _empty($extend) {
		$cid = D ( 'user_profile_setting' )->where ( "field_key='" . ACTION_NAME . "'" )->getField ( 'field_id' );
		$data = $this->_getUserProfile ();
		$data ['cid'] = $cid;
		$this->assign ( $data );
		$this->display ( 'extend' );
	}
	
	/**
	 * 获取登录用户的档案信息
	 *
	 * @return 登录用户的档案信息
	 */
	private function _getUserProfile() {
		$data ['user_profile'] = $this->_profile_model->getUserProfile ( $this->mid );
		$data ['user_profile_setting'] = $this->_profile_model->getUserProfileSettingTree ();
		
		return $data;
	}
	
	/**
	 * 保存基本信息操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveProfile() {
		$res = true;
		$save ['sex'] = 1 == intval ( $_POST ['sex'] ) ? 1 : 2;
		// $save['lang'] = t($_POST['lang']);
		// $save ['intro'] = t ( $_POST ['intro'] );
		// 添加地区信息
		$save ['location'] = t ( $_POST ['location'] );
		if (! $_POST ['provId'] || ! $_POST ['cityId'] || ! $_POST ['areaId']) {
			return $this->ajaxReturn ( "location", '请选择完整地区', 0 );
		}
		isset ( $_POST ['provId'] ) && $save ['province'] = intval ( $_POST ['provId'] );
		isset ( $_POST ['cityId'] ) && $save ['city'] = intval ( $_POST ['cityId'] );
		isset ( $_POST ['areaId'] ) && $save ['area'] = intval ( $_POST ['areaId'] );
		// 修改用户民族
		$save ['nation'] = $_POST ['nation'];
		// 修改用户真实姓名
// 		$uname = t ( $_POST ['uname'] );
// 		$verified ['realname'] = filter_keyword ( $uname );
		M ( 'UserVerified' )->where ( 'uid=' . $this->mid )->save ( $verified );
		
		// $oldName = t ( $_POST ['old_name'] );
		// $save ['uname'] = filter_keyword ( $uname );
		// $res = model ( 'Register' )->isValidName ( $uname, $oldName );
		// if (! $res) {
		// $error = model ( 'Register' )->getLastError ();
		// return $this->ajaxReturn ( null, model ( 'Register' )->getLastError (), $res );
		// }
		// 如果包含中文将中文翻译成拼音
		// if (preg_match ( '/[\x7f-\xff]+/', $save ['uname'] )) {
		// // 昵称和呢称拼音保存到搜索字段
		// $save ['search_key'] = $save ['uname'] . ' ' . model ( 'PinYin' )->Pinyin ( $save ['uname'] );
		// } else {
		// $save ['search_key'] = $save ['uname'];
		// }
		
		$save ['birthday'] = $_POST ['birthday'];
		$save ['z-code'] = $_POST ['z-code'];
		$save ['p-level'] = $_POST ['p-level'];
		$save ['education'] = $_POST ['education'];
		$save ['post'] = $_POST ['post'];
		$save ['weixin'] = $_POST ['weixin'];
		$save ['qq'] = $_POST ['qq'];
		$res = model ( 'User' )->where ( "`uid`={$this->mid}" )->save ( $save );
		model ( 'User' )->cleanCache ( $this->mid );
		// $user_feeds = model ( 'Feed' )->where ( 'uid=' . $this->mid )->field ( 'feed_id' )->findAll ();
		// if ($user_feeds) {
		// $feed_ids = getSubByKey ( $user_feeds, 'feed_id' );
		// model ( 'Feed' )->cleanCache ( $feed_ids, $this->mid );
		// }
		$this->ajaxReturn ( null, '保存成功', 1 );
		// // 保存用户资料配置字段
		// (false !== $res) && $res = $this->_profile_model->saveUserProfile ( $this->mid, $_POST );
		// // 保存用户标签信息
		// $tagIds = t ( $_REQUEST ['user_tags'] );
		// ! empty ( $tagIds ) && $tagIds = explode ( ',', $tagIds );
		// $rowId = intval ( $this->mid );
		// if (! empty ( $rowId )) {
		// $registerConfig = model ( 'Xdata' )->get ( 'admin_Config:register' );
		// if (count ( $tagIds ) > $registerConfig ['tag_num']) {
		// return $this->ajaxReturn ( null, '最多只能设置' . $registerConfig ['tag_num'] . '个标签', false );
		// }
		// // model ( 'Tag' )->setAppName ( 'public' )->setAppTable ( 'user' )->updateTagData ( $rowId, $tagIds );
		// }
		// $result = $this->ajaxReturn ( null, $this->_profile_model->getError (), $res );
		// return $this->ajaxReturn ( null, $this->_profile_model->getError (), $res );
	}
	
	/**
	 * 保存注册信息操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveRegister() {
		// 修改用户昵称
		$uname = t ( $_POST ['uname'] );
		$oldName = t ( $_POST ['old_name'] );
		$save ['uname'] = filter_keyword ( $uname );
		$res = model ( 'Register' )->isValidName ( $uname, $oldName );
		if (! $res) {
			$error = model ( 'Register' )->getLastError ();
			return $this->ajaxReturn ( "uname", model ( 'Register' )->getLastError (), $res );
		}
		// 如果包含中文将中文翻译成拼音
		if (preg_match ( '/[\x7f-\xff]+/', $save ['uname'] )) {
			// 昵称和呢称拼音保存到搜索字段
			$save ['search_key'] = $save ['uname'] . ' ' . model ( 'PinYin' )->Pinyin ( $save ['uname'] );
		} else {
			$save ['search_key'] = $save ['uname'];
		}
		$save ['utype'] = $_POST ['utype'];
		$save ['subject'] = $_POST ['subject'];
		$save ['school-age'] = $_POST ['age'];
		$save ['intro'] = str_replace ( "&#", "&amp;#", $_POST ['intro'] );
		
		$res = model ( 'User' )->where ( "`uid`={$this->mid}" )->save ( $save );
		model ( 'User' )->cleanCache ( $this->mid );
		$this->ajaxReturn ( null, '保存成功', 1 );
	}
	
	/**
	 * 保存银行信息操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveBank() {
		$save ['accountname'] = t ( $_POST ['accountname'] );
		$save ['bankname'] = t ( $_POST ['bankname'] );
		$save ['bankaccount'] = t ( $_POST ['bankaccount'] );
		$save ['branchbankname'] = t ( $_POST ['branchbankname'] );
		$save ['zhifubao'] = t ( $_POST ['zhifubao'] );
		model ( 'UserVerified' )->where ( "`uid`={$this->mid}" )->save ( $save );
		$this->ajaxReturn ( null, '保存成功', 1 );
	}
	/**
	 * 头像设置页面
	 */
	public function avatar() {
		model ( 'User' )->cleanCache ( $this->mid );
		$user_info = model ( 'User' )->getUserInfo ( $this->mid );
		$this->assign ( 'user_info', $user_info );
		
		$this->setTitle ( L ( 'PUBLIC_IMAGE_SETTING' ) ); // 个人设置
		$this->setKeywords ( L ( 'PUBLIC_IMAGE_SETTING' ) );
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user_info ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user_info ['category'] . $user_info ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user_info ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 保存登录用户的头像设置操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveAvatar() {
		$dAvatar = model ( 'Avatar' );
		$dAvatar->init ( $this->mid ); // 初始化Model用户id
		                               // 安全过滤
		$step = t ( $_GET ['step'] );
		if ('upload' == $step) {
			$result = $dAvatar->upload ();
		} else if ('save' == $step) {
			$result = $dAvatar->dosave ();
		}
		model ( 'User' )->cleanCache ( $this->mid );
		$user_feeds = model ( 'Feed' )->where ( 'uid=' . $this->mid )->field ( 'feed_id' )->findAll ();
		if ($user_feeds) {
			$feed_ids = getSubByKey ( $user_feeds, 'feed_id' );
			model ( 'Feed' )->cleanCache ( $feed_ids, $this->mid );
		}
		$this->ajaxReturn ( $result ['data'], $result ['info'], $result ['status'] );
	}
	
	/**
	 * 保存登录用户的头像设置操作，Flash上传
	 *
	 * @return string 操作后的反馈信息
	 */
	public function doSaveUploadAvatar() {
		$data ['big'] = base64_decode ( $_POST ['png1'] );
		$data ['middle'] = base64_decode ( $_POST ['png2'] );
		$data ['small'] = base64_decode ( $_POST ['png3'] );
		if (empty ( $data ['big'] ) || empty ( $data ['middle'] ) || empty ( $data ['small'] )) {
			exit ( 'error=' . L ( 'PUBLIC_ATTACHMENT_UPLOAD_FAIL' ) ); // 图片上传失败，请重试
		}
		if (model ( 'Avatar' )->init ( $this->mid )->saveUploadAvatar ( $data, $this->user )) {
			exit ( 'success=' . L ( 'PUBLIC_ATTACHMENT_UPLOAD_SUCCESS' ) ); // 附件上传成功
		} else {
			exit ( 'error=' . L ( 'PUBLIC_ATTACHMENT_UPLOAD_FAIL' ) ); // 图片上传失败，请重试
		}
	}
	
	/**
	 * 标签设置页面
	 */
	public function tag() {
		$registerConfig = model ( 'Xdata' )->get ( 'admin_Config:register' );
		$this->assign ( 'tag_num', $registerConfig ['tag_num'] );
		$this->display ();
	}
	
	/**
	 * 隐私设置页面
	 */
	public function privacy() {
		$user_privacy = D ( 'UserPrivacy' )->getUserSet ( $this->mid );
		$this->assign ( 'user_privacy', $user_privacy );
		
		$user = model ( 'User' )->getUserInfo ( $this->mid );
		$this->setTitle ( L ( 'PUBLIC_PRIVACY' ) );
		$this->setKeywords ( L ( 'PUBLIC_PRIVACY' ) );
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user ['category'] . $user ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 保存登录用户隐私设置操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSavePrivacy() {
		// dump($_POST);exit;
		$res = model ( 'UserPrivacy' )->dosave ( $this->mid, $_POST );
		$this->ajaxReturn ( null, model ( 'UserPrivacy' )->getError (), $res );
	}
	
	/**
	 * 个性域名设置页面
	 */
	public function domain() {
		// 是否启用个性化域名
		$user = model ( 'User' )->getUserInfo ( $this->mid );
		$data ['user_domain'] = $user ['domain'];
		$this->assign ( $data );
		
		$this->setTitle ( L ( 'PUBLIC_DOMAIN_NAME' ) ); // 个人设置
		$this->setKeywords ( L ( 'PUBLIC_DOMAIN_NAME' ) );
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user ['category'] . $user ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 保存用户个性域名操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveDomain() {
		$domain = t ( $_POST ['domain'] );
		// 验证信息
		if (strlen ( $domain ) < 5) {
			$this->ajaxReturn ( null, '域名长度不能少于5个字符', 0 ); // 仅限5个字符以上20个字符以内的英文/数字/下划线，以英文字母开头，不能含有特殊字符，一经设置，无法更改。
		}
		if (strlen ( $domain ) > 20) {
			$this->ajaxReturn ( null, L ( 'PUBLIC_SHORT_DOMAIN_CHARACTERLIMIT' ), 0 ); // 域名长度不能超过20个字符
		}
		if (! ereg ( '^[a-zA-Z][_a-zA-Z0-9]+$', $domain )) {
			$this->ajaxReturn ( null, '仅限于英文/数字/下划线，以英文字母开头，不能含有特殊字符', 0 ); // 仅限5个字符以上20个字符以内的英文/数字/下划线，以英文字母开头，不能含有特殊字符，一经设置，无法更改。
		}
		
		$keywordConfig = model ( 'Xdata' )->get ( 'keywordConfig' );
		$keywordConfig = explode ( ",", $keywordConfig );
		if (! empty ( $keywordConfig ) && in_array ( $domain, $keywordConfig )) {
			$this->ajaxReturn ( null, L ( 'PUBLIC_DOMAIN_DISABLED' ), 0 ); // 该个性域名已被禁用
		}
		
		// 预留域名使用
		$sysDomin = model ( 'Xdata' )->getConfig ( 'sys_domain', 'site' );
		$sysDomin = explode ( ",", $sysDomin );
		if (! empty ( $sysDomin ) && in_array ( $domain, $sysDomin )) {
			$this->ajaxReturn ( null, L ( 'PUBLIC_DOMAIN_DISABLED' ), 0 ); // 该个性域名已被禁用
		}
		
		if (model ( 'User' )->where ( "uid!={$this->mid} AND domain='{$domain}'" )->count ()) {
			$this->ajaxReturn ( null, L ( 'PUBLIC_DOMAIN_OCCUPIED' ), 0 ); // 此域名已经被使用
		} else {
			$user_info = model ( 'User' )->getUserInfo ( $this->mid );
			! $user_info ['domian'] && model ( 'User' )->setField ( 'domain', "$domain", 'uid=' . $this->mid );
			model ( 'User' )->cleanCache ( $this->mid );
			$this->ajaxReturn ( null, L ( 'PUBLIC_DOMAIN_SETTING_SUCCESS' ), 1 ); // 域名设置成功
		}
	}
	
	/**
	 * 账号安全设置页面
	 */
	public function security() {
		$user = model ( 'User' )->getUserInfo ( $this->mid );
		$this->setTitle ( L ( 'PUBLIC_ACCOUNT_SECURITY' ) );
		$this->setKeywords ( L ( 'PUBLIC_ACCOUNT_SECURITY' ) );
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user ['category'] . $user ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 修改登录用户账号密码操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doModifyPassword() {
		$_POST ['oldpassword'] = t ( $_POST ['oldpassword'] );
		$_POST ['password'] = $_POST ['password'] ;
		$_POST ['repassword'] = $_POST ['repassword'] ;
		// 验证信息
		if ($_POST ['oldpassword'] === '') {
			return $this->ajaxReturn ( "oldpassword", '请填写原始密码', 0 );
		}
		if ($_POST ['password'] === '') {
			return $this->ajaxReturn ( "password", '请填写新密码', 0 );
		}
		if ($_POST ['repassword'] === '') {
			return $this->ajaxReturn ( "repassword", '请填写确认密码', 0 );
		}
		if(strpos($_POST ['repassword'], ' ')!==false){
			return $this->ajaxReturn ( "repassword", '密码不能包含空格', 0 );
		}
		if ($_POST ['password'] != $_POST ['repassword']) {
			return $this->ajaxReturn ( "repassword", L ( 'PUBLIC_PASSWORD_UNSIMILAR' ), 0 ); // 新密码与确认密码不一致
		}
		if (strlen ( $_POST ['password'] ) < 6) {
			return $this->ajaxReturn ( "password", '密码太短了，最少6位', 0 );
		}
		if (strlen ( $_POST ['password'] ) > 15) {
			return $this->ajaxReturn ( "password", '密码太长了，最多15位', 0 );
		}
		if ($_POST ['password'] == $_POST ['oldpassword']) {
			return $this->ajaxReturn ( "password", L ( 'PUBLIC_PASSWORD_SAME' ), 0 ); // 新密码与旧密码相同
		}
		
		$user_model = model ( 'User' );
		$map ['uid'] = $this->mid;
		$user_info = $user_model->where ( $map )->find ();
		
		if ($user_info ['password'] == $user_model->encryptPassword ( $_POST ['oldpassword'], $user_info ['login_salt'] )) {
			$data ['login_salt'] = rand ( 11111, 99999 );
			$data ['password'] = $user_model->encryptPassword ( $_POST ['password'], $data ['login_salt'] );
			$res = $user_model->where ( "`uid`={$this->mid}" )->save ( $data );
			$info = $res ? L ( 'PUBLIC_PASSWORD_MODIFY_SUCCESS' ) : L ( 'PUBLIC_PASSWORD_MODIFY_FAIL' ); // 密码修改成功，密码修改失败
		} else {
			return $this->ajaxReturn ( "oldpassword", '原始密码错误', 0 ); // 原始密码错误
		}
		return $this->ajaxReturn ( null, $info, $res );
	}
	
	/**
	 * 申请认证
	 *
	 * @return void
	 */
	public function authenticate() {
		$verified = M ( 'UserVerified' )->query ( 'select uv.id,uv.uid,uv.sid,uv.realname,uv.idcard,uv.phone,uv.verified,uv.attachment, s.province,s.city,s.area, s.name as school  from ts_user_verified uv left join ts_db_school s on uv.sid=s.id where uv.uid=' . $this->mid . ' LIMIT 1' );
		$verified = $verified [0];
		$verifyruler = model ( 'Xdata' )->lget ( 'square', $_POST );
		$verified ['idcard1'] = $verified ['idcard'];
		$verified ['phone1'] = $verified ['phone'];
		if (! empty ( $verified ) && ($verified ['verified'] == '1' || $verified ['verified'] == '0')) {
			$verified ['idcard1'] = substr_replace ( $verified ['idcard'], '*', 4, 5 );
			$verified ['phone1'] = substr_replace ( $verified ['phone'], '*', 4, 5 );
		}
		// 区域选项数据
		$areaModel = M ( 'Area' );
		$this->assign ( 'provName', $areaModel->getAreaById ( $verified ['province'] ) );
		$this->assign ( 'cityName', $areaModel->getAreaById ( $verified ['city'] ) );
		$this->assign ( 'areaName', $areaModel->getAreaById ( $verified ['area'] ) );
		
		$attach = $this->getAttachUrl ( $verified ['attachment'] );
		$this->assign ( 'verifyruler', $verifyruler );
		$this->assign ( 'verified', $verified );
		$this->assign ( 'attach', $attach );
		$this->display ();
	}
	
	/**
	 * 提交申请认证
	 *
	 * @return void
	 */
	public function doAuthenticate() {
		$verifiedModel = M ( 'UserVerified' );
		$verified = $verifiedModel->where ( "uid={$this->mid}" )->find ();
		if ($verified ['verified'] == '0') {
			$this->error ( '请勿重复提交认证申请！' );
		}
		$data ['phone'] = trim ( $_POST ['phone'] );
		$Regx1 = '/^[0-9]*$/';
		if (strlen ( $data ['phone'] ) !== 11 || preg_match ( $Regx1, $data ['phone'] ) == 0) {
			return $this->ajaxReturn ( "phone", '请输入正确的手机号码格式', 0 );
		}
		if ($verified ['verified'] == '1') {
			$verifiedModel->where ( 'uid =' . $this->mid )->save ( $data );
			$this->success ( '保存成功' );
			return;
		}
		$idcard = substr_replace ( $verified ['idcard'], '*', 4, 5 );
		$phone = substr_replace ( $verified ['phone'], '*', 4, 5 );
		
		$data ['attachment'] = trim ( $_POST ['attachId'] );
		$data ['uid'] = $this->mid;
		$data ['realname'] = trim ( $_POST ['realname'] );
		
		$_POST ['idcard'] && $data ['idcard'] = trim ( $_POST ['idcard'] );
		$data ['sid'] = trim ( $_POST ['schoolId'] );
		
		$Regx2 = '/^[A-Za-z0-9]*$/';
		$Regx3 = '/^[A-Za-z|\x{4e00}-\x{9fa5}]+$/u';
		
		if (strlen ( $data ['attachment'] ) == 0) {
			return $this->ajaxReturn ( "attachId", '请上传身份证附件', 0 );
		}
		if (strlen ( $data ['sid'] ) == 0 || $data ['sid'] == '0') {
			return $this->ajaxReturn ( "schoolId", '请选择所属学校', 0 );
		}
		// if (preg_match ( $Regx3, $data ['realname'] ) == 0 || strlen ( $data ['realname'] ) > 30) {
		// $this->error ( '请输入正确的姓名格式' );
		// }
		if (strlen ( $data ['phone'] ) !== 11 || preg_match ( $Regx1, $data ['phone'] ) == 0) {
			return $this->ajaxReturn ( "phone", '请输入正确的手机号码格式', 0 );
		}
		if (strlen ( $data ['idcard'] ) !== 18 || preg_match ( $Regx2, $data ['idcard'] ) == 0 || preg_match ( $Regx1, substr ( $data ['idcard'], 0, 17 ) ) == 0) {
			return $this->ajaxReturn ( "idcard", '请输入正确的身份证号码', 0 );
		}
		$d ['idcard'] = $data ['idcard'];
		$d ['verified'] = array (
				'IN',
				'\'0\',\'1\'' 
		);
		$d ['uid'] = array (
				'neq',
				$this->mid 
		);
		$idcard = $verifiedModel->where ( $d )->find ();
		if ($idcard) {
			return $this->ajaxReturn ( "idcard", '该身份证号码已存在', 0 );
		}
		$data ['vtime'] = time ();
		if ($verified ['verified'] != '1') {
			$data ['verified'] = '0';
		}
		// }
		if ($verified) {
			// $data['id'] = $_POST['id'];
			$res = $verifiedModel->where ( 'uid =' . $this->mid )->save ( $data );
			$info = '保存成功';
		} else {
			$res = $verifiedModel->add ( $data );
			$info = '申请认证成功，正在等待管理员审核';
		}
		if (false !== $res) {
			// 给用户发送等待审核通知
			$notifyModel = model ( 'Notify' );
			$notifyModel->sendNotify ( $this->mid, 'public_account_doAuthenticate' );
			// 给系统管理员发送审核通知
			$touid = D ( 'user_group_link' )->where ( 'user_group_id=' . C ( 'USER_GROUP_ADMIN' ) )->field ( 'uid' )->findAll ();
			//拼装发送给管理员消息的username数据
			$config['username']='<a class=\'cblue\' target=\'_blank\' href=\''.U('public/Profile/index',array('uid'=>$this->user['uid'])).'\'>'.$this->user['uname'].'</a>';
			foreach ( $touid as $k => $v ) {
				$notifyModel->sendNotify ( $v ['uid'], 'verify_audit',$config );
			}		
			// echo 1;?
			$this->success ( $info );
		} else {
			$this->error ( '操作失败！' );
			// echo 0;
		}
	}
	
	/**
	 * 注销认证
	 *
	 * @return bool 操作是否成功 1:成功 0:失败
	 */
	public function delverify() {
		$verified_group_id = D ( 'user_verified' )->where ( 'uid=' . $this->mid )->getField ( 'usergroup_id' );
		$res = D ( 'user_verified' )->where ( 'uid=' . $this->mid )->delete ();
		$res2 = D ( 'user_group_link' )->where ( 'uid=' . $this->mid . ' and user_group_id=' . $verified_group_id )->delete ();
		if ($res && $res2) {
			// 清除权限组 用户组缓存
			model ( 'Cache' )->rm ( 'perm_user_' . $this->mid );
			model ( 'Cache' )->rm ( 'user_group_' . $this->mid );
			model ( 'Notify' )->sendNotify ( $this->mid, 'public_account_delverify' );
			echo 1;
		} else {
			echo 0;
		}
	}
	
	/**
	 * 黑名单设置
	 *
	 * @return void
	 */
	public function blacklist() {
		$user = model ( 'User' )->getUserInfo ( $this->mid );
		$this->setTitle ( '黑名单' );
		$this->setKeywords ( '黑名单' );
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user ['category'] . $user ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 通知设置
	 *
	 * @return void
	 */
	public function notify() {
		$user_privacy = D ( 'UserPrivacy' )->getUserSet ( $this->mid );
		$this->assign ( 'user_privacy', $user_privacy );
		
		$user = model ( 'User' )->getUserInfo ( $this->mid );
		$this->setTitle ( '通知设置' );
		$this->setKeywords ( '通知设置' );
		// 获取用户职业信息
		$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
		$userCateArray = array ();
		if (! empty ( $userCategory )) {
			foreach ( $userCategory as $value ) {
				$user ['category'] .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
			}
		}
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->mid
		// ) );
		// $this->setDescription ( t ( $user ['category'] . $user ['location'] . ',' . implode ( ',', $user_tag [$this->mid] ) . ',' . $user ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 修改用户身份
	 */
	public function editUserCategory() {
		$this->assign ( 'mid', $this->mid );
		$this->display ();
	}
	
	/**
	 * 执行修改用户身份操作
	 */
	public function doEditUserCategory() {
		$userCategoryIds = t ( $_POST ['user_category_ids'] );
		empty ( $userCategoryIds ) && exit ( $this->error ( '请至少选择一个职业信息' ) );
		$userCategoryIds = explode ( ',', $userCategoryIds );
		$userCategoryIds = array_filter ( $userCategoryIds );
		$userCategoryIds = array_unique ( $userCategoryIds );
		$result = model ( 'UserCategory' )->updateRelateUser ( $this->mid, $userCategoryIds );
		if ($result) {
			// 获取用户身份信息
			$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->mid );
			$userCateArray = array ();
			if (! empty ( $userCategory )) {
				foreach ( $userCategory as $value ) {
					$category .= '<a href="#" class="btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
				}
			}
			$this->ajaxReturn ( $category, L ( 'PUBLIC_SAVE_SUCCESS' ), $result );
		} else {
			$this->ajaxReturn ( null, '职业信息保存失败', $result );
		}
	}
	/**
	 * 学校选择列表
	 */
	public function getSchoolList() {
		$ids = array (
				'province' => t ( $_REQUEST ['provId'] ) 
		);
		$_REQUEST ['cityId'] && $ids ['city'] = t ( $_REQUEST ['cityId'] );
		$_REQUEST ['areaId'] && $ids ['area'] = t ( $_REQUEST ['areaId'] );
		$list = M ( 'School' )->getSchoolByArea ( $ids );
		$res = array ();
		foreach ( $list as $v ) {
			$letter = $v ['letter'];
			if (! $res [$letter]) {
				$res [$letter] = array ();
			}
			array_push ( $res [$letter], array (
					'id' => $v ['id'],
					'name' => $v ['name'] 
			) );
		}
		$this->ajaxReturn ( json_encode ( $res ) );
	}
	/**
	 * 帐号绑定
	 */
	public function bind() {
		// 邮箱绑定
		// $user = M('user')->where('uid='.$this->mid)->field('email')->find();
		// $replace = substr($user['email'],2,-3);
		// for ($i=1;$i<=strlen($replace);$i++){
		// $replacestring.='*';
		// }
		// $data['email'] = str_replace( $replace, $replacestring ,$user['email'] );
		
		// 站外帐号绑定
		$bindData = array ();
		Addons::hook ( 'account_bind_after', array (
				'bindInfo' => &$bindData 
		) );
		$data ['bind'] = $bindData;
		$this->assign ( $data );
		$user = model ( 'User' )->getUserInfo ( $this->mid );
		$this->setTitle ( '帐号绑定' );
		$this->setKeywords ( '帐号绑定' );
		$this->setDescription ( t ( implode ( ',', getSubByKey ( $data ['bind'], 'name' ) ) ) );
		$this->display ();
	}
	
	// 附件地址
	private function getAttachUrl($att_id) {
		$Attach = new Model ();
		$sql = "select attach_id,name,save_path,save_name from ts_attach where attach_id=" . $att_id;
		$data = $Attach->query ( $sql );
		return $data [0];
	}
}