<?php
class CourseAdminAction extends Action {
	protected $course;
	protected $ClassHours;
	protected $Grade;
	public function _initialize() {
		$this->course = D ( 'Course', 'dreambox' );
		$this->ClassHours = D ( 'ClassHours' );
		$this->Grade = D ( 'Grade' );
		// $this->Grade = D('AdreamboxGrade');
		// $this->AdreamboxCourse = D('AdreamboxCourse');
		$this->assign ( 'isAdmin', 1 );
	}
	
	/**
	 * 梦想盒子
	 * 后台管理
	 */
	public function index() {
		$list = $this->course->where ( "is_del = 0" )->findAll ();
		$this->assign ( 'list', $list );
		$this->display ();
	}
	
	// 添加课程
	public function addClass() {
		if ($_POST) {
			$data = $this->course->create ();
			if (false === $data) {
				$this->error ( $this->course->getError () );
			}
			mysql_query ( 'BEGIN' ); // 开启事务
			
			$data_arr = $this->ClassHours->create ();
			$number = $data_arr ['number'];
			$title = $data_arr ['title'];
			$type = $data_arr ['typename'];
			$options ['userId'] = $this->mid;
			$options ['max_size'] = 30000 * 1024 * 1024; // 10MB
			$options ['allow_exts'] = 'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf,flv';
			
			// if (! empty ( $_FILES ['class_logo'] ['name'] )) {
			// $info = X ( 'Xattach' )->upload ( 'class_logo', $options );
			// if ($info ['status']) {
			// $_POST ['imgurl'] = $info ['info'] [0] ['savepath'] . $info ['info'] [0] ['savename'];
			// }
			// } else {
			// $this->error ( "请上传课程封面！" );
			// }
			// $data ['imgurl'] = $_POST ['imgurl'];
			
			// 验证
			if (empty ( $_POST ['class_devname'] )) {
				$this->error ( "开发者不能为空" );
			}
			/*
			 * if (empty($info['info'][1]['id'])) { $this->error("请填写课程资源"); } if (empty($info['info'][2]['id'])) { $this->error("请填写课程预览文件"); } if (empty($info['info'][3]['id'])) { $this->error("请填写课程视频"); }
			 */
			if (empty ( $_POST ['gid'] )) {
				$this->error ( "请选择年级" );
			}
			if (empty ( $_POST ['class_intro'] )) {
				$this->error ( "课程描述不能为空" );
			}
			if (empty ( $_POST ['course_integral'] )) {
				$this->error ( "课程积分不能为空" );
			}
			$data ['imgurl'] = $info ['info'] [0] ['savepath'] . $info ['info'] [0] ['savename'];
			/* $data['resources'] = $info['info'][1]['id']; */
			/*      $data['preview'] = $info['info'][2]['id'];*/
			/*  $data['vdio'] = $info['info'][3]['id'];*/
			$data ['sum'] = count ( $number );
			$data ['type'] = $_POST ['classtype'];
			$data ['class_devname'] = $_POST ['class_devname'];
			$data ['uid'] = $this->mid;
			$data ['course_integral'] = intval ( $_POST ['course_integral'] );
			$data ['is_close'] = $_POST ['classisdel'];
			$id = $this->course->add ( $data );
			$class_hour_data ['hid'] = $id;
			$class_hour_data ['create_time'] = $data_arr ['create_time'];
			
			for($i = 0; $i < count ( $number ); $i ++) {
				$class_hour_data ['number'] = $number [$i];
				$class_hour_data ['title'] = $title [$i];
				if ($class_hour_data ['title'] != '') {
					$res = $this->ClassHours->add ( $class_hour_data );
				}
				if (! $res) {
					mysql_query ( 'ROLLBACK' ); // 出错就回滚
					$this->error ( "添加失败" );
				}
			}
			
			// 添加同步标签
			
			$tag_data = array (
					'tag_name' => $data ['class_name'],
					'type' => '3',
					'tag_hid' => '2' 
			);
			if (M ( 'tag' )->where ( "tag_name='" . $data ['class_name'] . "' and type>0" )->count () == 0) {
				M ( 'tag' )->add ( $tag_data );
			} else {
				M ( 'tag' )->where ( "tag_name = '" . $data ['class_name'] . "'" )->save ( $tag_data );
			}
			mysql_query ( 'COMMIT' );
			$this->success ( "添加成功" );
		} else {
			$TypeName = D ( 'db_course_type' )->findAll ();
			$this->assign ( 'typename', $TypeName );
			$this->assign ( "grade_list", $this->Grade->findAll () );
			$this->display ();
		}
	}
	
	// 删除课程
	public function delClass() {
		$id = intval ( $_GET ['id'] );
		$map ['is_del'] = 1;
		$map ['is_close'] = 1;
		$class_name = $this->course->where ( "id='$id'" )->getField ( "class_name" );
		if ($this->course->where ( 'id=' . $id )->save ( $map )) {
			// 修改同步标签
			M ( 'tag' )->where ( "tag_name='$class_name'" )->delete ();
			
			$this->success ( "删除成功！" );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	
	// 关闭课程
	public function closeClass() {
		$id = intval ( $_GET ['id'] );
		$class_name = $this->course->where ( "id='$id'" )->getField ( "class_name" );
		$map ['is_close'] = 1;
		$res = $this->course->where ( "id =$id" )->save ( $map );
		if ($res) {
			// 修改同步标签
			M ( 'tag' )->where ( "tag_name='$class_name'" )->delete ();
			
			$this->success ( "关闭成功！" );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	
	// 开启课程
	public function openClass() {
		$id = intval ( $_GET ['id'] );
		$class_name = $this->course->where ( "id='$id'" )->getField ( "class_name" );
		$map ['is_close'] = 0;
		$res = $this->course->where ( "id =$id" )->save ( $map );
		if ($res) {
			// 修改同步标签
			$map ['tag_name'] = $class_name;
			$map ['type'] = 3;
			if (M ( 'tag' )->where ( "tag_name='" . $data ['class_name'] . "' and type>0" )->count () == 0) {
				M ( 'tag' )->add ( $map );
			} else {
				M ( 'tag' )->where ( "tag_name = '" . $data ['class_name'] . "'" )->save ( $map );
			}
			
			$this->success ( "开启成功！" );
		} else {
			$this->error ( '开启失败！' );
		}
	}
	// 修改课程
	public function updateClass() {
		$id = intval ( $_REQUEST ['id'] );
		if ($_POST) {
			
			$id = intval ( $_POST ['class_id'] );
			$data = $this->course->create ();
			
			if (false === $data) {
				$this->error ( $this->course->getError () );
			}
			
			mysql_query ( 'BEGIN' ); // 开启事务
			$data_arr = $this->ClassHours->create ();
			
			$number = $data_arr ['number'];
			$title = $data_arr ['title'];
			
			$data ['sum'] = count ( $number );
			$data ['class_devname'] = $_POST ['class_devname'];
			$data ['course_integral'] = intval ( $_POST ['course_integral'] );
			$data ['type'] = $_POST ['classtype'];
			$data ['edit_time'] = time ();
			$options ['userId'] = $this->mid;
			$options ['max_size'] = 30000 * 1024 * 1024; // 10MB
			$options ['allow_exts'] = 'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf,flv';
			
			if (! empty ( $_FILES ['class_logo'] ['name'] )) {
				$info = X ( 'Xattach' )->upload ( 'class_logo', $options );
				if ($info ['status']) {
					$_POST ['imgurl'] = $info ['info'] [0] ['savepath'] . $info ['info'] [0] ['savename'];
				}
			}
			$data ['imgurl'] = $_POST ['imgurl'];
			// 修改同步标签
			$class_name = $this->course->where ( "id=$id" )->getField ( "class_name" );
			$tag_data = array (
					'tag_name' => $data ['class_name'],
					'type' => '3' 
			);
			M ( 'tag' )->where ( "tag_name='$class_name'" )->save ( $tag_data );
			
			$this->course->where ( "id=$id" )->save ( $data );
			$this->ClassHours->where ( "hid=$id" )->delete ();
			/*
			 * if(!$this->ClassHours->where("hid=$id")->delete()) { mysql_query('ROLLBACK'); //出错就回滚 $this->error("操作失败"); }
			 */
			$class_hour_data ['hid'] = $id;
			for($i = 0; $i < count ( $number ); $i ++) {
				$numbercheck = M ( "db_class_hours" )->where ( "hid=$id" )->order ( "hid DESC" )->find ();
				$class_hour_data ['number'] = $numbercheck ['number'] + 1;
				$class_hour_data ['title'] = $title [$i];
				$res = $this->ClassHours->add ( $class_hour_data );
				
				if (! $res) {
					mysql_query ( 'ROLLBACK' ); // 出错就回滚
					$this->error ( "修改失败" );
				}
			}
			
			mysql_query ( 'COMMIT' );
			$this->success ( '修改成功' );
		} else {
			$info = $this->course->where ( "id=$id" )->find ();
			$ClassHours_info = $this->ClassHours->where ( "hid=$id" )->order ( "number asc" )->select ();
			$attachpreview = model ( 'Attach' )->getAttacnName ( $info ['preview'] );
			$attachresources = model ( 'Attach' )->getAttacnName ( $info ['resources'] );
			$attachrevdio = model ( 'Attach' )->getAttacnName ( $info ['vdio'] );
			$TypeName = D ( 'db_course_type' )->findAll ();
			$this->assign ( 'typename', $TypeName );
			$this->assign ( 'attachpreview', $attachpreview );
			$this->assign ( 'attachresources', $attachresources );
			$this->assign ( 'attachrevdio', $attachrevdio );
			$this->assign ( "ClassHours_info", $ClassHours_info );
			$this->assign ( "info", $info );
			$this->display ();
		}
	}
	
	// 年级管理
	public function grade() {
		$this->assign ( "list", $this->Grade->select () );
		$this->display ();
	}
	
	// 年级管理
	public function addGrade() {
		if ($_POST) {
			
			if (false === $this->Grade->create ()) {
				$this->error ( $this->Grade->getError () );
			}
			$this->Grade->add ();
			$this->success ( "添加成功" );
		} else {
			$this->error ( "非法访问" );
		}
	}
	
	// 修改班级
	public function updateGrade() {
		$id = intval ( $_REQUEST ['id'] );
		if ($_POST) {
			if (false === $this->Grade->create ()) {
				$this->error ( $this->Grade->getError () );
			}
			
			$this->Grade->where ( "id=$id" )->save ();
			$this->success ( "修改成功" );
		} else {
			$info = $this->Grade->where ( "id=$id" )->find ();
			$this->assign ( "info", $info );
			$this->display ();
		}
	}
	
	// 删除年级
	public function delGrade() {
		$id = intval ( $_GET ['id'] );
		if ($this->Grade->where ( 'id=' . $id )->delete ()) {
			$this->success ( "删除成功！" );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	
	// 级别管理
	public function TypeManage() {
		$TypeName = D ( 'db_course_type' )->findAll ();
		$this->assign ( 'delUrl', U ( 'admin/CourseAdmin/delType' ) );
		$this->assign ( 'typename', $TypeName );
		$this->display ();
	}
	public function delType() {
		D ( 'db_course_type' )->where ( 'id=' . t ( $_POST ['id'] ) )->delete ();
		$return ['status'] = 1;
		$return ['data'] = '删除成功';
		exit ( json_encode ( $return ) );
	}
	public function DotypeManage() {
		$map ['name'] = t ( $_POST ['typename'] );
		$res = D ( 'db_course_type' )->add ( $map );
		if ($res) {
			$this->success ( "添加成功" );
		} else {
			$this->error ( "添加失败" );
		}
	}
	public function adreminfo() {
		$res = D ( "db_course_info" )->where ( 'id = 1' )->find ();
		$this->assign ( 'value', $res );
		$this->display ();
	}
	public function addcourseinfo() {
		$map ['value'] = $_POST ['course'];
		$res = D ( "db_course_info" )->where ( "id = 1" )->save ( $map );
		if ($res) {
			$this->success ( "修改成功" );
		} else {
			$this->error ( "修改失败" );
		}
	}
	
	/*
	 * Utf-8、gb2312都支持的汉字截取函数 cut_str(字符串, 截取长度, 开始长度, 编码); 编码默认为 utf-8 开始长度默认为 0
	 */
	function cut_str($string, $sublen, $start = 0, $code = 'UTF-8') {
		if ($code == 'UTF-8') {
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all ( $pa, $string, $t_string );
			
			if (count ( $t_string [0] ) - $start > $sublen)
				return join ( '', array_slice ( $t_string [0], $start, $sublen ) ) . "...";
			return join ( '', array_slice ( $t_string [0], $start, $sublen ) );
		} else {
			$start = $start * 2;
			$sublen = $sublen * 2;
			$strlen = strlen ( $string );
			$tmpstr = '';
			
			for($i = 0; $i < $strlen; $i ++) {
				if ($i >= $start && $i < ($start + $sublen)) {
					if (ord ( substr ( $string, $i, 1 ) ) > 129) {
						$tmpstr .= substr ( $string, $i, 2 );
					} else {
						$tmpstr .= substr ( $string, $i, 1 );
					}
				}
				if (ord ( substr ( $string, $i, 1 ) ) > 129)
					$i ++;
			}
			if (strlen ( $tmpstr ) < $strlen)
				$tmpstr .= "...";
			return $tmpstr;
		}
	}
	public function classfile() {
		$id = intval ( $_GET ['id'] );
		$prensent = D ( "db_course" )->where ( "id = " . $id )->find ();
		
		$file = M ( "db_lesson_file" );
		$file = $file->where ( "lessionID=$id" )->select ();
		
		foreach ( $file as $key => $val ) {
			$file [$key] ['fileNames'] = $this->cut_str ( $val ['fileName'], 17, 0 );
			$file [$key] ['fileSrc'] = UPLOAD_URL . '/' . $val ['fileSrc'];
		}
		$config = model ( 'Xdata' )->get ( 'admin_Config:attach' );
		
		$this->assign ( 'fileList', $file );
		$this->assign ( '$uid', $this->mid );
		$this->assign ( 'prensent', $prensent );
		$this->assign ( 'maxSize', $config ['attach_max_size'] );
		$this->assign ( 'allowExts', $config ['attach_allow_extension'] );
		$this->display ();
	}
	public function classvideo() {
		$id = intval ( $_GET ['id'] );
		$prensent = D ( "db_course" )->where ( "id = " . $id )->find ();
		$file = M ( "db_lession_video" );
		$file = $file->where ( "lessionID=$id" )->select ();
		foreach ( $file as $key => $val ) {
			$file [$key] ['videoNames'] = $this->cut_str ( $val ['videoName'], 17, 0 );
		}
		$this->assign ( '$uid', $this->mid );
		$this->assign ( 'videoList', $file );
		$this->assign ( 'prensent', $prensent );
		$this->display ();
	}
	/**
	 * 课程视频简介
	 */
	public function videoIntro() {
		$file = M ( "db_video_intro" )->select ();
		$this->assign ( 'videoList', $file );
		$this->display ();
	}
	/**
	 * 公共资源上传
	 */
	public function otherFile() {
		$id = - 1;
		$prensent = D ( "db_course" )->where ( "id = " . $id )->find ();
		
		$file = M ( "db_lesson_file" );
		$file = $file->where ( "lessionID=$id" )->select ();
		
		foreach ( $file as $key => $val ) {
			$file [$key] ['fileNames'] = $this->cut_str ( $val ['fileName'], 17, 0 );
			$file [$key] ['fileSrc'] = UPLOAD_URL . '/' . $val ['fileSrc'];
		}
		$config = model ( 'Xdata' )->get ( 'admin_Config:attach' );
		
		$this->assign ( 'fileList', $file );
		$this->assign ( '$uid', $this->mid );
		$this->assign ( 'prensent', $prensent );
		$this->assign ( 'maxSize', $config ['attach_max_size'] );
		$this->assign ( 'allowExts', $config ['attach_allow_extension'] );
		$this->display ();
	}
	/**
	 * 课程管理上传资料
	 */
	public function upload_Lession_File() {
		if ($_POST) {
			if (! empty ( $_FILES )) {
				$config = model ( 'Xdata' )->get ( 'admin_Config:attach' );
				$options ['max_size'] = $config ['attach_max_size'];
				
				$options ['allow_exts'] = $config ['attach_allow_extension'];
				$size = $_FILES ['fileSrc'] ['size'];
				$_FILES ['fileSrc'] ['size'] = $_FILES ['fileSrc'] ['size'] / 1024 / 1024;
				
				if ($_FILES ['fileSrc'] ['size'] > intval ( $options ['max_size'] )) {
					$this->error ( '文件大小超过' . $options ['max_size'] . 'MB，请重新上传！' );
				}
				$extend = explode ( ".", $_FILES ['fileSrc'] ['name'] );
				$va = count ( $extend ) - 1;
				$fileType = $extend [$va];
				$logo_options ['save_to_db'] = false;
				$logo = X ( 'Xattach' )->upload ( 'file', $options );
				if ($logo ['status']) {
					$logofile = $logo ['info'] [0] ['savepath'] . $logo ['info'] [0] ['savename'];
				} else {
					$this->error ( $logo ['info'] );
				}
				$_POST ['fileSrc'] = $logofile;
			}
			// $fileName = htmlspecialchars ( $_POST ['fileName'] );
			// $fileSrc = $_POST ['fileSrc'];
			// $createdBy = $this->mid;
			// $cretatedOn = date ( 'Y-m-d' );
			// $lessionID = intval ( $_POST ['lession_id'] );
			// $description = htmlspecialchars ( $_POST ['description'] );
			// $sqlQuery = "INSERT INTO `ts_db_lesson_file` (fileName,fileSrc,fileType,createdBy,cretatedOn,lessionID,description)
			// VALUES ('$fileName','$fileSrc','$fileType',$createdBy,'$cretatedOn',$lessionID,'$description') ";
			$map ['fileName'] = htmlspecialchars ( $_POST ['fileName'] );
			$map ['fileSrc'] = $_POST ['fileSrc'];
			$map ['fileType'] = $fileType;
			$map ['createdBy'] = $this->mid;
			$map ['cretatedOn'] = date ( 'Y-m-d' );
			$map ['lessionID'] = intval ( $_POST ['lession_id'] );
			$map ['description'] = htmlspecialchars ( $_POST ['description'] );
			$result = M ( 'db_lesson_file' )->add ( $map );
			if ($result) {
				// 处理下载次数
				$downModel = M ( 'db_course_download' );
				$downModel->add ( array (
						'type' => '1',
						'file_id' => $result,
						'size' => $size 
				) );
				$this->success ( '上传成功！' );
			} else {
				$this->error ( '上传失败！' );
			}
		}
	}
	/**
	 * 课程管理上传视频
	 */
	public function upload_Lession_Video() {
		if ($_POST) {
			$videoIfreamHtml = h ( $_POST ['videoIfreamHtml'] );
			$videoName = htmlspecialchars ( trim ( $_POST ['videoName'] ) );
			if ($videoName == "") {
				$this->error ( '请填写视频名称！' );
			}
			$data = array (
					'videoName' => htmlspecialchars ( trim ( $_POST ['videoName'] ) ),
					'videoIfreamHtml' => $videoIfreamHtml,
					// 'isHtml' => intval($_POST['isHtml']),
					'remarks' => htmlspecialchars ( $_POST ['videodescription'] ),
					'cretatedOn' => date ( 'Y-m-d' ),
					'lessionID' => intval ( $_POST ['lession_id'] ),
					'createdBy' => $this->mid 
			);
			$result = M ( 'db_lession_video' )->add ( $data );
			if ($result) {
				$this->success ( '上传成功！' );
			} else {
				$this->error ( '上传失败！' );
			}
		}
	}
	/**
	 * 课程管理上传视频
	 */
	public function upload_Video_Intro() {
		if ($_POST) {
			$videoSrc = htmlspecialchars ( trim ( $_POST ['videoIfreamHtml'] ) );
			$title = htmlspecialchars ( trim ( $_POST ['videoName'] ) );
			// $isHtml = intval($_POST['isHtml']);
			$remarks = htmlspecialchars ( $_POST ['videodescription'] );
			$ctime = date ( 'Y-m-d' );
			$createdBy = $this->mid;
			$sqlQuery = "INSERT INTO `ts_db_video_intro` (title,video_src,remarks,ctime,createBy)
			VALUES ('$title','$videoSrc','$remarks','$ctime','$createdBy') ";
			$result = M ()->execute ( $sqlQuery );
			if ($result) {
				$this->success ( '上传成功！' );
			} else {
				$this->error ( '上传失败！' );
			}
		}
	}
	public function del_Lession_File() {
		if ($_GET) {
			$fileID = intval ( $_GET ['id'] );
			$file = M ( "db_lesson_file" );
			$file->where ( "fileID=$fileID" )->delete ();
			$this->success ( '删除成功！' );
		}
	}
	public function del_Lession_video() {
		if ($_GET) {
			$fileID = intval ( $_GET ['id'] );
			$file = M ( "db_lession_video" );
			$file->where ( "videoID=$fileID" )->delete ();
			$this->success ( '删除成功！' );
		}
	}
	public function del_video_intro() {
		if ($_GET) {
			$fileID = intval ( $_GET ['id'] );
			$file = M ( "db_video_intro" );
			$file->where ( "id=$fileID" )->delete ();
			$this->success ( '删除成功！' );
		}
	}
	public function onchangeTop() {
		$info = M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->find ();
		if ($info ['is_top'] == 1) {
			$data ['is_top'] = 0;
			M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->save ( $data );
			echo 0;
		} else {
			$data ['is_top'] = 1;
			M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->save ( $data );
			echo 1;
		}
	}
	public function onchangeCome() {
		$info = M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->find ();
		if ($info ['is_come'] == 1) {
			$data ['is_come'] = 0;
			M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->save ( $data );
			echo 0;
		} else {
			$data ['is_come'] = 1;
			M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->save ( $data );
			echo 1;
		}
	}
	public function onchangeOfficial() {
		$info = M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->find ();
		if ($info ['is_official'] == 1) {
			$data ['is_official'] = 0;
			M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->save ( $data );
			echo 0;
		} else {
			$data ['is_official'] = 1;
			M ( "db_lesson_file" )->where ( "fileID={$_GET['id']}" )->save ( $data );
			echo 1;
		}
	}
}