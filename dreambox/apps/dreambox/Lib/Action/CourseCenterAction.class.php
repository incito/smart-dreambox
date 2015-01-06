<?php
class CourseCenterAction extends Action {
	private $courseModel;
	/**
	 * 初始化，配置内容标题
	 *
	 * @return void
	 */
	public function _initialize() {
		$this->courseModel = model ( 'Course' );
	}
	
	/**
	 * 梦想课程首页
	 */
	public function index() {
		// 获取梦想课程简介介绍
		$info = $this->courseModel->getCourseInfo ();
		// 获取所有课程简介视频
		$videos = $this->courseModel->getVideoIntros ();
		
		// 获取所有的课程信息
		$courses = $this->courseModel->getCourses ();
		// 获取课程类型
		$courseType = $this->courseModel->getCourseType ();
		// 获取活动信息
		$courseEvent = M ( 'Event', 'event' )->getCourseCenterEvent ( 4 );
		
		$shortResult = getShort1 ( $info, 200 );
		
		$this->assign ( 'shortResult', $shortResult );
		$this->assign ( 'info', $info );
		$this->assign ( 'videos', $videos );
		$this->assign ( 'courses', $courses );
		$this->assign ( 'courseType', $courseType );
		$this->assign ( 'events', $courseEvent );
		$this->assign ( 'mid', $this->mid );
		$this->assign ( 'tab', 'course' );
		$this->display ( 'index' );
	}
	
	/**
	 * 获取单门课程信息
	 */
	public function courseInfo() {
		// 获取课程id
		$course_id = $_GET ['id'];
		$course = $this->courseModel->getCourseById ( $course_id );
		$courseType = $this->courseModel->getCourseType ();
		// 获取课程类型
		for($i = 0; $i < count ( $courseType ); $i ++) {
			if ($courseType [$i] ['id'] == $course ['type']) {
				$this->assign ( 'courseType', $courseType [$i] ['name'] );
			}
		}
		// 获取适应年级
		if ($course ['gid'] == 1) {
			$suitGradeName = '1-3年级';
		} else if ($course ['gid'] == 2) {
			$suitGradeName = '4-6年级';
		} else if ($course ['gid'] == 3) {
			$suitGradeName = '7-9年级';
		}
		
		// 获取该课程下面的所有视频
		$video = M ( "db_lession_video" )->where ( "lessionID=" . $course_id . " AND videoIfreamHtml like '%.swf'" )->find ();
		$info = $course ['class_intro'];
		$shortResult = getShort1 ( $info, 200 );
		
		// 获取课程标签
		$tagMap ['name'] = $course ['class_name'];
		$tagMap ['tag_hid'] = 2;
		$tag = M ( 'tag' )->where ( $tagMap )->field ( 'tag_id' )->find ();
		$tag_id = $tag ['tag_id'];
		// 获取所有的课程动态,标签必须是该门课程的
		
		// 获取所有关注
		$followModel = model ( 'CourseFollow' );
		$followCondition = array (
				'course_id' => $course_id,
				'user_id' => $this->mid,
				'start' => 0,
				'num' => 4 
		);
		$follows = $followModel->queryFollow ( $followCondition );
		$this->assign ( 'shortResult', $shortResult );
		$this->assign ( 'info', $info );
		$this->assign ( 'name', $course ['class_name'] );
		$this->assign ( 'devName', $course ['class_devname'] );
		$this->assign ( 'relevance', $course ['relevance'] );
		$this->assign ( 'coreQuality', $course ['core_quality'] );
		$this->assign ( 'sumHours', $course ['sum'] );
		$this->assign ( 'suitGradeName', $suitGradeName );
		$this->assign ( 'videoSrc', $video ['videoIfreamHtml'] );
		$this->assign ( 'courseId', $course ['id'] );
		$this->assign ( 'notIncludeTag', true );
		// 需要添加为梦想课程
		$this->assign ( 'tagId', $tag ['tag_id'] );
		$this->assign ( 'follows', $follows );
		$this->assign ( 'tab', 'course' );
		$this->display ( 'courseCenterDetail' );
	}
	/**
	 * 动态加载博客
	 */
	public function courseBlogList() {
		$tagId = $_REQUEST ['tag_id'];
		$beginNum = $_REQUEST ['beginNum'];
		$getNum = $_REQUEST ['getNum'];
		// 获取所有的课程动态,标签必须是该们课程的
		$blog = D ( 'Blog', 'blog' );
		$tagId = explode ( '-', $tagId );
		$tagId = $tagId [count ( $tagId ) - 1];
		$blogs = $blog->getBlogListForTag ( $tagId, $this->mid, $beginNum, $getNum );
		$this->ajaxReturn ( $blogs, $this->mid, 1 );
	}
	/**
	 * 动态切换关注用户
	 */
	public function changeFollowUser() {
		$beginNum = $_REQUEST ['beginNum'];
		$getNum = $_REQUEST ['getNum'];
		$courseId = $_REQUEST ['course_id'];
		$followCondition = array (
				'course_id' => $courseId,
				'user_id' => $this->mid,
				'start' => $beginNum,
				'num' => $getNum 
		);
		// 获取所有关注
		$followModel = model ( 'CourseFollow' );
		$follows = $followModel->queryFollow ( $followCondition );
		$this->ajaxReturn ( $follows, count ( $follows ), 1 );
	}
	/**
	 * 进入下载课程资源
	 */
	public function downloadIndex() {
		// 判断是否是管理员
		$map ['uid'] = $this->mid;
		$map ['user_group_id'] = C ( 'USER_GROUP_ADMIN' );
		$link = M('user_group_link')->where($map)->find();
		if (! (CheckPermission ( 'dreambox_normal', 'select_class' ) || $link)) {
			$this->error ( '对不起，您还不是梦想老师,无法进行资源下载！' );
		}
		// 获取所有的课程信息
		$courses = $this->courseModel->getCourses ();
		
		$this->assign ( "courses", $courses );
		$this->display ( 'resDownload' );
	}
	/**
	 * 打开下载课程资源子列表
	 */
	public function downloadDetail() {
		$couseId = $_POST ['course_id'];
		$sql = 'SELECT f.fileName,f.fileSrc,f.cretatedOn,d.* FROM ts_db_course_download d INNER JOIN ts_db_lesson_file f ON d.file_id=f.fileID AND d.type=1 AND f.lessionID=' . $couseId;
		$files = M ()->query ( $sql );
		foreach ( $files as &$file ) {
			$file ['size'] = $this->formatBytes ( $file ['size'] );
		}
		$this->ajaxReturn ( $files );
	}
	/**
	 * 下载课程资源
	 */
	public function downloadFile() {
		$filePath = UPLOAD_PATH . '/' . $_GET ['fileSrc'];
		$fileName = $_GET ['fileName'];
		$ext = substr ( $filePath, strrpos ( $filePath, '.' ) );
		$fileName = $fileName . $ext;
		if (file_exists ( $filePath )) {
			// Http::download ( $filePath, iconv ( 'UTF-8', 'GB2312', $fileName ) );
			$fp = fopen ( $filePath, "r" );
			$file_size = filesize ( $filePath );
			// 下载文件需要用到的头
			Header ( "Content-type: application/octet-stream" );
			Header ( "Accept-Ranges: bytes" );
			Header ( "Accept-Length:" . $file_size );
			header ( "Content-Length: " . filesize ( $filePath ) );
			Header ( "Content-Disposition: attachment; filename=" . iconv ( 'UTF-8', 'GB2312', $fileName ) );
			$buffer = 1024;
			$file_count = 0;
			// 向浏览器返回数据
			while ( ! feof ( $fp ) && $file_count < $file_size ) {
				$file_con = fread ( $fp, $buffer );
				$file_count += $buffer;
				echo $file_con;
			}
			fclose ( $fp );
		} else {
			$this->error ( L ( 'attach_noexist' ) );
		}
	}
	/**
	 * 添加count
	 */
	public function addDownloadCount() {
		$id = $_GET ['id'];
		$count = M ( 'db_course_download' )->where ( 'id=' . $id )->getField ( 'count' );
		$count += 1;
		M ( 'db_course_download' )->where ( 'id=' . $id )->save ( array (
				'count' => $count 
		) );
		$this->ajaxReturn ( null, $count . '次' );
	}
	private function formatBytes($size) {
		$units = array (
				' B',
				' KB',
				' MB',
				' GB',
				' TB' 
		);
		for($i = 0; $size >= 1024 && $i < 4; $i ++)
			$size /= 1024;
		return round ( $size, 2 ) . $units [$i];
	}
}