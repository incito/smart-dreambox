<?php
class TermAction extends Action {
	private $termModel;
	/**
	 * 初始化，配置内容标题
	 *
	 * @return void
	 */
	public function _initialize() {
		$this->termModel = M ( 'Term' );
	}
	
	/**
	 * 修改学期
	 */
	public function showTerm() {
		if (! CheckPermission ( 'dreambox_normal', 'modify_term' )) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		$termModel = model ( 'Term' );
		$term = $termModel->getCurrentTerm ( $this->mid );
		if (empty ( $term )) { // 没有学期
			$term ['status'] = '0';
			$term ['stime'] = $termModel->getDefaultStime ();
			$term ['etime'] = $termModel->getDefaultEtime ();
		} else { // 管理员还未确认
			$term ['stime'] = date ( 'Y-m-d', $term ['stime'] );
			$term ['etime'] = date ( 'Y-m-d', $term ['etime'] );
		}
		$this->assign ( 'stime', $term ['stime'] );
		$this->assign ( 'etime', $term ['etime'] );
		$this->assign ( 'term', $term );
		
		// 查询学校信息
		$sid = M ( 'user_verified' )->where ( array (
				'uid' => $this->mid 
		) )->getField ( 'sid' );
		$user = M ()->query ( 'SELECT
				*
			FROM
				ts_user_verified v
			INNER JOIN ts_user_group_link l ON v.uid = l.uid
			WHERE
				v.sid = ' . $sid . ' 
			AND l.user_group_id = ' . C ( 'USER_GROUP_DREAM_ADMIN' ) . ' limit 1' );
		$school = M ( 'db_school' )->where ( 'id=' . $sid )->find ();
		$schoolMaster = "";
		if (! empty ( $school ['remark'] )) {
			$remark = explode ( '|', $school ['remark'] );
			$schoolMaster = $remark [0];
		}
		$users = M ( 'user_verified' )->where ( 'sid=' . $sid . ' AND verified=\'1\' AND type=0' )->select ();
		$this->assign ( 'schoolMaster', $schoolMaster );
		$this->assign ( 'school', $school );
		$this->assign ( 'user', $user [0] );
		$this->assign ( 'users', $users );
		$this->display ( 'termInfo' );
	}
	/**
	 * 操作学期信息
	 */
	public function operateTerm() {
		if (! (CheckPermission ( 'dreambox_normal', 'select_class' ) || CheckPermission ( 'dreambox_normal', 'modify_term' ))) {
			$this->error ( '对不起，您没有权限进行该操作！' );
		}
		// 获取当前学期
		$term = $this->termModel->getCurrentTerm ( $this->mid );
		// 检查时间是否有重叠
		$result = $this->checkTermDate ( $term );
		if (! empty ( $result )) {
			echo $result;
			return;
		}
		// 如果当前学期为空
		if (empty ( $term )) {
			// 创建新学期
			$this->modifyTerm ( null, true );
		} else {
			$this->modifyTerm ( $term ['id'], false );
		}
	}
	/**
	 * 修改学期信息
	 */
	private function modifyTerm($termId, $isNew) {
		// 获取学校id
		$schoolId = M ( 'user_verified' )->where ( 'uid=' . $this->mid )->getField ( 'sid' );
		
		$term = M ( 'db_term' );
		
		$data = array (
				'name' => $this->getTermName (),
				'stime' => strtotime ( $_POST ['stime'] ),
				'etime' => strtotime ( $_POST ['etime'] ),
				'mtime' => time (),
				'school_id' => $schoolId,
				'status' => 0 
		);
		// 判断是否为学校管理员或学校账号
		if (CheckPermission ( 'dreambox_normal', 'modify_term' )) {
			$data ['status'] = intval ( $_POST ['status'] );
			$data ['teacher_num'] = $_POST ['teacher_num'];
			$data ['class_num'] = $_POST ['class_num'];
			$data ['student_num'] = $_POST ['student_num'];
			
			// 处理用户认证表
			$ids = $_POST ['ids'];
			if ($ids != "") {
				$ids = explode ( ',', $ids, '-1' );
				$map ['sid'] = $schoolId;
				$map ['uid'] = array (
						'in',
						$ids 
				);
				$map ['verified'] = '1';
				$ver = M ( 'user_verified' );
				$verData = array (
						'sid' => '0',
						'verified' => '2' 
				);
				$ver->where ( $map )->save ( $verData );
				$notifyModel = model ( 'Notify' );
				foreach ( $ids as $id ) {
					// 发送通过审核通知
					$notifyModel->sendNotify ( $id, 'delete_teacher_permission' );
				}
				// 移出梦想老师用户组
				model ( 'UserGroupLink' )->moveGroup ( $ids, null, C ( 'USER_GROUP_TEACHER' ) );
			}
			// 处理学校表
			$schoolMaster = $_POST ['schoolMaster'];
			// 查询学校信息
			$school = M ( 'db_school' )->where ( 'id=' . $schoolId )->find ();
			$school ['phone'] = $_POST ['phone'];
			if (! empty ( $school ['remark'] )) {
				$pos = strpos ( $school ['remark'], '|' );
				if ($pos) {
					$other = substr ( $school ['remark'], $pos );
					$school ['remark'] = $schoolMaster . $other;
				} else {
					$school ['remark'] = $schoolMaster;
				}
			} else {
				$school ['remark'] = $schoolMaster;
			}
			M ( 'db_school' )->where ( 'id=' . $schoolId )->save ( $school );
			// 处理学校管理员
			$admin = $_POST ['admin'];
			if ($admin != $this->mid) {
				M ( 'DreamCenter' )->doDreamAdmin ( $schoolId, $admin );
			}
		}
		// 增加或者修改学期
		if ($isNew) {
			$termId = $term->add ( $data );
		} else {
			$term->where ( 'id=' . $termId )->save ( $data );
		}
		
		// 先清除以前的数据
		$week = M ( 'db_week' );
		$week->startTrans ();
		try {
			$week->where ( 'term_id=' . $termId )->delete ();
			
			// 增加学期的周表
			// 获取开始时间的周id
			$eweek = floor ( ($data ['etime'] - $data ['stime']) / (60 * 60 * 24 * 7) );
			$result = true;
			for($i = 0; $i < $eweek + 2; $i ++) {
				if ($i >= $eweek) {
					// 获取学期开始时间
					$w = date ( 'w', $data ['stime'] );
					$temp1 = 60 * 60 * 24;
					$temp2 = 7 * $temp1;
					$time = $data ['stime'] + ($i) * $temp2 + (1 - $w) * $temp1;
					// 如果时间已经超出范围
					if ($time > $data ['etime']) {
						break;
					}
				}
				$weekData ['week_num'] = $i + 1;
				$weekData ['term_id'] = $termId;
				$result = $result && $week->add ( $weekData );
			}
			$_SESSION ['NEEDNOTIFYSCHOOLADMIN'] = 'NO';
			if ($result) {
				$week->commit ();
				echo 'dreambox/Course/showCourse';
			} else {
				$week->rollback ();
				echo 'public/Index/index';
			}
		} 		// 捕获异常
		catch ( Exception $e ) {
			$week->rollback ();
			echo 'public/Index/index';
		}
	}
	private function getTermName() {
		$stime = $_POST ['stime'];
		$dateArr = explode ( '-', $stime );
		$month = ( int ) $dateArr [1];
		if ($month >= 2 && $month < 8) {
			return $dateArr [0] . '年春季课表';
		}
		return $dateArr [0] . '年秋季课表';
	}
	private function checkTermDate($term) {
		$stime = strtotime ( $_POST ['stime'] );
		$etime = strtotime ( $_POST ['etime'] );
		// 开始与结束时间之间的间隔需要超过4个月
		if (($etime - $stime) < (3600 * 24 * 30 * 4)) {
			return '当前时间段间隔时间小于4个月,不符合学期要求';
		}
		// 控制时间段
		$dstime = $this->termModel->getDefaultStime ();
		$detime = $this->termModel->getDefaultEtime ();
		if (! $this->checkDateScope ( $stime, $etime, strtotime ( $dstime ), strtotime ( $detime ) )) {
			return '当前时间段不在(' . $dstime . '--' . $detime . ')范围内';
		}
		if (empty ( $term )) {
			$terms = M ( 'db_term' )->query ( 'select * from ts_db_term where school_id=(select sid from ts_user_verified where uid=' . $this->mid . ') order by id desc ' );
		} else {
			$terms = M ( 'db_term' )->where ( 'id <>' . $term ['id'] . ' AND school_id=' . $term ['school_id'] );
		}
		foreach ( $terms as $term1 ) {
			// if (($stime < $term1 ['etime'] && $stime > $term1 ['stime']) || ($etime < $term1 ['etime'] && $etime > $term1 ['stime'])) {
			if ($stime <= $term ['etime']) {
				return '当前时间段(' . $_POST ['stime'] . '--' . $_POST ['etime'] . ')与学期(' . date ( 'Y-m-d', $term1 ['stime'] ) . '~' . date ( 'Y-m-d', $term1 ['etime'] ) . ')重合';
			}
		}
		return;
	}
	private function checkDateScope($stime, $etime, $dstime, $detime) {
		return $stime >= $dstime && $etime <= $detime;
	}
}