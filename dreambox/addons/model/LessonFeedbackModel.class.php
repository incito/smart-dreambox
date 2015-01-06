<?php

/**
 * 梦想老师课程签到模型
 * @author zjj
 * 
 */
class LessonFeedbackModel extends Model {
	// 签到后的签到记录ID
	private $feedId = '';
	/**
	 *
	 * @var 数据库表名
	 */
	protected $tableName = 'db_lesson_feedback';
	
	/**
	 * 查询可签到课程
	 */
	public function show($user_id) {
		$map ["user_id"] = $user_id;
		$map ["feedback_status"] = 0;
		$data = M ( "db_select_course" )->where ( $map )->order ( "week_num,week_day asc" )->select ();
		return $data;
	}
	
	/**
	 * 查询签到的流水
	 */
	public function search($limit = 20, $map = array(), $order = "create_time desc") {
		if (isset ( $_POST )) {
			$_POST ['school_name'] && $map ['s.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_name'] ) . '%' 
			);
			$_POST ['realname'] && $map ['realname'] = array (
					'LIKE',
					'%' . t ( $_POST ['realname'] ) . '%' 
			);
			if ($_POST ['term_id']) {
				$condition ['name'] = array (
						'LIKE',
						'%' . t ( $_POST ['term_id'] ) . '%' 
				);
				$term_array = M ( 'db_term' )->where ( $condition )->field ( 'id' )->select ();
				// 二维转一维数组
				$term_id = array ();
				for($i = 0; $i < sizeof ( $term_array ); $i ++) {
					$array = $term_array [$i];
					$term_id [$i] = $array ['id'];
				}
				
				$map ['term_id'] = array (
						'in',
						$term_id 
				);
			}
			
			if ($_POST ['course_name']) {
				// $condition ['id'] = $_POST ['course_name'];
				// $course_name = M ( 'db_course' )->where ( $condition )->limit ( 1 )->getField ( 'class_name' );
				$map ['course_name'] = array (
						'LIKE',
						'%' . t ( $_POST ['course_name'] ) . '%' 
				);
			}
			
			// 注册时间判断，ctime为数组格式
			if (! empty ( $_POST ['create_time'] )) {
				if (! empty ( $_POST ['create_time'] [0] ) && ! empty ( $_POST ['create_time'] [1] )) {
					// 时间区间条件
					$map ['create_time'] = array (
							'BETWEEN',
							array (
									"'" . $_POST ['create_time'] [0] . "'",
									"'" . $_POST ['create_time'] [1] . "'" 
							) 
					);
				} else if (! empty ( $_POST ['create_time'] [0] )) {
					// 时间大于条件
					$map ['create_time'] = array (
							'GT',
							$_POST ['create_time'] [0] 
					);
				} elseif (! empty ( $_POST ['create_time'] [1] )) {
					// 时间小于条件
					$map ['create_time'] = array (
							'LT',
							$_POST ['create_time'] [1] 
					);
				}
			}
		}
		
		// 查询数据
		$query = "as f LEFT JOIN ts_user_verified as v ON f.user_id = v.uid AND v.verified = '1' LEFT JOIN ts_db_school as s ON s.id = v.sid LEFT JOIN ts_db_term as t ON f.term_id = t.id";
		$field = "f.id,f.course_name,f.hours_name,f.grade_name,concat(f.class_name,'班') as class_name,f.section_num,
		           f.lesson_time,f.create_time,v.realname,s.name as school_name,t.name as term_id,
		            case f.from when 0 then 'PC' when 1 then '微信' end as client,
		            case f.status when 0 then '待确认' when 1 then '已确认' when 2 then '驳回' end as audit_status"; // ,ih.increase_integral LEFT JOIN ts_db_integral_history as ih ON ih.ref_id = f.id AND ih.type=10
		$list = $this->join ( $query )->field ( $field )->where ( $map )->order ( $order )->findPage ( $limit );
		
		// 数据组装
		$userGroupHash = array ();
		$uids = array ();
		foreach ( $list ['data'] as $k => $v ) {
			$userGroupHash [$v ['uid']] = array ();
			$uids [] = $v ['uid'];
			$list ['data'] [$k] ['user_group'] = &$userGroupHash [$v ['uid']];
		}
		$gmap ['uid'] = array (
				'IN',
				$uids 
		);
		$userGroupLink = D ( 'user_group_link' )->where ( $gmap )->findAll ();
		foreach ( $userGroupLink as $v ) {
			$userGroupHash [$v ['uid']] [] = $v ['user_group_id'];
		}
		
		return $list;
	}
	
	// /**
	// * 签到
	// */
	// public function feedback($uid, $data, $type = 'sign') {
	// $this->startTrans ();
	// try {
	// // 修改选课表
	// $status = M ( "db_select_course" )->where ( 'id=' . $data ['course_id'] )->setField ( 'feedback_status', '1' );
	// if (! $status) {
	// $this->rollback ();
	// return '更改课表状态失败';
	// }
	// // 添加签到记录
	// // $data = $this->create ();
	
	// $s_course = M ( 'db_select_course' )->where ( 'id=' . $data ['course_id'] )->find ();
	// $data ['week_num'] = $s_course ['week_num'];
	// $data ['week_day'] = $s_course ['week_day'];
	
	// $data ['user_id'] = $uid;
	// $data ['create_time'] = date ( "Y-m-d H:i:s", time () );
	// $data ['status'] = 0; // 未确认签到
	// $insId = $this->getLastInsID ();
	// // 积分
	// $integralModel = M ( 'Integral' );
	// $integralSet = $integralModel->getIntegralSet ( $type, 'id,value,rate' );
	// $integral = array (
	// 'id' => $uid,
	// 'type'=>$type,
	// 'score_set'=>$integralSet,
	// 'comment' => $data ['course_name'] . '-' . $data ['hours_name']
	// );
	// // 添加该次签到的积分信息
	// $data ['data'] = serialize ( array (
	// 'integral' => $integral
	// ) );
	// $status = $this->add ( $data );
	// if (! $status) {
	// $this->rollback ();
	// return '添加签到流水失败';
	// }
	// if (! $integralSet ['value']) {
	// $this->rollback ();
	// return '积分失败';
	// }
	// $this->feedId = $insId;
	// $this->commit ();
	// return intval ( $integralSet ['value'] );
	// } // 捕获异常
	// catch ( Exception $e ) {
	// $this->rollback ();
	// return '签到异常';
	// }
	// }
	/**
	 * 签到
	 */
	public function feedback($uid, $data, $type = 'sign') {
		$this->startTrans ();
		try {
			// 修改选课表
			$status = M ( "db_select_course" )->where ( 'id=' . $data ['course_id'] )->setField ( 'feedback_status', '1' );
			if (! $status) {
				$this->rollback ();
				return '更改课表状态失败';
			}
			// 添加签到记录
			// $data = $this->create ();
			
			$s_course = M ( 'db_select_course' )->where ( 'id=' . $data ['course_id'] )->find ();
			$data ['week_num'] = $s_course ['week_num'];
			$data ['week_day'] = $s_course ['week_day'];
			
			$data ['user_id'] = $uid;
			$data ['create_time'] = date ( "Y-m-d H:i:s", time () );
			$status = $this->add ( $data );
			$insId = $this->getLastInsID ();
			if (! $status) {
				$this->rollback ();
				return '添加签到流水失败';
			}
			
			// 积分
			$integralModel = M ( 'Integral' );
			$integral = $integralModel->updateIntegral ( array (
					'id' => $uid,
					'type' => $type,
					'comment' => $data ['course_name'] . '-' . $data ['hours_name'] 
			) );
			if (! $integral) {
				$this->rollback ();
				$msg = $integralModel->error;
				return empty ( $msg ) ? '积分失败' : $msg;
			}
			$this->feedId = $insId;
			$this->commit ();
			return $integral;
		} 		// 捕获异常
		catch ( Exception $e ) {
			$this->rollback ();
			return '签到异常';
		}
	}
	public function getFeedId() {
		return $this->feedId;
	}
	/**
	 * 获得待确认签到列表
	 *
	 * @param unknown $uid        	
	 */
	public function getConfirmList($uid, $param = false) {
		$data = $this->query ( 'SELECT lf.id, lf.week_num,v2.realname, lf.lesson_time, lf.week_day, lf.grade_name, lf.class_name, lf.section_num, lf.course_name, lf.hours_name, lf.status FROM ts_user_verified v1 LEFT JOIN ts_db_term t ON v1.sid = t.school_id INNER JOIN ts_db_lesson_feedback lf ON t.id = lf.term_id inner JOIN ts_user_verified v2 on lf.user_id=v2.uid WHERE v1.uid = ' . $uid . (! $param ? '' : ' and (' . $param . ')') . '  ORDER BY lf.create_time DESC' );
		foreach ( $data as &$v ) {
			$v ['lesson_time'] = '第' . $v ['week_num'] . '周  ' . $v ['lesson_time'];
			$v ['grade_name'] = $v ['grade_name'] . $v ['class_name'] . '班';
		}
		return $data;
	}
	/**
	 * 确认或驳回签到
	 *
	 * @param unknown $id1
	 *        	确认ID
	 * @param unknown $id2
	 *        	驳回ID
	 */
	public function confirm($id1, $id2) {
		$id1_empty = empty ( $id1 );
		$id2_empty = empty ( $id2 );
		if ($id1_empty && $id2_empty) {
			return false;
		}
		$ret = true;
		// 执行签到确认
		if (! $id1_empty) {
			is_array ( $id1 ) && $id1 = implode ( ',', $id1 );
			$sql = 'update __TABLE__ set status=1 where id in(' . $id1 . ') and status=0';
			$ret = $this->execute ( $sql );
			if ($ret) {
				// 添加积分
				$feeds = $this->where ( 'id in (' . $id1 . ')' )->field ( 'data' )->findAll ();
				$integralModel = M ( 'Integral' );
				foreach ( $feeds as $v ) {
					$data = unserialize ( $v ['data'] );
					$map ['id'] = $data ['integral'] ['id'];
					$map ['type'] = $data ['integral'] ['type'];
					$map ['comment'] = $data ['integral'] ['comment'];
					$map ['score_set'] = $data ['integral'] ['score_set'];
					$integralModel->updateIntegral ( $map );
				}
			}
		}
		// 执行签到驳回
		if (! $id2_empty) {
			is_array ( $id2 ) && $id2 = implode ( ',', $id2 );
			$sql = 'update __TABLE__ set status=2 where id in (' . $id2 . ')';
			$ret = $this->execute ( $sql );
			$sql = 'update ts_db_select_course sc inner join ts_db_lesson_feedback lf on sc.id=lf.course_id set feedback_status=2 where lf.id in(' . $id2 . ')';
			$ret && $ret = $this->db->execute ( $sql );
		}
		return $ret;
	}
	public function confirm1($id1, $id2, $uid) {
		$score = 300;
		$id1_empty = empty ( $id1 );
		$id2_empty = empty ( $id2 );
		if ($id1_empty && $id2_empty) {
			return false;
		}
		// 校验签到记录是否都属于该学校下
		$feedIds = $id1 . ($id1_empty || $id2_empty ? '' : ',') . $id2;
		$count = $this->query ( 'SELECT count(lf.id) as c FROM ts_user_verified v1 LEFT JOIN ts_db_term t ON v1.sid = t.school_id INNER JOIN ts_db_lesson_feedback lf ON t.id = lf.term_id WHERE v1.uid = ' . $uid . ' AND lf.id IN (' . $feedIds . ') ORDER BY lf.create_time DESC' );
		$count = $count [0] ['c'];
		// 如果存在不是该学校的签到记录，返回false
		if (substr_count ( $feedIds, ',' ) + 1 != $count) {
			return false;
		}
		$rate = M ()->query ( "select rate from ts_db_credit_setting where id=10" );
		$rate = floatval ( $rate [0] ['rate'] ); // 学校积分转化率
		                                         // 执行签到确认
		if (! $id1_empty) {
			// 被驳回过的签到，用于加积分
			$feeds = $this->where ( 'id in (' . $id1 . ') and status=2' )->field ( 'user_id,course_name,hours_name' )->findAll ();
			// 确认签到
			$sql = 'update __TABLE__ set status=1 where id in(' . $id1 . ')';
			$ret1 = $this->execute ( $sql );
			if ($ret1 && $feeds) {
				$sid = M ( 'UserVerified' )->where ( 'uid=' . $uid )->getField ( 'sid' );
				$integralModel = M ( 'Integral' );
				foreach ( $feeds as $v ) {
					$hisData ['ref_id'] = $v ['user_id'];
					$hisData ['increase_integral'] = $score;
					$hisData ['type'] = 10; // 签到ID
					$hisData ['operator_id'] = $uid;
					$hisData ['comment'] = '签到通过：' . $v ['course_name'] . '-' . $v ['hours_name'];
					$hisData ['ctime'] = time ();
					$hisData ['mtime'] = $hisData ['ctime'];
					$ret1 && $ret1 = $integralModel->saveIntegral ( $hisData, false );
					if ($rate !== floatval ( 0 ) && $ret1) {
						$data ['id'] = $v ['user_id'];
						$data ['type'] = 10;
						$data ['sid'] = $sid;
						$data ['comment'] = $hisData ['comment'];
						$data ['increase_integral'] = floor ( $score * $rate * 100 ) / 100;
						$integralModel->rateToSchool ( $data );
					}
				}
			}
		}
		// 执行签到驳回
		if (! $id2_empty) {
			// 未确认的签到，用于扣减积分
			$feeds = $this->where ( 'id in (' . $id2 . ') and (status=0 or status=1)' )->field ( 'user_id,course_name,hours_name' )->findAll ();
			$sql = 'update __TABLE__ set status=2 where id in (' . $id2 . ')';
			$ret = $this->execute ( $sql );
			if ($ret && $feeds) {
				empty ( $sid ) && $sid = M ( 'UserVerified' )->where ( 'uid=' . $uid )->getField ( 'sid' );
				empty ( $integralModel ) && $integralModel = M ( 'Integral' );
				foreach ( $feeds as $v ) {
					$hisData ['ref_id'] = $v ['user_id'];
					$hisData ['increase_integral'] = - $score;
					$hisData ['type'] = 10; // 签到ID
					$hisData ['operator_id'] = $uid;
					$hisData ['comment'] = '签到被驳回：' . $v ['course_name'] . '-' . $v ['hours_name'];
					$hisData ['ctime'] = time ();
					$hisData ['mtime'] = $hisData ['ctime'];
					$ret && $ret = $integralModel->saveIntegral ( $hisData, false );
					if ($rate !== floatval ( 0 ) && $ret) {
						$data ['id'] = $v ['user_id'];
						$data ['type'] = 10;
						$data ['sid'] = $sid;
						$data ['comment'] = $hisData ['comment'];
						$data ['increase_integral'] = floor ( - $score * $rate * 100 ) / 100;
						$integralModel->rateToSchool ( $data );
					}
				}
			}
		}
		return $ret1 || $ret;
	}
}
?>