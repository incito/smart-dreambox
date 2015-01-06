<?php
class DreamCenterAction extends Action {
	private $direCity = array (
			'上海' => 310,
			'北京' => 110,
			'天津' => 120,
			'重庆' => 500,
			'海南' => 460,
			'湖北' => 420,
			'新疆' => 650 
	);
	private $code = array (
			'460' => 469,
			'420' => 429,
			'650' => 659 
	);
	
	/**
	 * 梦想中心首页
	 */
	public function index() {
		$start = $_REQUEST ['start'] == null ? 0 : $_REQUEST ['start'];
		
		// 统计数量
		$countSql = 'SELECT
							count(1) AS count,
							SUM(t.teacher_num) AS tsum,
							SUM(t.student_num) AS ssum
						FROM
							ts_db_school AS s
						LEFT JOIN (
							SELECT
								a.school_id,
								a.teacher_num,
								a.student_num
							FROM
								ts_db_term a
							WHERE
								id = (
									SELECT
										max(id)
									FROM
										ts_db_term
									WHERE
										school_id = a.school_id
								)
						) AS t ON s.id = t.school_id';
		$sql = 'SELECT
					s.id,
					s.`name`,
					s.sponsors,
					v.uid
				FROM
					ts_db_school AS s
				LEFT JOIN ts_user_verified AS v ON s.id = v.sid
				WHERE
					v.type = 1
				ORDER BY
					s.first_letter,s.name DESC
					LIMIT ' . $start . ',10';
		
		$schoolModel = M ( 'db_school' );
		
		// 统计教师数量等信息
		$countInfo = $schoolModel->query ( $countSql );
		$otherConfig = model ( 'Xdata' )->get ( 'admin_Config:otherConfig' );
		$students = intval ( $otherConfig ['dreambox_center_student'] );
		$countInfo [0] ['ssum'] = $countInfo [0] ['count'] * $students;
		$this->assign ( 'countInfo', $countInfo [0] );
		// 查询学校信息
		$schoolInfo = $schoolModel->query ( $sql );
		$this->assign ( 'schoolInfo', $schoolInfo );
		$this->assign ( 'tab', 'dreamcenter' );
		$this->display ( 'index' );
	}
	/**
	 * 地图过滤
	 */
	public function filterArea() {
		$province = $_REQUEST ['province'];
		if (empty ( $province )) {
			$province = '全国';
		}
		$key = $_REQUEST ['key'];
		if ($key) {
			$searchCondition = " AND (s.name LIKE '%" . $key . "%' OR s.sponsors LIKE '%" . $key . "%') ";
		} else {
			$searchCondition = '';
		}
		$start = $_REQUEST ['start'] == null ? 0 : $_REQUEST ['start'];
		
		// 如果是查询全国信息
		if ($province == '全国') {
			$searchCondition = " WHERE (s.name LIKE '%" . $key . "%' OR s.sponsors LIKE '%" . $key . "%') ";
			// 统计数量
			$countSql = 'SELECT 
							count(1) AS count,
							SUM(t.teacher_num) AS tsum,
							SUM(t.student_num) AS ssum
						FROM
							ts_db_school AS s
						LEFT JOIN (
							SELECT
								a.school_id,
								a.teacher_num,
								a.student_num
							FROM
								ts_db_term a
							WHERE
								id = (
									SELECT
										max(id)
									FROM
										ts_db_term
									WHERE
										school_id = a.school_id
								)
						) AS t ON s.id = t.school_id' . $searchCondition;
			$sql = 'SELECT
						s.id,
						s.`name`,
						s.sponsors,
						v.uid
					FROM
						ts_db_school AS s
					LEFT JOIN ts_user_verified AS v ON s.id = v.sid
					' . $searchCondition . ' AND v.type = 1 ORDER BY
						s.first_letter,s.name DESC
					LIMIT ' . $start . ',10';
		} else {
			$province = "'%" . $_REQUEST ['province'] . "%'";
			
			$areaModel = M ( 'area' );
			$provId = $areaModel->where ( "pid=0 AND title like " . $province )->getField ( 'area_id' );
			// 如果省份不存在
			if (! $provId) {
				$this->ajaxReturn ( null, '未查询到该省份信息', 0 );
			}
			$city = $_REQUEST ['city'];
			if ($city) {
				$cityCondition = $this->getCityCondition ( $areaModel, $provId );
				
				// 根据城市统计
				$countSql = 'SELECT
							count(1) AS count,
							SUM(t.teacher_num) AS tsum,
							SUM(t.student_num) AS ssum
						FROM
							ts_db_school AS s
						LEFT JOIN (
							SELECT
								a.school_id,
								a.teacher_num,
								a.student_num
							FROM
								ts_db_term a
							WHERE
								id = (
									SELECT
										max(id)
									FROM
										ts_db_term
									WHERE
										school_id = a.school_id
								)
						) AS t ON s.id = t.school_id WHERE s.province=' . $provId . $cityCondition . $searchCondition;
				$sql = 'SELECT
						s.id,
						s.`name`,
						s.sponsors,
						v.uid
					FROM
						ts_db_school AS s
					LEFT JOIN ts_user_verified AS v ON s.id = v.sid
					WHERE s.province=' . $provId . $cityCondition . $searchCondition . ' AND v.type=1 ORDER BY
						s.first_letter,s.name DESC
					LIMIT ' . $start . ',10';
			} else {
				// 根据省份统计
				$countSql = 'SELECT
							count(1) AS count,
							SUM(t.teacher_num) AS tsum,
							SUM(t.student_num) AS ssum
						FROM
							ts_db_school AS s
						LEFT JOIN (
							SELECT
								a.school_id,
								a.teacher_num,
								a.student_num
							FROM
								ts_db_term a
							WHERE
								id = (
									SELECT
										max(id)
									FROM
										ts_db_term
									WHERE
										school_id = a.school_id
								)
						) AS t ON s.id = t.school_id WHERE s.province=' . $provId . $searchCondition;
				$sql = 'SELECT
						s.id,
						s.`name`,
						s.sponsors,
						v.uid
					FROM
						ts_db_school AS s
					LEFT JOIN ts_user_verified AS v ON s.id = v.sid
					WHERE s.province=' . $provId . $searchCondition . ' AND v.type=1 ORDER BY
						s.first_letter,s.name DESC
					LIMIT ' . $start . ',10';
			}
		}
		$schoolModel = M ( 'db_school' );
		// 统计教师数量等信息
		$countInfo = $schoolModel->query ( $countSql );
		$otherConfig = model ( 'Xdata' )->get ( 'admin_Config:otherConfig' );
		$students = intval ( $otherConfig ['dreambox_center_student'] );
		$countInfo [0] ['ssum'] = $countInfo [0] ['count'] * $students;
		// 查询学校信息
		$schoolInfo = $schoolModel->query ( $sql );
		$data = array (
				'countInfo' => $countInfo,
				'schoolInfo' => $schoolInfo 
		);
		$this->ajaxReturn ( $data, '查询成功', 1 );
	}
	/**
	 * 获取城市条件
	 */
	public function getCityCondition($areaModel, $provId) {
		$city = "'" . $_REQUEST ['city'] . "%'";
		$province = $_REQUEST ['province'];
		$preCode = $this->direCity [$province];
		// 如果是直辖市
		if ($preCode) {
			// 如果是新疆 海南 湖北的情况
			$preCode = empty ( $this->code [$preCode] ) ? $preCode : $this->code [$preCode];
			$preCode = "'" . $preCode . "%'";
			// 根据直辖市查询到所在地区
			// 排除包含空格的城市信息
			$sql = "SELECT
						area_id
					FROM
						(
							SELECT
								a.area_id,
								REPLACE (a.title, '　', '') AS title
							FROM
								ts_area AS a
							WHERE
								a.area_id like " . $preCode . ") AS t
					WHERE
						t.title LIKE " . $city . " LIMIT 1";
			$result = $areaModel->query ( $sql );
			$cityId = $result [0] ['area_id'];
			$cityConditon = " AND s.area=" . $cityId;
		}
		// 如果不是直辖县级城市
		if (! $cityId) {
			$cityId = $areaModel->where ( "pid=" . $provId . " AND title like " . $city )->getField ( 'area_id' );
			$cityConditon = " AND s.city=" . $cityId;
		}
		// 如果市不存在
		if (! $cityId) {
			$this->ajaxReturn ( null, '未查询到该城市信息', 0 );
		}
		
		return $cityConditon;
	}
}