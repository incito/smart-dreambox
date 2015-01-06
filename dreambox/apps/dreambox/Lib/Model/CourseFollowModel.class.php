<?php
class CourseFollowModel extends Model {
	public $tableName = 'db_course_follow';
	/**
	 * 添加关注
	 */
	public function addFollow($data) {
		M ( 'db_course_follow' )->add ( $data );
	}
	
	/**
	 * 查询关注
	 */
	public function queryFollow($condition) {
		$querySql = 'SELECT u.uname,f.* FROM ts_user u INNER JOIN ts_db_course_follow f on u.uid=f.uid AND f.uid <> ' . $condition ['user_id'] . ' AND course_id=' . $condition ['course_id'] . ' ORDER BY id DESC LIMIT ' . $condition ['start'] . ',' . $condition ['num'];
		$result = $this->db->query ( $querySql );
		$time = time ();
		$userModel = model ( 'User' );
		$followModel = model ( 'Follow' );
		foreach ( $result as &$follow ) {
			if ($follow ['type'] == 1) { // 课程
				$follow ['action'] = '选择了';
				$follow ['content'] = $this->getCourse ( $follow );
				$follow['result'] = $follow ['action'].' '.$follow ['content'];
			} else if ($follow ['type'] == 2) { // 博客
				$follow ['action'] = '发布了';
				$blog = $this->getBlog ( $follow );
				$follow ['blog_url'] = $blog ['blog_url'];
				$follow ['blog_id'] = $blog ['id'];
				$follow['title'] = $blog['title'];
				$follow['result'] = $follow ['action'].' '.$follow ['title'];
			}
			// 查询用户信息
			$userInfo = $userModel->getUserInfo ( $follow ['uid'] );
			$follow ['user_small'] = $userInfo ['avatar_small'];
			$follow ['sex'] = $userInfo ['sex'] == 1 ? '男' : '女';
			// 计算时间
			$follow ['time'] = $this->getTimeStr ( $follow, $time );
			// 查询是否已经关注
			$status = $followModel->getFollowState ( $condition ['user_id'], $follow ['uid'] );
			$follow ['status'] = $status ['following'];
		}
		return $result;
	}

	/**
	 * 获取选课内容或者写课程感悟
	 */
	private function getCourse($follow) {
		$classNames = $this->db->query ( 'SELECT class_name FROM ts_db_course where id=' . $follow ["course_id"] );
		return $classNames [0] ['class_name'];
	}
	
	/**
	 * 获取选课内容或者写课程感悟
	 */
	private function getBlog($follow) {
		$blog = $this->db->query ( 'SELECT * FROM ts_blog where id=' . $follow ['blog_id'] );
		return $blog [0];
	}
	private function getTimeStr($follow, $time) {
		// 获取时间差
		$timeInt = $time - $follow ['ctime'];
		// 获取凌晨时间
		$beforeDawnTime = strtotime ( date ( 'Y-m-d', $time ) );
		$dayTime = 60 * 60 * 24;
		$timeStr = '';
		// 如果创建时间大于凌晨时间
		if ($follow ['ctime'] >= $beforeDawnTime) {
			$timeInt = $follow ['ctime'] - $beforeDawnTime;
			$hourTime = 60 * 60;
			if ($timeInt >= $hourTime) {
				$timeStr = ceil ( $timeInt / $hourTime ) . '小时前';
			} else if ($timeInt >= 60) {
				$timeStr = ceil ( $timeInt / 60 ) . '分钟前';
			} else if ($timeInt >= 0) {
				$timeStr = $timeInt . '秒钟前';
			}
		} else {
			$timeStr = ceil ( $timeInt / $dayTime ) . '天前';
		}
		return $timeStr;
	}
}