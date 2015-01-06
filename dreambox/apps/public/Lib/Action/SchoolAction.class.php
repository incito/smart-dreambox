<?php
/**
 * SchoolAction 学校主页模块
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class SchoolAction extends Action {
	private $_schoolModel;
	/**
	 * _initialize 模块初始化
	 *
	 * @return void
	 */
	protected function _initialize() {
		$this->_schoolModel = M ( 'School' );
		$this->commonPage = 0; // 设置为不引用pages.css
		$this->appCssList [] = 'person.css';
	}
	/**
	 * 获得详情
	 */
	public function detail(){
		$sid=intval($_GET['sid']);
		$data=$this->_schoolModel->getSchoolDetail($sid);
		//是否可以编辑的用户
		$permission=$this->checkPermission($sid);
		//码表
		if($permission['canEdit']){
			$clist=M('db_school_category')->field('id,title as name')->select();
			$schoolTypeList=M('db_school_nature')->select();
			$educationalTypeList=M('db_educational_nature')->select();
			$this->assign('clist',$clist);
			$this->assign('schoolTypeList',$schoolTypeList);
			$this->assign('educationalTypeList',$educationalTypeList);
			
			//头像编辑信息
			$school_uid=$this->getSchoolUID($sid);
			$user_info = model ( 'User' )->getUserInfo ( $school_uid);
			$this->assign ( 'user_info', $user_info );
		}
		$this->assign('school',$data);
		$this->assign('permission',$permission);
		$this->display();
	}
	/**
	 * 保存修改
	 */
	public function save(){
	  	$data['id']=intval($_POST['id']);
// 	  	$data['name']=t($_POST['name']);
	  	$data['intro']=t($_POST['intro']);
	  	$data['cid0']=intval($_POST['cid0']);
// 	  	$data['sponsors']=t($_POST['sponsors']);
	  	$data['school_type']=intval($_POST['school_type']);
	  	$data['phone']=t($_POST['phone']);
	  	$data['zip_code']=t($_POST['zip_code']);
	  	$data['province']=intval($_POST['provId']);
	  	$data['city']=intval($_POST['cityId']);
	  	$data['area']=intval($_POST['areaId']);
	  	$data['location']=t($_POST['location']);
	  	$data['educational_type']=intval($_POST['educational_type']);
	  	$data['admin_id']=intval($_POST['admin_id']);
	  	
		$msg="";
		$permission=$this->checkPermission($data['id']);
		if(!$permission['canEdit']){
			$msg='你没有该操作的权限！';
		}
// 		else if (!$data['name']){
// 			$msg='学校名称不能为空';
// 		}
// 		else if (get_str_length($data['name']) > 30){
// 			$msg='学校名称不能超过30个字';
// 		}
// 		else if ($this->_schoolModel->where("name='" . $data['name'] . "' and id<>" .$data['id'])->find()) {
// 			$msg='这个梦想中心名称已被占用';
// 		}		
		else if (!$data['cid0']) {
			$msg='请选择学校类型';
		}
		else if (!$data['school_type']) {
			$msg='请选择学校性质';
		}
		else if (!$data['educational_type']) {
			$msg='请选择办学性质';
		}
		else if (get_str_length($data['intro']) > 100) {
			$msg='学校简介请不要超过100个字';
		}
		else if ($data['zip_code'] && mb_strlen($data['zip_code'], 'utf8') != 6 && !is_int($data['zip_code'])) {
			$msg='邮编格式不对';
		}
		else if(!preg_match('/^\d{1,10}[-]?\d{1,20}$/', $data['phone'])){
			$msg='电话格式不对';
		}
		//重新绑定管理员
		if($data['admin_id']){
			if(!$permission['canBind']){
				$msg='你没有该操作权限！';
			}else{
				if(!M('DreamCenter')->doDreamAdmin ( $data['id'], $data['admin_id'] )){
					$msg='要绑定管理员的账号非法';
				}
			}
		}
		if ($msg){
			$this->ajaxReturn(null,$msg,0);
		}
		$data['first_letter']=getFirstLetter($data['name']);
		$res=$this->_schoolModel->save($data);
		$this->ajaxReturn(null,'修改成功!',1);
		
	}
	/**
	 * 保存学校账号的的头像设置操作
	 *
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveAvatar() {
		$sid=intval($_GET['sid']);
		$school_uid=$this->getSchoolUID($sid);
		$dAvatar = model ( 'Avatar' );
		$dAvatar->init ( $school_uid ); // 初始化Model用户id
		// 安全过滤
		$step = t ( $_GET ['step'] );
		if ('upload' == $step) {
			$result = $dAvatar->upload ();
		} else if ('save' == $step) {
			$result = $dAvatar->dosave ();
		}
		$this->ajaxReturn ( $result ['data'], $result ['info'], $result ['status'] );
	}
	/**
	 * 重新绑定管理员的下拉列表数据
	 */
	public function getAdminList(){
		$sid=intval($_REQUEST['sid']);
		if($sid){
			$data=$this->_schoolModel->getSchoolAdminList($sid);
		}
		$this->ajaxReturn($data);
	}
	private function checkPermission($sid){
		$perm=$this->mid>0;
		$map['uid']=$this->mid;
		$map['sid']=$sid;
		$map['verified']='1';
		$isAdmin=M('UserGroup')->isAdmin($this->mid);
		$perm&&$perm=M('UserVerified')->where($map)->find();
		$edit=$isAdmin||($perm&&CheckPermission('dreambox_normal','edit_school'));
		$bind=$isAdmin||($perm&&CheckPermission('dreambox_normal','bind_admin'));
		return array('canEdit'=>$edit,'canBind'=>$bind);
	}
	private function getSchoolUID($sid=0){
		//头像编辑信息
		$map['sid']=intval($_REQUEST['sid']);
		$map['type']='1';
		$school_uid=M('UserVerified')->where($map)->getField('uid');
		return $school_uid;
	} 
}