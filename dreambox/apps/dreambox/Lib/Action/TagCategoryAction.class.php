<?php
class TagCategoryAction extends Action {
	public function index() {
		$tag_id = $_GET ['tag_id'];
		$tagModel = model ( 'Tag' );
		$tag = $tagModel->getTagById ( $tag_id );
		$hotTag = $tagModel->getHotBlogTag ( 5 );
		
		$this->assign ( 'tag_id', $tag_id );
		$this->assign ( 'tag_name', $tag ['name'] );
		$this->assign ( 'hotTags', $hotTag );
		$this->display ();
	}
	/**
	 * 标签分类首页
	 */
	public function getIndexBlog() {
		$tag_id = $_POST ['tag_id'];
		$blogModel = D ( 'Blog', 'blog' );
		$condition = array (
				'tag_id' => $_POST ['tag_id'],
				'mid' => $this->mid,
				'time_type' => $_POST ['time_type'],
				'order_type' => $_POST ['order_type'] 
		);
		$blogs = $blogModel->getTagCategoryBlog ( $condition, $_POST ['beginNum'], $_POST ['getNum'] );
		$this->ajaxReturn ( $blogs, $this->mid, 1 );
	}
}