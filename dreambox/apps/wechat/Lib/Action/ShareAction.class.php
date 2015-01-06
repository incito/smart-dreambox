<?php
/**
 * 积分信息
 * @author xucaibing
 *
 */
class ShareAction extends WechatAction {
	private $week_array = array (
			1 => '周一',
			2 => '周二',
			3 => '周三',
			4 => '周四',
			5 => '周五',
			6 => '周六',
			0 => '周日' 
	);
	/**
	 * 分享博文
	 */
	public function shareBlog() {
		$param = $this->postData;
		$this->doShareBlog ( $this->getUid (), $param->content, $param->images, $param->tags );
	}
	
	/**
	 * 获取标签
	 */
	public function getTag() {
		$uid = $this->getUid ();
		if (! $uid) {
			$result ['code'] = 2;
			$result ['msg'] = "您还没登陆！";
			exit ( json_encode ( $result ) );
		}
		$result ['code'] = 1;
		$result ['msg'] = 'OK';
		// $result ['data'] = M ()->query ( 'SELECT t.tag_id id, t.name, t.tag_hid hid FROM ts_tag t LEFT JOIN ts_blog_tag b ON b.tag_id = t.tag_id WHERE t.tag_hid = 2 AND t. name <> "" GROUP BY t.tag_id ORDER BY COUNT(b.id) DESC' );
		exit ( json_encode ( $result ) );
	}
	private function doShareBlog($uid, $content, $images, $tags) {
		$data ['uid'] = $uid;
		$data ['cTime'] = time ();
		$data ['mTime'] = $data ['cTime'];
		$data ['title'] = '';
		$data ['content'] = '';
		// 来自微信
		$data ['terminal'] = 1;
		if ($images) {
			$i = 0;
			$prePath = $this->getRelativePath ();
			foreach ( $images as $image ) {
				$path = $prePath . $this->saveBase64Img ( $image );
				// 新浪云
// 				$path = 'http://dreambox1-data.stor.sinaapp.com/uploads' . $this->saveBase64Img ( $image );
				if ($i == 0) {
					$data ['cover'] = $path;
				}
				$imgSrc = '<img src="' . $path . '"/><br/>';
				$content .= $imgSrc;
				$i ++;
			}
		}
		$data ['content'] .= $this->replaceChar ( $this->replaceEnter ( $content ) );
		$blogId = D ( 'Blog', 'blog' )->add ( $data );
		if ($blogId) {
			// 添加文章数
			model ( 'UserData' )->setUid ( $uid )->updateKey ( 'blog_count', 1, true );
			// 添加标签
			// model ( 'Tag' )->addBlogTag ( $uid, $blogId, $tags );
			
			$result = array (
					'code' => 1,
					'msg' => '发表成功',
					'data' => $this->getSuccessData ( $uid ) 
			);
		} else {
			$result = array (
					'code' => 0,
					'msg' => '发表失败' 
			);
		}
		exit ( json_encode ( $result ) );
	}
	/**
	 * 替换回车
	 */
	private function replaceEnter($content) {
		return trim ( str_replace ( array (
				'\r\n',
				'\r',
				'\n' 
		), '<br/>', $content ) );
	}
	
	/**
	 * 替换回车
	 */
	private function replaceChar($content) {
		return trim ( str_replace ( "&#", "&amp;#", $content ) );
	}
	/**
	 * 图片转换
	 */
	private function base64ToImage($imgStr) {
		preg_match ( '/data:image\/([a-z]*);base64/', $imgStr, $match );
		return array (
				$match [1],
				base64_decode ( str_replace ( $match [0], '', $imgStr ) ) 
		);
	}
	/**
	 * 保存图片
	 */
	private function saveBase64Img($imgStr) {
		$imgData = $this->base64ToImage ( $imgStr );
		$datePath = '/' . date ( 'Y/md/H/' );
		$savePath = UPLOAD_PATH . $datePath;
		if (! file_exists ( $savePath )) {
			mkdir ( $savePath, 0777, true );
		}
		// 拼装带后缀的文件名
		$saveName = uniqid () . '.' . $imgData [0];
		file_put_contents ( $savePath . $saveName, $imgData [1] );
		return $datePath . $saveName;
	}
	private function getRelativePath() {
		return str_replace ( 'http://' . $_SERVER ['HTTP_HOST'], '', UPLOAD_URL );
	}
	
	/**
	 * 获取发文成功数据
	 */
	private function getSuccessData($uid) {
		$courseModel = M ( "Course", "dreambox" );
		$data = $courseModel->getCurrentDaySignCourse ( $uid );
		foreach ( $data as & $course ) {
			$course ['sc_id'] = $course ['id'];
			$course ['lesson_date'] = date ( "Y-m-d" );
			$course ['week_day'] = $this->week_array [$course ['week_day']];
			
			$cour = $courseModel->getCourseById ( $course ['course_id'] );
			$course ['course_name'] = $cour ['class_name'];
			
			$grade = $courseModel->getGradeById ( $course ['grade_id'] );
			$course ['grade_name'] = $grade ['name'];
			
			$section = $courseModel->getSectionById ( $course ['section_num'] );
			$course ['section_name'] = $section ['name'];
		}
		return $data;
	}
}

?>