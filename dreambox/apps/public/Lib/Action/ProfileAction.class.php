<?php
/**
 * ProfileAction 个人档案模块
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class ProfileAction extends Action {
	private $blog;
	private $_schoolModel;
	private $_integralModel;
	private $_courseModel;
	private $_termModel;
	private $pageSize = 10;
	private $num2zh = array (
			'1' => '一',
			'2' => '二',
			'3' => '三',
			'4' => '四',
			'5' => '五',
			'6' => '六',
			'7' => '七',
			'8' => '八',
			'9' => '九' 
	);
	/**
	 * _initialize 模块初始化
	 *
	 * @return void
	 */
	protected function _initialize() {
		// 短域名判断
		if (! isset ( $_GET ['uid'] ) || empty ( $_GET ['uid'] )) {
			$this->uid = $this->mid;
		} elseif (is_numeric ( $_GET ['uid'] )) {
			$this->uid = intval ( $_GET ['uid'] );
		} else {
			$map ['domain'] = t ( $_GET ['uid'] );
			$this->uid = model ( 'User' )->where ( $map )->getField ( 'uid' );
		}
		// 初始化博客的Model
		$this->blog = D ( 'Blog', 'blog' );
		$this->_schoolModel = M ( 'School' );
		$this->_integralModel = M ( 'Integral' );
		$this->_courseModel = M ( 'Course', 'dreambox' );
		$this->_termModel = M ( 'Term', 'dreambox' );
		$this->assign ( 'uid', $this->uid );
	}
	
	/**
	 * 隐私设置
	 */
	public function privacy($uid) {
		if ($this->mid != $uid) {
			$privacy = model ( 'UserPrivacy' )->getPrivacy ( $this->mid, $uid );
			return $privacy;
		} else {
			return true;
		}
	}
	
	/**
	 * 个人档案展示页面
	 */
	public function index() {
		$this->assign ( 'tab', 'myindex' );
		if(empty($this->uid)){
			$this->error('不存在的用户');
		}
		if (M ( 'UserGroupLink' )->where ( 'uid=' . $this->uid . ' and user_group_id=' . C ( 'USER_GROUP_DREAM_CENTER' ) )->find ()) {
			$this->schoolIndex ();
		} else {
			$this->personIndex ();
		}
	}
	/**
	 * 个人主页
	 */
	private function personIndex() {
		$this->commonPage = 0; // 设置为不引用pages.css
		$this->appCssList [] = 'person.css';
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		$this->_tab_menu ();
		
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			// 加载微博筛选信息
			$d ['feed_type'] = t ( $_REQUEST ['feed_type'] ) ? t ( $_REQUEST ['feed_type'] ) : '';
			$d ['feed_key'] = t ( $_REQUEST ['feed_key'] ) ? t ( $_REQUEST ['feed_key'] ) : '';
			$this->assign ( $d );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		
		// 记录访客
		if ($this->mid > 0 && $this->uid > 0 && $this->mid != $this->uid) {
			M ( 'Profile' )->addVisitRecord ( $this->mid, $this->uid );
		}
		// 博客的所有年份
		$this->_blogYears ();
		
		// 添加积分
		// model ( 'Credit' )->setUserCredit ( $this->uid, 'space_access' );
		
		$this->assign ( 'userPrivacy', $userPrivacy );
		// seo
		$seo = model ( 'Xdata' )->get ( "admin_Config:seo_user_profile" );
		$replace ['uname'] = $user_info ['uname'];
		if ($feed_id = model ( 'Feed' )->where ( 'uid=' . $this->uid )->order ( 'publish_time desc' )->limit ( 1 )->getField ( 'feed_id' )) {
			$replace ['lastFeed'] = D ( 'feed_data' )->where ( 'feed_id=' . $feed_id )->getField ( 'feed_content' );
		}
		$replaces = array_keys ( $replace );
		foreach ( $replaces as &$v ) {
			$v = "{" . $v . "}";
		}
		$seo ['title'] = str_replace ( $replaces, $replace, $seo ['title'] );
		$seo ['keywords'] = str_replace ( $replaces, $replace, $seo ['keywords'] );
		$seo ['des'] = str_replace ( $replaces, $replace, $seo ['des'] );
		! empty ( $seo ['title'] ) && $this->setTitle ( $seo ['title'] );
		! empty ( $seo ['keywords'] ) && $this->setKeywords ( $seo ['keywords'] );
		! empty ( $seo ['des'] ) && $this->setDescription ( $seo ['des'] );
		$this->display ('index');
		// $this->display ('index_zjj');
	}
	/**
	 * 学校主页
	 */
	private function schoolIndex() {
		$this->commonPage = 0;
		$this->appCssList [] = 'person.css';
		// 学校基本信息
		$school = $this->_schoolModel->getSchoolInfo ( $this->uid );
		// 如果学校不存在
		if (! $school) {
			$this->error ( '该主页不存在！' );
		}
		$short=getShort1($school['intro'],80);
		if($short['hasMore']){
			$short['output']=$short['output'].'...';
		}
		$school['intro']=$short['output'];
		// 学校积分信息
		$integral = $this->_integralModel->getIntegralRankByUid ( $this->uid, 1 );
		//学校评级
		$score=M('db_school_rate')->join('as sr left join ts_db_term t on sr.term_id=t.id')->where('t.school_id='. $school ['id'])->field('teacher_coverage,teacher_covers,class_coverage,lesson_average,composite_score,over_school,rank')->find();
		// 课表及签到情况
		// 本周周一的日期
		$mondayTime = this_monday ( time () );
		$feedback = $this->getFeedbacks ( $school ['id'], $mondayTime );
		// 博客的所有年份
		$this->_blogYears (1);
		// 首页 相册
		$user_addition ['photoIndex'] = $this->photoIndex ();
		$school['avatar_url']=getUserFace($this->uid,'b');
		$this->assign ( 'school', $school );
		$this->assign ( 'user_addition', $user_addition );
		$this->assign ( 'integral', $integral );
		$this->assign ( 'feedback', $feedback );
		$this->assign ( 'thisMonday', $mondayTime );
		$this->assign ( 'score', $score );
		$this->display ( 'school' );
	}
	/**
	 * 获得指定周的数据
	 */
	public function getWeekFeedback() {
		$mondayTime = intval ( $_GET ['mtime'] );
		$sid = intval ( $_GET ['sid'] );
		$feedback = $this->getFeedbacks ( $sid, $mondayTime );
		$this->ajaxReturn ( $feedback );
	}
	/**
	 * 获得指定周、指定天的详细签到记录
	 */
	public function getFeedbackListAjax() {
		$map ['term_id'] = intval ( $_GET ['term_id'] );
		$map ['week_num'] = intval ( $_GET ['week_num'] );
		$map ['week_day'] = intval ( $_GET ['week_day'] );
		$map ['feedback_status'] = intval ( $_GET ['status'] );
		$courseLog = M ( 'db_select_course' )->join ( 'as sc left join ts_db_course dc on sc.course_id=dc.id left join ts_db_grade g on sc.grade_id=g.id left join ts_user_verified uv on sc.user_id=uv.uid left join ts_db_section s on sc.section_num=s.id' )->where ( $map )->field ( 'sc.user_id as uid,uv.realname,dc.class_name as course_name,concat(g.name,sc.class_num,\'班\') as class_name,s.name as section_num' )->select ();
		$courseLog && $courseLog = $this->_mergeUsersAvatar ( $courseLog );
		$this->ajaxReturn ( $courseLog );
	}
	/**
	 * 获得学校下的教师数据
	 */
	public function getTeachers(){
		$sid=intval($_GET['sid']);
		// 梦想教师信息
		$teachers = $this->_schoolModel->getSchoolTeachers ($sid,999999);
		$teachers && $teachers = $this->_mergeUsersAvatar ( $teachers );
		$teacherData = $teachers;
		$this->ajaxReturn($teacherData);
	}
	/**
	 * 获得学校下的课程数据
	 */
	public function getCourses(){
		$sid=intval($_GET['sid']);
		$term_id=intval($_GET['term_id']);
		$courses=$this->_courseModel->getSchoolCourses($sid,$term_id);
		$this->ajaxReturn($courses);
	}
	private function getFeedbacks($sid, $mondayTime) {
		// 最后一学期
		$map ['school_id'] = $sid;
		$term = M ( 'Term' )->where ( $map )->order ( 'stime desc' )->field ( 'id,stime,etime' )->find ();
		$endM = date ( 'n', $term ['etime'] );
		// 下周一time
		$nextMondayTime = $mondayTime + 86400 * 7;
		$nextMondayM = date ( 'n', $nextMondayTime );
		// 如果下周一时间超过最后一学期结束时间，若最后一学期为秋季，且下周一时间超过1.31，或者最后一学期为春季，且下周一时间超过8月，没有下一周
		if ($nextMondayTime > $term ['etime'] && (($endM > 1 && $endM < 8 && $nextMondayM > 7) || ($endM > 7 || $endM < 2) && $nextMondayM > 1)) {
			$nextMondayTime = 0;
		}
		// 第一学期
		$term = M ( 'Term' )->where ( $map )->order ( 'stime' )->field ( 'id,stime,etime' )->find ();
		// 上周一time
		$preMondayTime = $mondayTime - 86400 * 7;
		// 如果周一时间早于第一学期开学时间，没有上一周
		if ($mondayTime <= $term ['stime']) {
			$preMondayTime = 0;
		}
		$mondayM = date ( 'n', $mondayTime );
		$termName = date ( Y, $mondayTime );
		if ($mondayM > 1 && $mondayM < 8) {
			$termName .= '年春季课表';
		} else {
			//如果是跨年，年份-1
			if(intval($mondayM)<2){
				$termName=$termName-1;
			}
			$termName .= '年秋季课表';
		}
		$map ['name'] = $termName;
		// 当前学期
		$nowTerm = M ( 'Term' )->where ( $map )->order('id desc')->field ( 'id,stime,etime,status' )->find ();
		$nowWeek = week_count ( $nowTerm ['stime'], $mondayTime );
		//没有确认的学期不展示选课和签到数据
		if($nowTerm['status']){
			$feedback = M ( 'Course', 'dreambox' )->getSchoolFeedbacks ( $sid, $nowTerm ['id'], $nowWeek );
		}
		$feedback ['nextMondayTime'] = $nextMondayTime;
		$feedback ['preMondayTime'] = $preMondayTime;
		$feedback ['term'] = $termName . '及签到情况';
		$feedback ['week_num'] = $nowWeek;
		$feedback ['term_id'] = $nowTerm ['id'];
		return $this->processFeedbacks ( $feedback, $mondayTime );
	}
	/**
	 * 处理每周签到记录
	 *
	 * @param unknown $feedback
	 *        	签到记录
	 * @param unknown $mondayTime
	 *        	本周一的日期
	 * @return multitype:string number
	 */
	private function processFeedbacks($feedback, $mondayTime) {
		for($i = 1; $i <= 7; $i ++) {
			$exists = false;
			// 查找星期$i的数据是否存在，不存在填充0
			foreach ( $feedback ['data'] as &$f ) {
				if ($f ['week_day'] == $i) {
					$f ['date'] = date ( 'n月j日', $mondayTime + 86400 * ($i - 1) );
					$exists = true;
				}
			}
			if (! $exists) {
				$feedback ['data'] [] = array (
						'week_day' => $i,
						'feedbacked' => '0',
						'notfeedback' => '0',
						'date' => date ( 'n月j日', $mondayTime + 86400 * ($i - 1) ) 
				);
			}
		}
		
		$ret = array ();
		$time=time();
		foreach ( $feedback ['data'] as $v ) {
			$timeToMonday=$time-$mondayTime-86400*($v ['week_day']-1);
			//是否是今天
			if($timeToMonday<86400&&$timeToMonday>=0){
				$v['isToday']='1';
			}
			$ret [$v ['week_day']] = $v;
		}
		$feedback ['data'] = $ret;
		return $feedback;
	}
	/**
	 * 合并用户基本信息和头像信息
	 *
	 * @param unknown $users        	
	 * @return boolean unknown
	 */
	private function _mergeUsersAvatar($users) {
		if (empty ( $users )) {
			return false;
		}
		$avatarModel = model ( 'Avatar' );
		foreach ( $users as &$u ) {
			$u ['avatar'] = $avatarModel->init ( $u ['uid'] )->getUserAvatar ();
		}
		return $users;
	}
	/**
	 * 基本信息补充
	 */
	private function _additionInfo() {
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		
		// 实名认证的姓名
		$realname = M ( 'UserVerified' )->getRealname ( $this->uid );
		$user_addition ['realname'] = $realname;
		//登录用户的实名认证状态
		$user_addition ['mid_verified'] = M ( 'UserVerified' )->getVerifyStatus ( $this->mid );
		$user_addition ['uid_verified'] = M ( 'UserVerified' )->getVerifyStatus ( $this->uid );
		
		// 星座
		$constellation = M ( 'Profile' )->getConstellation ( $user_info ['birthday'] );
		$user_addition ['constellation'] = $constellation;
		
		// 学校信息
		$school = M ( 'UserVerified' )->getSchoolInfo ( $this->uid );
		$user_addition ['schoolUid'] = M ( 'UserVerified' )->getUidBySid($school['id']);
		$user_addition ['schoolName'] = $school['name'];
		
		// 可用积分
		$integral = M ( 'Integral' )->getAvailableIntegral ( $this->uid );
		$user_addition ['available_integral'] = $integral;
		// 累计积分
		$integral = M ( 'Integral' )->getSumIntegral ( $this->uid );
		$user_addition ['sum_integral'] = $integral;
		
		// 首页 相册
		$user_addition ['photoIndex'] = $this->photoIndex ();
		
		$this->assign ( 'user_addition', $user_addition );
	}
	
	/**
	 * 获取指定用户的应用数据列表
	 *
	 * @return array 指定用户的应用数据列表
	 */
	public function appList() {
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		$this->_assignUserInfo ( $this->uid );
		
		$appArr = $this->_tab_menu ();
		$type = t ( $_GET ['type'] );
		if (! isset ( $appArr [$type] )) {
			$this->error ( '参数出错！！' );
		}
		$this->assign ( 'type', $type );
		$className = ucfirst ( $type ) . 'Protocol';
		$content = D ( $className, $type )->profileContent ( $this->uid );
		if (empty ( $content )) {
			$content = '暂无内容';
		}
		$this->assign ( 'content', $content );
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			// 档案类型
			$ProfileType = model ( 'UserProfile' )->getCategoryList ();
			$this->assign ( 'ProfileType', $ProfileType );
			// 个人资料
			$this->_assignUserProfile ( $this->uid );
			// 获取用户职业信息
			$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->uid );
			if (! empty ( $userCategory )) {
				foreach ( $userCategory as $value ) {
					$user_category .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
				}
			}
			$this->assign ( 'user_category', $user_category );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		$this->setTitle ( $user_info ['uname'] . '的' . L ( 'PUBLIC_APPNAME_' . $type ) );
		$this->setKeywords ( $user_info ['uname'] . '的' . L ( 'PUBLIC_APPNAME_' . $type ) );
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->uid
		// ) );
		// $this->setDescription ( t ( $user_category . $user_info ['location'] . ',' . implode ( ',', $user_tag [$this->uid] ) . ',' . $user_info ['intro'] ) );
		
		$this->display ();
	}
	
	/**
	 * 获取指定应用的信息
	 *
	 * @return void
	 */
	public function appprofile() {
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		
		$d ['widgetName'] = ucfirst ( t ( $_GET ['appname'] ) ) . 'Profile';
		foreach ( $_GET as $k => $v ) {
			$d ['widgetAttr'] [$k] = t ( $v );
		}
		$d ['widgetAttr'] ['widget_appname'] = t ( $_GET ['appname'] );
		$this->assign ( $d );
		
		$this->_assignUserInfo ( array (
				$this->uid 
		) );
		($this->mid != $this->uid) && $this->_assignFollowState ( $this->uid );
		$this->display ();
	}
	
	/**
	 * 获取用户详细资料
	 *
	 * @return void
	 */
	public function data() {
		if (! CheckPermission ( 'core_normal', 'read_data' ) && $this->uid != $this->mid) {
			$this->error ( '对不起，您没有权限浏览该内容!' );
		}
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		$this->_tab_menu ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			// 档案类型
			$ProfileType = model ( 'UserProfile' )->getCategoryList ();
			$this->assign ( 'ProfileType', $ProfileType );
			// 个人资料
			$this->_assignUserProfile ( $this->uid );
			// 获取用户职业信息
			$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->uid );
			if (! empty ( $userCategory )) {
				foreach ( $userCategory as $value ) {
					$user_category .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
				}
			}
			$this->assign ( 'user_category', $user_category );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( $user_info ['uname'] . '的资料' );
		$this->setKeywords ( $user_info ['uname'] . '的资料' );
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
		// $this->uid
		// ) );
		// $this->setDescription ( t ( $user_category . $user_info ['location'] . ',' . implode ( ',', $user_tag [$this->uid] ) . ',' . $user_info ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 获取指定用户的某条动态
	 *
	 * @return void
	 */
	public function feed() {
		$feed_id = intval ( $_GET ['feed_id'] );
		if (empty ( $feed_id )) {
			$this->error ( L ( 'PUBLIC_INFO_ALREADY_DELETE_TIPS' ) );
		}
		
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			$feedInfo = model ( 'Feed' )->get ( $feed_id );
			if (! $feedInfo)
				$this->error ( '该微博不存在或已被删除' );
				// if (intval ( $_GET ['uid'] ) != $feedInfo ['uid'])
				// $this->error ( '参数错误' );
			if ($feedInfo ['is_audit'] == '0' && $feedInfo ['uid'] != $this->mid) {
				$this->error ( '此微博正在审核' );
				exit ();
			}
			if ($feedInfo ['is_del'] == '1') {
				$this->error ( L ( 'PUBLIC_NO_RELATE_WEIBO' ) );
				exit ();
			}
			
			$weiboSet = model ( 'Xdata' )->get ( 'admin_Config:feed' );
			$a ['initNums'] = $weiboSet ['weibo_nums'];
			$a ['weibo_type'] = $weiboSet ['weibo_type'];
			$a ['weibo_premission'] = $weiboSet ['weibo_premission'];
			$this->assign ( $a );
			if ($feedInfo ['from'] == '1') {
				$feedInfo ['from'] = getFromClient ( 6, $feedInfo ['app'], '3G版' );
			} else {
				switch ($feedInfo ['app']) {
					case 'weiba' :
						$feedInfo ['from'] = getFromClient ( 0, $feedInfo ['app'], '微吧' );
						break;
					default :
						$feedInfo ['from'] = getFromClient ( $feedInfo ['from'], $feedInfo ['app'] );
						break;
				}
			}
			// $feedInfo['from'] = getFromClient( $feedInfo['from'] , $feedInfo['app']);
			// 微博图片
			if ($feedInfo ['type'] === 'postimage') {
				$var = unserialize ( $feedInfo ['feed_data'] );
				$feedInfo ['image_body'] = $var ['body'];
				if (! empty ( $var ['attach_id'] )) {
					$var ['attachInfo'] = model ( 'Attach' )->getAttachByIds ( $var ['attach_id'] );
					foreach ( $var ['attachInfo'] as $ak => $av ) {
						$_attach = array (
								'attach_id' => $av ['attach_id'],
								'attach_name' => $av ['name'],
								'attach_url' => getImageUrl ( $av ['save_path'] . $av ['save_name'] ),
								'extension' => $av ['extension'],
								'size' => $av ['size'] 
						);
						$_attach ['attach_small'] = getImageUrl ( $av ['save_path'] . $av ['save_name'], 100, 100, true );
						$_attach ['attach_middle'] = getImageUrl ( $av ['save_path'] . $av ['save_name'], 550 );
						$feedInfo ['attachInfo'] [$ak] = $_attach;
					}
				}
			}
			$this->assign ( 'feedInfo', $feedInfo );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		// seo
		$feedContent = unserialize ( $feedInfo ['feed_data'] );
		$seo = model ( 'Xdata' )->get ( "admin_Config:seo_feed_detail" );
		$replace ['content'] = $feedContent ['content'];
		$replace ['uname'] = $feedInfo ['user_info'] ['uname'];
		$replaces = array_keys ( $replace );
		foreach ( $replaces as &$v ) {
			$v = "{" . $v . "}";
		}
		$seo ['title'] = str_replace ( $replaces, $replace, $seo ['title'] );
		$seo ['keywords'] = str_replace ( $replaces, $replace, $seo ['keywords'] );
		$seo ['des'] = str_replace ( $replaces, $replace, $seo ['des'] );
		! empty ( $seo ['title'] ) && $this->setTitle ( $seo ['title'] );
		! empty ( $seo ['keywords'] ) && $this->setKeywords ( $seo ['keywords'] );
		! empty ( $seo ['des'] ) && $this->setDescription ( $seo ['des'] );
		$this->assign ( 'userPrivacy', $userPrivacy );
		// 赞功能
		$diggArr = model ( 'FeedDigg' )->checkIsDigg ( $feed_id, $this->mid );
		$this->assign ( 'diggArr', $diggArr );
		
		$this->display ();
	}
	
	/**
	 * 获取用户访客列表
	 *
	 * @return void
	 */
	public function visitor() {
		$this->commonPage = 0; // 设置为不引用pages.css
		$this->appCssList [] = 'person.css';
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$key = t ( $_REQUEST ['follow_key'] );
			if ($key === '') {
				$visitor_list = model ( 'Follow' )->getVisitorList ( $this->uid, $this->pageSize );
			} else {
				$visitor_list = model ( 'Follow' )->searchFollows ( $key, 'visitor', 20, $this->uid );
				$this->assign ( 'follow_key', $key );
				$this->assign ( 'jsonKey', json_encode ( $key ) );
			}
			$fids = getSubByKey ( $visitor_list ['data'], 'fid' );
			if ($fids) {
				$uids = array_merge ( $fids, array (
						$this->uid 
				) );
			} else {
				$uids = array (
						$this->uid 
				);
			}
			// 获取用户用户组信息
			$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $uids );
			$this->assign ( 'userGroupData', $userGroupData );
			$this->_assignFollowState ( $uids );
			$this->_assignUserInfo ( $uids );
			$this->_assignUserProfile ( $uids );
			$this->_assignUserTag ( $uids );
			$this->_assignUserCount ( $fids );
			// 更新查看粉丝时间
			// if ($this->uid == $this->mid) {
			// $t = time () - intval ( $GLOBALS ['ts'] ['_userData'] ['view_follower_time'] ); // 避免服务器时间不一致
			// model ( 'UserData' )->setUid ( $this->mid )->updateKey ( 'view_follower_time', $t, true );
			// }
			$this->assign ( 'visitor_list', $visitor_list );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( L ( 'PUBLIC_TA_FOLLWER', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->setKeywords ( L ( 'PUBLIC_TA_FOLLWER', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->display ();
	}
	
	/**
	 * 获取用户关注列表
	 *
	 * @return void
	 */
	public function following() {
		$this->commonPage = 0; // 设置为不引用pages.css
		$this->appCssList [] = 'person.css';
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$key = t ( $_REQUEST ['follow_key'] );
			if ($key === '') {
				$following_list = model ( 'Follow' )->getFollowingList ( $this->uid, t ( $_GET ['gid'] ), $this->pageSize );
			} else {
				$following_list = model ( 'Follow' )->searchFollows ( $key, 'following', 20, $this->uid );
				$this->assign ( 'follow_key', $key );
				$this->assign ( 'jsonKey', json_encode ( $key ) );
			}
			$fids = getSubByKey ( $following_list ['data'], 'fid' );
			
			if ($fids) {
				$uids = array_merge ( $fids, array (
						$this->uid 
				) );
			} else {
				$uids = array (
						$this->uid 
				);
			}
			// 获取用户组信息
			$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $uids );
			$this->assign ( 'userGroupData', $userGroupData );
			$this->_assignFollowState ( $uids );
			$this->_assignUserInfo ( $uids );
			$this->_assignUserProfile ( $uids );
			$this->_assignUserTag ( $uids );
			$this->_assignUserCount ( $fids );
			// 关注分组
			($this->mid == $this->uid) && $this->_assignFollowGroup ( $fids );
			$this->assign ( 'following_list', $following_list );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( L ( 'PUBLIC_TA_FOLLOWING', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->setKeywords ( L ( 'PUBLIC_TA_FOLLOWING', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->display ();
	}
	
	/**
	 * 获取用户粉丝列表
	 *
	 * @return void
	 */
	public function follower() {
		$this->commonPage = 0; // 设置为不引用pages.css
		$this->appCssList [] = 'person.css';
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		M ( 'UserCount' )->resetUserCount ( $this->mid, 'new_folower_count', 0 );
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$key = t ( $_REQUEST ['follow_key'] );
			if ($key === '') {
				$follower_list = model ( 'Follow' )->getFollowerList ( $this->uid, $this->pageSize );
			} else {
				$follower_list = model ( 'Follow' )->searchFollows ( $key, 'follower', 20, $this->uid );
				$this->assign ( 'follow_key', $key );
				$this->assign ( 'jsonKey', json_encode ( $key ) );
			}
			$fids = getSubByKey ( $follower_list ['data'], 'fid' );
			if ($fids) {
				$uids = array_merge ( $fids, array (
						$this->uid 
				) );
			} else {
				$uids = array (
						$this->uid 
				);
			}
			// 获取用户用户组信息
			$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $uids );
			$this->assign ( 'userGroupData', $userGroupData );
			$this->_assignFollowState ( $uids );
			$this->_assignUserInfo ( $uids );
			$this->_assignUserProfile ( $uids );
			$this->_assignUserTag ( $uids );
			$this->_assignUserCount ( $fids );
			// 更新查看粉丝时间
			if ($this->uid == $this->mid) {
				$t = time () - intval ( $GLOBALS ['ts'] ['_userData'] ['view_follower_time'] ); // 避免服务器时间不一致
				model ( 'UserData' )->setUid ( $this->mid )->updateKey ( 'view_follower_time', $t, true );
			}
			$this->assign ( 'follower_list', $follower_list );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( L ( 'PUBLIC_TA_FOLLWER', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->setKeywords ( L ( 'PUBLIC_TA_FOLLWER', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->display ();
	}
	
	/**
	 * 批量获取用户的相关信息加载
	 *
	 * @param string|array $uids
	 *        	用户ID
	 */
	private function _assignUserInfo($uids) {
		! is_array ( $uids ) && $uids = explode ( ',', $uids );
		$user_info = model ( 'User' )->getUserInfoByUids ( $uids );
		$this->assign ( 'user_info', $user_info );
		// dump($user_info);exit;
	}
	
	/**
	 * 获取用户的档案信息和资料配置信息
	 *
	 * @param
	 *        	mix uids 用户uid
	 * @return void
	 */
	private function _assignUserProfile($uids) {
		$data ['user_profile'] = model ( 'UserProfile' )->getUserProfileByUids ( $uids );
		$data ['user_profile_setting'] = model ( 'UserProfile' )->getUserProfileSetting ( array (
				'visiable' => 1 
		) );
		// 用户选择处理 uid->uname
		foreach ( $data ['user_profile_setting'] as $k => $v ) {
			if ($v ['form_type'] == 'selectUser') {
				$field_ids [] = $v ['field_id'];
			}
			if ($v ['form_type'] == 'selectDepart') {
				$field_departs [] = $v ['field_id'];
			}
		}
		foreach ( $data ['user_profile'] as $ku => &$uprofile ) {
			foreach ( $uprofile as $key => $val ) {
				if (in_array ( $val ['field_id'], $field_ids )) {
					$user_info = model ( 'User' )->getUserInfo ( $val ['field_data'] );
					$uprofile [$key] ['field_data'] = $user_info ['uname'];
				}
				if (in_array ( $val ['field_id'], $field_departs )) {
					$depart_info = model ( 'Department' )->getDepartment ( $val ['field_data'] );
					$uprofile [$key] ['field_data'] = $depart_info ['title'];
				}
			}
		}
		$this->assign ( $data );
	}
	
	/**
	 * 根据指定应用和表获取指定用户的标签
	 *
	 * @param
	 *        	array uids 用户uid数组
	 * @return void
	 */
	private function _assignUserTag($uids) {
		// $user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( $uids );
		// $this->assign ( 'user_tag', $user_tag );
	}
	
	/**
	 * 批量获取多个用户的统计数目
	 *
	 * @param array $uids
	 *        	用户uid数组
	 * @return void
	 */
	private function _assignUserCount($uids) {
		$user_count = model ( 'UserData' )->getUserDataByUids ( $uids );
		$this->assign ( 'user_count', $user_count );
	}
	
	/**
	 * 批量获取用户uid与一群人fids的彼此关注状态
	 *
	 * @param array $fids
	 *        	用户uid数组
	 * @return void
	 */
	private function _assignFollowState($fids = null) {
		// 批量获取与当前登录用户之间的关注状态
		$follow_state = model ( 'Follow' )->getFollowStateByFids ( $this->mid, $fids );
		$this->assign ( 'follow_state', $follow_state );
		// dump($follow_state);exit;
	}
	
	/**
	 * 获取用户最后一条微博数据
	 *
	 * @param
	 *        	mix uids 用户uid
	 * @param
	 *        	void
	 */
	private function _assignUserLastFeed($uids) {
		return true; // 目前不需要这个功能
		$last_feed = model ( 'Feed' )->getLastFeed ( $uids );
		$this->assign ( 'last_feed', $last_feed );
	}
	
	/**
	 * 调整分组列表
	 *
	 * @param array $fids
	 *        	指定用户关注的用户列表
	 * @return void
	 */
	private function _assignFollowGroup($fids) {
		$follow_group_list = model ( 'FollowGroup' )->getGroupList ( $this->mid );
		// 调整分组列表
		if (! empty ( $follow_group_list )) {
			$group_count = count ( $follow_group_list );
			for($i = 0; $i < $group_count; $i ++) {
				if ($follow_group_list [$i] ['follow_group_id'] != $data ['gid']) {
					$follow_group_list [$i] ['title'] = (strlen ( $follow_group_list [$i] ['title'] ) + mb_strlen ( $follow_group_list [$i] ['title'], 'UTF8' )) / 2 > 8 ? getShort ( $follow_group_list [$i] ['title'], 3 ) . '...' : $follow_group_list [$i] ['title'];
				}
				if ($i < 2) {
					$data ['follow_group_list_1'] [] = $follow_group_list [$i];
				} else {
					if ($follow_group_list [$i] ['follow_group_id'] == $data ['gid']) {
						$data ['follow_group_list_1'] [2] = $follow_group_list [$i];
						continue;
					}
					$data ['follow_group_list_2'] [] = $follow_group_list [$i];
				}
			}
			if (empty ( $data ['follow_group_list_1'] [2] ) && ! empty ( $data ['follow_group_list_2'] [0] )) {
				$data ['follow_group_list_1'] [2] = $data ['follow_group_list_2'] [0];
				unset ( $data ['follow_group_list_2'] [0] );
			}
		}
		
		$data ['follow_group_status'] = model ( 'FollowGroup' )->getGroupStatusByFids ( $this->mid, $fids );
		
		$this->assign ( $data );
	}
	
	/**
	 * 个人主页头部数据
	 *
	 * @return void
	 */
	public function _top() {
		// 获取用户组信息
		$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $this->uid );
		$this->assign ( 'userGroupData', $userGroupData );
		// 获取用户积分信息
		$userCredit = model ( 'Credit' )->getUserCredit ( $this->uid );
		$this->assign ( 'userCredit', $userCredit );
		// 加载用户关注信息
		($this->mid != $this->uid) && $this->_assignFollowState ( $this->uid );
		// 获取用户统计信息
		$userData = model ( 'UserData' )->getUserData ( $this->uid );
		$userData ['visitor_count'] = M ( 'Profile' )->getVisitedCount ( $this->uid );
		$this->assign ( 'userData', $userData );
		
		// 用户补充信息
		$this->_additionInfo ();
	}
	
	/**
	 * 个人主页标签导航
	 *
	 * @return void
	 */
	public function _tab_menu() {
		// 取全部APP信息
		$map ['status'] = 1;
		$appList = model ( 'App' )->where ( $map )->field ( 'app_name' )->findAll ();
		// 获取APP的HASH数组
		foreach ( $appList as $app ) {
			$appName = strtolower ( $app ['app_name'] );
			$className = ucfirst ( $appName );
			$dao = D ( $className . 'Protocol', strtolower ( $className ), false );
			if (method_exists ( $dao, 'profileContent' )) {
				$appArr [$appName] = L ( 'PUBLIC_APPNAME_' . $appName );
			}
			unset ( $dao );
		}
		$this->assign ( 'appArr', $appArr );
		
		return $appArr;
	}
	
	/**
	 * 个人主页右侧
	 *
	 * @return void
	 */
	public function _sidebar() {
		// 判断用户是否已认证
// 		$isverify = D ( 'user_verified' )->where ( 'verified=1 AND uid=' . $this->uid )->find ();
// 		if ($isverify) {
// 			$this->assign ( 'verifyInfo', $isverify ['info'] );
// 		}
// 		// 判断访问用户是否已认证
// 		if ($this->mid == $this->uid) {
// 			$isMidVerify = true;
// 		} else {
// 			$isMidVerify = D ( 'user_verified' )->where ( 'verified=1 AND uid=' . $this->mid )->find ();
// 			$isMidVerify = ( boolean ) $isMidVerify;
// 		}
// 		$this->assign ( 'isMidVerify', $isMidVerify );
		// 加载用户标签信息
		// $this->_assignUserTag ( array (
		// $this->uid
		// ) );
		// 加载关注列表
		$sidebar_following_list = model ( 'Follow' )->getFollowingList ( $this->uid, null, 6 );
		$this->assign ( 'sidebar_following_list', $sidebar_following_list );
		// dump($sidebar_following_list);exit;
		// 加载粉丝列表
		$sidebar_follower_list = model ( 'Follow' )->getFollowerList ( $this->uid, 6 );
		$this->assign ( 'sidebar_follower_list', $sidebar_follower_list );
		
		// 加载访客列表
		$sidebar_visitor_list = model ( 'Follow' )->getVisitorList ( $this->uid, 6 );
		$this->assign ( 'sidebar_visitor_list', $sidebar_visitor_list );
		
		// 加载用户信息
		$uids = array (
				$this->uid 
		);
		
		$followingfids = getSubByKey ( $sidebar_following_list ['data'], 'fid' );
		$followingfids && $uids = array_merge ( $uids, $followingfids );
		
		$followerfids = getSubByKey ( $sidebar_follower_list ['data'], 'fid' );
		$followerfids && $uids = array_merge ( $uids, $followerfids );
		
		$visitorfids = getSubByKey ( $sidebar_visitor_list ['data'], 'fid' );
		$visitorfids && $uids = array_merge ( $uids, $visitorfids );
		
		$this->_assignUserInfo ( $uids );
	}
	
	/**
	 * 查询用户最近上传的图片显示在首页
	 */
	public function photoIndex() {
		//隐私控制
		$map['userid']=$this->uid;
		if($this->mid!=$this->uid){
			$relationship = getFollowState($this->mid, $this->uid);
			//未关注
			if($relationship=='unfollow'){
				$map['privacy']=1;
			//已关注
			}else if($relationship=='havefollow' || $relationship=='eachfollow'){
				$map['privacy']=array('IN','1,2');
			}
		}
		$path = M ( 'photo' )->where ($map)->order ( 'ctime DESC' )->getField ( 'savepath' );
		if (empty ( $path )) {
			return APPS_URL . '/photo/_static/images/photo_zwzp.gif';
		}
		return UPLOAD_URL.'/'.$path;
	}
	
	/**
	 * 博客数据列表
	 */
	public function blogList() {
		$year = $_REQUEST ['year'];
		$beginNum = $_REQUEST ['beginNum'];
		$pageSize = $_REQUEST ['pageSize'];
		$isSchool = $_REQUEST ['isSchool'];
		if (empty ( $_REQUEST ['year'] )) {
			$year = date ( 'Y', time () );
		}
		if (empty ( $_REQUEST ['beginNum'] )) {
			$beginNum = 0;
		}
		if (empty ( $_REQUEST ['pageSize'] )) {
			$pageSize = 5;
		}
		$begin = strtotime ( $year . '-01-01' );
		$end = strtotime ( ($year + 1) . '-01-01' );
		
		$map ['ts_blog.cTime'] = array (
				array (
						'egt',
						$begin 
				),
				array (
						'lt',
						$end 
				),
				'and' 
		);
// 		$map['begin'] = $begin;
// 		$map['end'] = $end;
		$map ['ts_blog.uid'] = $_REQUEST ['uid'];
		//处理学校博客
		if($isSchool){
			$uvModel=M('UserVerified');
        	$smap['sid']=$uvModel->where('uid='.$map ['ts_blog.uid'])->getField('sid');
        	$smap['verified']='1';
        	$uids=$uvModel->where($smap)->getField('id,uid');
        	$uids&&$uids=implode($uids, ',');
			$uids&&$map ['ts_blog.uid']=array('IN',$uids);
		}
		$map ['ts_blog.status'] = 1;
		$list = $this->blog->getBlogListForBox ( $this->mid, $map, $beginNum, $pageSize );
		
		$result ['list'] = $list;
		
		$count = $this->blog->getBlogCountForBox ( $map );
		$over_count = $count - $beginNum - sizeof ( $list );
		$result ['over_count'] = $over_count < 0 ? 0 : $over_count;
		$result ['next_begin_number'] = $beginNum + sizeof ( $list );
		
// 		echo var_dump($result);
		return $this->ajaxReturn ( $result );
	}
	public function getBlog() {
		$id = $_REQUEST ['id'];
		$blog = $this->blog->getBlog ( $id );
		return $this->ajaxReturn ( $blog );
	}
	
	/**
	 * 转载详细
	 */
	public function shareList() {
		return $this->_getBlogComment ( 1 );
	}
	
	/**
	 * 喜欢详细
	 */
	public function likeList() {
		return $this->_getBlogComment ( 2 );
	}
	
	/**
	 * 评论列表
	 */
	public function commentList() {
		return $this->_getBlogComment ( 3 );
	}
	
	/**
	 * 查询博客的喜欢、转载详细
	 *
	 * @param unknown $blogId        	
	 * @param unknown $type        	
	 */
	private function _getBlogComment($type) {
		$map ['blog_id'] = $_REQUEST ['id'];
		$map ['type'] = $type;
		$data = M ( 'blog_comment' )->where ( $map )->order ( 'ctime desc' )->select ();
		
		$index = 0;
		$user_info = array ();
		foreach ( $data as $blog ) {
		    $user_info [$index] ['id'] = $blog['id'];
			$user_info [$index] ['uid'] = $blog ['mid'];
			$user_info [$index] ['comment'] = $blog ['comment'];
			$user_info [$index] ['ctime'] = date ( 'm-d H:i', $blog ['ctime'] );
			// 用户昵称及头像
			$uid = $blog ['mid'];
			$user = model ( 'User' )->getUserInfoByUids ( $uid );
			$user_info [$index] ['uname'] = $user [$uid] ['uname'];
			$user_info [$index] ['avatar_small'] = $user [$uid] ['avatar_small'];
			
			$index ++;
		}
		// echo var_dump($user_info);
		return $this->ajaxReturn ( $user_info );
	}
	
	/**
	 * 转载
	 */
	public function shareBlog() {
	    $condition['mid'] = $this->mid;
	    $condition['blog_id'] = $_REQUEST ['id'];
	    $condition['type'] = 1;
	    $count = M ( 'blog_comment' )->where($condition)->count();
	    if($count > 0){
	        $this->error='您已经转载过了';
	    }
	    
		$status = $this->_addBlogComment ( 1 );
		return $this->ajaxReturn ( $status );
	}
	
	/**
	 * 喜欢
	 */
	public function likeBlog() {
	    $condition['mid'] = $this->mid;
	    $condition['blog_id'] = $_REQUEST ['id'];
	    $condition['type'] = 2;
	    $count = M ( 'blog_comment' )->where($condition)->count();
	    if($count > 0){
	        $this->error='您已经喜欢过了';
	    }
	    
		$status = $this->_addBlogComment ( 2 );
		return $this->ajaxReturn ( $status );
	}
	
	/**
	 * 评论
	 */
	public function commentBlog() {
		$status = $this->_addBlogComment ( 3 );
		return $this->ajaxReturn ( $status );
	}
	
	/**
	 * 处理转载、喜欢
	 *
	 * @param unknown $type        	
	 */
	private function _addBlogComment($type) {
		$data ['blog_id'] = $_REQUEST ['id'];
		$data ['uid'] = $_REQUEST ['uid'];
		$data ['mid'] = $this->mid;
		$data ['type'] = $type;
		$data ['ctime'] = time ();
		//回复他人的评论时设置关联关系
		if (! empty ( $_REQUEST ['ref_id'] ) && $_REQUEST ['ref_id'] > 0 ) {
		    $map['id'] = $_REQUEST ['ref_id'];
		    $data['uid'] = M ( 'blog_comment' )->where ( $map )->getField('mid');
		    $data['comment_id'] = $map['id'];
		    //文章的作者
		    $data['author'] = M ( 'blog' )->where ( 'id='.$data ['blog_id'] )->getField('uid');
		    
		}
		//评论内容
		if ($_REQUEST ['comment'] !='' ) {
			$data ['comment'] = $_REQUEST ['comment'];
		}
		
		return M ( 'Profile' )->addBlogComment ( $data, $type );
	}
	
	/**
	 * 获取时间抽的所有年份
	 */
	private function _blogYears($is_school=0) {
		$years = $this->blog->getYearsOfBlog ( $this->uid,$is_school );
		$this->assign ( 'blogYears', $years );
	}
	
	/**
	 * 积分页面
	 */
	public function integral() {
		$this->appCssList [] = 'person.css';
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		$this->_assignUserInfo ( $this->uid );
		// 积分规则
		$setting = M ( 'Integral' )->integralSetting ();
		$this->assign ( 'integralSetting', $setting );
		
		$this->display ();
	}
	/**
	 * 跳转到签到记录页面
	 */
	public function to_feedbacks(){
		$this->appCssList [] = 'feeds.css';
		$userinfo['uname']=$this->user['uname'];
		$userinfo['avatar']=$this->user['avatar_big'];
		$userinfo['school']=M('School')->where('id=(select sid from ts_user_verified where uid='.$this->mid.' limit 1)')->getField('name');
		$userinfo['integral'] = M ( 'Integral' )->getAvailableIntegral ( $this->uid );
		$this->assign('userinfo',$userinfo);
		$this->display('feedbacks');
	}
	/**
	 * 请求签到流水
	 */
	public function feedbacks(){
		if (empty ( $this->mid )) {
			$this->error ( '尚未登录' );
		}
		$map ['user_id'] = $this->mid;
// 		$_POST ['stime'] && $map ['create_time'] = array (
// 				'EGT',
// 				$_POST ['stime']
// 		);
// 		$_POST ['etime'] && $map ['create_time'] = array (
// 				'LT',
// 				$_POST ['etime']
// 		);
		$data = M ( 'LessonFeedback' )->where ( $map )->field('course_name,hours_name,grade_name,class_name,section_num,lesson_time,status')->order('create_time desc')->findPage (5);
		$this->ajaxReturn ( $data,null,1 );		
	}
	/**
	 * 积分明细
	 */
	public function integralDetail() {
		$map ['ref_id'] = $_REQUEST ['uid'];
		
		$end = strtotime ( $_REQUEST ['end'] ) + 24 * 60 * 60;
		if (! empty ( $_REQUEST ['begin'] ) && ! empty ( $_REQUEST ['end'] )) {
			$map ['ih.ctime'] = array (
					'BETWEEN',
					array (
							strtotime ( $_REQUEST ['begin'] ),
							$end - 1
					) 
			);
		} else if (! empty ( $_REQUEST ['begin'] )) {
			$map ['ih.ctime'] = array (
					'GT',
					strtotime ( $_REQUEST ['begin'] ) 
			);
		} else if (! empty ( $_REQUEST ['end'] )) {
			$map ['ih.ctime'] = array (
					'LT',
					$end
			);
		}
		if (! empty ( $_REQUEST ['type'] ) && $_REQUEST ['type'] > 0) {
			$map ['ih.type'] = ( int ) $_REQUEST ['type'];
		}
		$result = M ( 'Integral' )->integralDetail ( $map, $this->pageSize );
		
		return $this->ajaxReturn ( $result );
	}
	
	/**
	 * 计算总页数
	 *
	 * @param unknown $count        	
	 * @return number
	 */
	private function _pageCount($count) {
		$pageSize = $this->pageSize;
		return ($count % $pageSize) == 0 ? ($count / $pageSize) : (( int ) ($count / $pageSize + 1));
	}
}