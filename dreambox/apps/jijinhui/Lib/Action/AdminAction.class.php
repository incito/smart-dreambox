<?php
/**
 * AdminAction
 * 相册管理
 * @uses Action
 * @package Admin
 * @version $2009-7-29$
 * @copyright 2009-2011 LiuXiaoqing
 * @author LiuXiaoqing <liuxiaoqing@thinksns.com>
 * @license ThinkSNS Version 1.6
 */
import ( 'admin.Action.AdministratorAction' );
class AdminAction extends AdministratorAction {
	
	/**
	 * _initialize
	 * 初始化相册管理
	 *
	 * @access public
	 * @return void
	 */
	public function _initialize() {
		// 管理权限判定
		parent::_initialize ();
	}
	
	/**
	 * index
	 * 获取配置信息
	 *
	 * @access public
	 * @return void
	 */
	public function index() {
		$leftju = D ( "jijunhui_left" )->findAll ();
		// 获取权限组
		$group = M ( 'user_group' )->findAll ();
		$this->assign ( "leftju", $leftju );
		$this->assign ( "group", $group );
		$this->display ();
	}
	public function addcontent() {
		$title = $_POST ['title'];
		$content = $_POST ['content'];
		$map ['title'] = $title;
		$map ['content'] =  $content;
		$map ['uid'] = $this->mid;
		$map ['create_time'] = time ();
		$map ['gid'] = $_POST ['leftId'];
		$groupIds = $_POST ['userGroup'];
		$res = D ( "jijunhui_content" )->add ( $map );
		if ($res) {
			$this->addGroupId ( $groupIds, $res );
			$this->success ( "添加成功" );
		} else {
			$this->error ( "添加失败" );
		}
	}
	public function editLeft() {
		$id = intval ( $_GET ['id'] );
		$data = M ( "jijunhui_left" )->where ( "id=$id" )->find ();
		$this->assign ( 'data', $data );
		$this->display ();
	}
	public function saveEditLeft() {
		$id = intval ( $_POST ['id'] );
		$data ['title'] = $_POST ['title'];
		$data ['create_time'] = time ();
		$data ['uid'] = $this->mid;
		$ids = M ( "jijunhui_left" )->where ( "id=$id" )->save ( $data );
		if ($ids) {
			$this->success ( '修改成功' );
		} else {
			$this->error ( '保存失败' );
		}
	}
	public function manage() {
		$res = D ( "jijunhui_content" )->findAll ();
		foreach ( $res as $key => $value ) {
			$gids = D ( "jijunhui_left" )->where ( "id = " . $value ['gid'] )->find ();
			$res [$key] ['gid'] = $gids ['title'];
		}
		$this->assign ( "res", $res );
		$this->display ();
	}
	public function editContent() {
		$id = intval ( $_GET ["id"] );
		$data = M ( "jijunhui_content" )->where ( "id=$id" )->find ();
		$leftju = D ( "jijunhui_left" )->findAll ();
		$groupIds = D ( "jijinhui_group" )->where ( "content_id=$id" )->field('group_id')->select();
		// 获取权限组
		$group = M ( 'user_group' )->findAll ();
		$this->assign ( "leftju", $leftju );
		$this->assign ( "group", $group );
		$this->assign ( "groupIds", $groupIds );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	public function saveEditContent() {
		$id = intval ( $_POST ['id'] );
		$data ['title'] = $_POST ['title'];
		$data ['content'] = $_POST ['content'];
		$data ['is_top'] = $_POST ['is_top'];
		$data ['sort'] = $_POST ['sort'];
		$data ['create_time'] = time ();
		$data ['uid'] = $this->mid;
		$data ['gid'] = $_POST ['leftId'];
		$groupIds = $_POST ['userGroup'];
		$model = M ( "jijunhui_content" );
		$ids = $model->where ( "id=$id" )->save ( $data );
		if ($ids) {
			$this->addGroupId ( $groupIds, $id );
			$this->success ( '修改成功' );
		} else {
			$this->error ( '保存失败' );
		}
	}
	public function delete() {
		$id = intval ( $_GET ['id'] );
		D ( "jijunhui_content" )->where ( "id = $id" )->delete ();
		$this->success ( "删除成功" );
	}
	public function addleft() {
		$res = D ( "jijunhui_left" )->findAll ();
		$this->assign ( "left", $res );
		$this->display ();
	}
	public function addleftsave() {
		$title = $_POST ['title'];
		$map ['title'] = $title;
		$map ['create_time'] = time ();
		$map ['uid'] = $this->mid;
		D ( "jijunhui_left" )->add ( $map );
		$this->success ( "添加成功" );
	}
	public function deleteleft() {
		$id = intval ( $_GET ['id'] );
		D ( "jijunhui_left" )->where ( "id = $id" )->delete ();
		$this->success ( "删除成功" );
	}
	private function addGroupId($groupIds, $content_id) {
		$model = D ( "jijinhui_group" );
		$map ['content_id'] = $content_id;
		$model->where ( $map )->delete ();
		for($i = 0; $i < count ( $groupIds ); $i ++) {
			$map ['group_id'] = $groupIds [$i];
			$model->add ( $map );
		}
	}
}
?>