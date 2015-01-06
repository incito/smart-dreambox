<?php
class TagAction extends Action {
	/**
	 * 系统配置 - 标签配置 *
	 */
	public function index() {
		$data = M ( 'tag' )->where ( "tag_hid=0" )->order ( '`tag_id` ASC' )->findAll ();
		$this->assign ( 'data', $data );
		$this->display ( 'tag' );
	}
	public function addTag() {
		$this->display ( 'addHidTag' );
	}
	public function doAddHidTag() {
		$tag = D ( "tag" );
		$data ['type'] = '1';
		$data ['tag_hid'] = '0';
		$data ['name'] = $_POST ['name'];
		$check = $tag->create ( $data );
		if (! $check) {
			$this->ajaxReturn ( null, $tag->getError (), 0 );
		}
		
		$result = $tag->add ( $data );
		if ($result) {
			$this->ajaxReturn ( null, '添加成功', 1 );
		} else {
			$this->ajaxReturn ( null, '添加失败,标签不能与已有标签重名', 0 );
		}
	}
	public function getChildTag() {
		$tag_hid = intval ( $_GET ['id'] );
		$data = M ( 'tag' )->where ( "tag_hid={$tag_hid}" )->order ( '`tag_id` ASC' )->findAll ();
		$this->assign ( 'data', $data );
		$this->assign ( "ch", "ch" );
		$this->display ( 'tag' );
	}
	public function addChildTag() {
		$tag_id = intval ( $_GET ['id'] );
		$this->assign ( 'tag_id', $tag_id );
		$this->display ();
	}
	public function doAddChildTag() {
		$data ['tag_hid'] = intval ( $_POST ['id'] );
		
		$data ['name'] = ($_POST ['name']);
		$id = M ( "tag" )->where ( "name='{$data['name']}' AND tag_hid={$data['tag_hid']}" )->find ();
		if ($id) {
			$this->ajaxReturn ( null, '添加失败,该标签名称已经存在', 0 );
		}
		$id = M ( 'tag' )->data ( $data )->add ();
		if ($id) {
			$this->ajaxReturn ( null, '添加新标签成功', 1 );
		}
	}
	// 修改班级
	public function editTag() {
		$id = intval ( $_REQUEST ['id'] );
		$tag = D ( "tag" );
		if ($_POST) {
			$data ['id'] = $id;
			$data ['name'] = $_POST ['name'];
			$check = $tag->create ( $data );
			if (! $check) {
				$this->ajaxReturn ( null, $tag->getError (), 0 );
			}
			$tag->where ( "tag_id=$id" )->save ();
			$this->ajaxReturn ( null, '修改成功', 1 );
		} else {
			$info = $tag->where ( "tag_id=$id" )->find ();
			$this->assign ( "info", $info );
			$this->display ();
		}
	}
	
	// 删除标签
	public function delTag() {
		$id = intval ( $_POST ['id'] );
		$hid = intval ( $_POST ['hid'] );
		$result = 0;
		if ($hid == '0') {
			$result = M ( 'tag' )->where ( "tag_id={$id}" )->delete ();
			if ($result) {
				M ( 'tag' )->where ( "tag_hid={$id}" )->delete ();
				$result = 1;
			}
		} else {
			$result = M ( 'tag' )->where ( "tag_id={$id}" )->delete ();
			if ($result) {
				M ( 'userTag' )->where ( "tag_id={$id}" )->delete ();
				$result = 1;
			}
		}
		$this->ajaxReturn ( null, '', $result );
	}
	public function doDeleteTags() {
		$_POST ['ids'] = explode ( ',', t ( $_POST ['ids'] ) );
		if (empty ( $_POST ['ids'] )) {
			echo 0;
			return;
		}
		$map ['tag_id'] = array (
				'in',
				$_POST ['ids'] 
		);
		echo M ( 'tag' )->where ( $map )->delete () ? '1' : '0';
	}
	
	/**
	 * 获取博文的标签
	 */
	public function getBlogTag() {
		$blog_id = $_GET ['blog_id'];
		$sql = 'SELECT bt.tag_id,t.name FROM ts_blog_tag bt INNER JOIN ts_tag t ON bt.tag_id=t.tag_id AND bt.blog_id=' . $blog_id;
		$result = M ()->query ( $sql );
		$this->ajaxReturn ( $result );
	}
}