<?php
/**
 * 相册应用控制器
 */
class IndexAction extends BaseAction {
	private $commentMax=140;

	/**
	 * 某人的全部专辑
	 * @return void
	 */
	public function albums () {		
		$this->personOrSchool();
		if($this->uid<=0){
			$this->error('不存在的用户');
		}
		// 获取相册数据
		$map['pa.userId'] = $this->uid;
		$map['pa.isDel'] = 0;
		$albumModel=D('Album', 'photo');
		// 默认创建相册
		$album=$albumModel->createNewData($this->uid);
		if($album){
			$data=array('data'=>array($album));
		}else{
			// 相册信息
			$count=$albumModel->where(array('userId'=>$this->uid,'isDel'=>0))->count();
			$data = $albumModel->join('as pa left join ts_photo p on p.albumId=pa.id and p.is_del=0')->where($map)->field('pa.id,pa.userId,pa.privacy,pa.privacy_data,pa.coverImagePath,pa.name,count(p.id) as photoCount')->group(' pa.id')->order(" pa.mTime DESC")->findPage(20,$count);
		}
		// 用户信息
		$userInfo = model('User')->getUserInfo($this->uid);
		// 最后更新时间
// 		$lastUpdateTime = D('Album', 'photo')->where("userId='{$this->uid}'")->order('mTime DESC')->limit(1)->getField('mTime');
		
		// 隐私控制
		if ($this->mid != $this->uid) {
			$relationship = getFollowState($this->mid, $this->uid);
		}
		$this->assign('data', $data);
		$this->assign('userInfo', $userInfo);
		$this->assign('relationship', $relationship);
// 		$this->assign('lastUpdateTime', $lastUpdateTime);

		$this->display();
	}

	/**
	 * 显示一个图片专辑
	 * @return void
	 */
	public function album () {
		$this->personOrSchool();
		$id = intval($_REQUEST['id']);
		// 获取相册信息
		$albumDao = D('Album');
		$album = $albumDao->where("id={$id}")->find();

		if (!$album) {
			$this->assign('jumpUrl', U('photo/Index/index'));
			$this->error('专辑不存在或已被删除！');
		}
		// 隐私控制
		if ($this->mid != $album['userId']) {
			$relationship = getFollowState($this->mid, $this->uid);
			if ($album['privacy'] == 3) {
				$this->error('这个'.$this->appName.'，只有主人自己可见。');
			} else if ($album['privacy'] == 2 && $relationship == 'unfollow') {
				$this->error('这个'.$this->appName.'，只有主人的粉丝可见。');
			} else if ($album['privacy'] == 4) {
				$cookie_password = cookie('album_password_'.$album['id']);
				// 如果密码不正确，则需要输入密码
				if ($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)) {
					$this->need_password($album);
					exit;
				}
			}
		}

		// 获取图片数据
		$order = '`order` DESC, `id` DESC';
		$map['albumId'] = $id;
		$map['userId'] = $this->uid;
		$map['is_del'] = 0;

		$config = photo_getConfig();
		// $photos	= D('Photo', 'photo')->order($order)->where($map)->findPage($config['photo_raws']);
		$photos	= D('Photo', 'photo')->order($order)->where($map)->findPage(10);
// 		$photos	= D('Photo', 'photo')->order($order)->where($map)->findPage(999999);
		$this->assign('photos', $photos);

		// 点击率加1
		$res = $albumDao->where("id={$id} AND userId={$this->uid}")->setInc('readCount');
		//dump($res);dump($albumDao->getLastSql());exit;

		$this->setTitle(getUserName($this->uid).'的'.$this->appName.'：'.$album['name']);

		$this->assign('photo_preview', $config['photo_preview']);
		$this->assign('album', $album);
		$this->display();
	}

	/**
	 * 显示一张图片
	 * @return void
	 */
	public function photo() {
		$this->personOrSchool();
		$uid = $this->uid;
		$aid = intval($_REQUEST['aid']);
		$id = intval($_REQUEST['id']);
		$type = t($_REQUEST['type']);	// 图片来源类型，来自某相册，还是其它的

		// 判断来源类型
		if (!empty($type) && $type != 'mAll') {
			$this->error('错误的链接！');
		}
		$this->assign('type', $type);

		// 获取所在相册信息
		$albumDao = D('Album');
		$album = $albumDao->find($aid);
		if (!$album) {
			$this->assign('jumpUrl', U('photo/Index/index'));
			$this->error('专辑不存在或已被删除！');
		}

		// 获取图片信息
		$photoDao = D('Photo');
		$photo = $photoDao->where(" albumId={$aid} AND `id`={$id} AND userId={$uid} ")->find();
		$this->assign('photo', $photo);

		// 验证图片信息是否正确
		if (!$photo) {
			$this->assign('jumpUrl', U('photo/Index/album', array('uid'=>$this->uid,'id'=>$aid)));
			$this->error('图片不存在或已被删除！');
		}

		// 隐私控制
		if ($this->mid != $album['userId']) {
			$relationship = getFollowState($this->mid, $this->uid);
			if ($album['privacy'] == 3) {
				$this->error('这个'.$this->appName.'的图片，只有主人自己可见。');
			} else if ($album['privacy'] == 2 && $relationship == 'unfollow') {
				$this->error('这个'.$this->appName.'的图片，只有主人的粉丝可见。');
			} else if ($album['privacy'] == 4) {;
				$cookie_password = cookie('album_password_'.$album['id']);
				// 如果密码不正确，则需要输入密码
				if ($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)) {
					$this->need_password($album, $id);
					exit;
				}
			}
		}
		
		$this->assign('album', $album);
		$this->assign('albumId', $album['id']);
		$this->assign('photoId', $id);

// 		// 获取所有图片数据
// 		$photos = $albumDao->getPhotos($this->uid, $aid, '', '`order` DESC, `id` DESC', 0);

// 		// 获取上一页 下一页 和 预览图
// 		if ($photos) {
// 			foreach ($photos as $v) {
// 				$photoIds[] = intval($v['id']);
// 			}
// 			$photoCount = count($photoIds);
// 			// 颠倒数组，取索引
// 			$pindex = array_flip($photoIds);
// 			// 当前位置索引
// 			$now_index = $pindex[$id];
// 			// 上一张
// 			$pre_index = $now_index - 1;
// 			if ($now_index <= 0) {
// 				$pre_index = $photoCount - 1;
// 			}
// 			$pre_photo = $photos[$pre_index];
// 			// 下一张
// 			$next_index = $now_index + 1;
// 			if ($now_index >= $photoCount - 1) {
// 				$next_index = 0;
// 			}
// 			$next_photo = $photos[$next_index];
// 			// 预览图的位置索引
// 			$start_index = $now_index - 2;
// 			if ($photoCount - $start_index < 5) {
// 				$start_index = ($photoCount - 5);
// 			}
// 			if ($start_index < 0) {
// 				$start_index = 0;
// 			}
// 			// 取出预览图列表 最多5个
// 			$preview_photos = array_slice($photos, $start_index, 5);
// 		} else {
// 			$this->error('图片列表数据错误！');
// 		}
		// 点击率加1
		$res = $photoDao->where("id={$id} AND albumId={$aid} AND userId={$this->uid}")->setInc('readCount');
		//dump($res);dump($albumDao->getLastSql());exit;

// 		$this->assign('photoCount', $photoCount);
// 		$this->assign('now', $now_index + 1);
// 		$this->assign('pre', $pre_photo);
// 		$this->assign('next', $next_photo);
// 		$this->assign('previews', $preview_photos);

		unset($pindex);
		unset($photos);
		unset($album);
		unset($preview_photos);

		$this->setTitle(getUserName($this->uid).'的图片：'.$photo['name']);

		$this->display();
	}

	/**
	 * 输入相册密码
	 * @param  [type] $album [description]
	 * @param  string $pid   [description]
	 * @return [type]        [description]
	 */
	public function need_password($album,$pid='') {

		//$aid	=	intval($_REQUEST['aid']);
		//$pid	=	intval($_REQUEST['pid']);
		//$uid	=	intval($_REQUEST['uid']);

		//获取相册信息
		/*$album	=	D('Album')->where(" id='$aid' AND userId='$uid' ")->find();

		if(!$album){
			$this->error('专辑不存在或已被删除！');
		}*/

		$this->assign('username',getUserName($album['userId']));
		$this->assign('pid',$pid);
		$this->assign('album',$album);
		$this->display('need_password');
	}

	//验证相册密码
	public function check_password() {

		$aid	=	intval($_REQUEST['aid']);
		$uid	=	intval($_REQUEST['uid']);
		$password	=	t($_REQUEST['password']);
		$_REQUEST['pid'] && $pid = intval($_REQUEST['pid']);
		//获取相册信息
		$album	=	D('Album')->where(" id='$aid' AND userId='$uid' ")->find();
		$id = $album['id'];
		if($album['isDel'] != 0){
			$this->error('专辑不存在或已被删除！');
		}
		if($password == $album['privacy_data']){
		// 	//跳转到图片页面
		// 	$url	=	U('/Index/photo',array('uid'=>$album['userId'],'aid'=>$album['id']));
		// }else{
			//跳转到相册页面
			$url	=	U('/Index/album',array('uid'=>$album['userId'],'id'=>$album['id']));
		}
		//验证密码
		if( $password == $album['privacy_data'] ){

			//加密保存密码
			$cookie_password	=	md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid);
			//密码保存7天
			cookie( 'album_password_'.$album['id'] , $cookie_password , 3600*24*7 );
			$this->assign('jumpUrl',$url);
			$this->success('密码验证成功，将自动保存7天。马上跳转到'.$this->appName.'页面！');

		}else{
			$this->assign('jumpUrl',$url);
			$this->error('密码验证失败！');
		}
	}

	private function personOrSchool(){
		if (M ( 'UserGroupLink' )->where ( 'uid=' . $this->uid . ' and user_group_id=' . C ( 'USER_GROUP_DREAM_CENTER' ) )->find ()) {
			$this->assign('isSchool','1');
			$this->schoolTop();
		} else {
			$this->assign('isSchool','0');
			$this->_top();
		}
	}
	
	public function schoolTop(){
		// 学校基本信息
		$school = M('School','public')->getSchoolInfo ( $this->uid );
		// 如果学校不存在
		if (! $school) {
			$this->error ( '该主页不存在！' );
		}
		//学校头像
		$school['avatar_url']=getUserFace($this->uid,'b');
		// 学校积分信息
		$integral = M('Integral')->getIntegralRankByUid ( $this->uid, 1 );
		$this->assign ( 'school', $school );
		$this->assign ( 'integral', $integral );
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
		$userData ['visitor_count'] = M ( 'Profile','public' )->getVisitedCount ( $this->uid );
		$this->assign ( 'userData', $userData );
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
		$user_info = model ( 'User' )->getUserInfoByUids (  $this->uid  );
		$this->assign ( 'user_info', $user_info );
		$this->assign ( 'user_addition', $user_addition );
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
	 * 获得评论列表
	 */
	public function getComments(){
		$map['photo_id']=t($_REQUEST['photo_id']);
		$data=M('photo_comment')->join('as pc left join ts_user u on pc.uid=u.uid')->field('pc.uid,u.uname,pc.comment,pc.ctime')->order('pc.ctime desc')->where($map)->select();
		$now=time();
		$nowY=date('Y',$now);
		foreach($data as &$v){
			$avatar=getUserFace($v['uid'], 's');
			$v['avatar']=$avatar;
			$format='n月j日 H:s';
			//不是本年数据显示“年”
			if($nowY!=date('Y',$v['ctime'])){
				$format='Y年n月j日 H:s';
			}
			$v['ctime']=date($format,$v['ctime']);
		}
		$this->ajaxReturn($data,null,1);
	}
	/**
	 * 评论
	 */
	public function doComment(){
		$commont=t($_REQUEST['comment']);
		$photo_id=intval($_REQUEST['photo_id']);
		//安全过滤
		if($this->uid<=0||$photo_id<=0){
			$this->ajaxReturn(null,'参数异常',0);
		}else if($this->mid<=0){
			$this->ajaxReturn(null,'请先登录',0);
		}else if(!$commont||mb_strlen($commont,'UTF-8')>$this->commentMax){
			$this->ajaxReturn(null,'评论字数限制在0~'.$this->commentMax.'之间',0);
		}
		$data['mid']=$this->uid;
		$data['uid']=$this->mid;	
		$data['comment']=$commont;
		$data['photo_id']=$photo_id;
		$data['ctime']=time();
		$res=M('photo_comment')->add($data);
		if($res){
			//读写分离后，insert后直接select会有问题
			$d['uid']=$this->mid;
			$d['uname']=$this->user['uname'];
			$d['comment']=$commont;
			$d['ctime']=date('n月j日 H:i',time());
			$d['avatar']=$this->user['avatar_small'];
			$this->ajaxReturn($d,'评论成功',1);
		}else{
			$this->ajaxReturn(null,'评论失败',0);
		}
	}
}