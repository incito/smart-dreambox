<?php
class TermModel extends Model {
	protected $tableName = 'db_term';
	/**
	 *
	 * @var 数据模型
	 */
	private $model = null;
	public function _initialize() {
		$this->model = D ( "db_term" );
	}
	
	/**
	 * 获取当前学期
	 */
	public function getCurrentTerm($userId) {
		// 通过子查询获取
		$result = $this->db->query ( 'select * from ts_db_term where school_id=(select sid from ts_user_verified where uid=' . $userId . ') order by id desc limit 1' );
		$term = $result [0];
		// 获取结束时间,并判断当前时间是否为范围内
		if (strtotime ( date ( 'Y-m-d' ) ) <= $term ['etime']) {
			return $term;
		}
	}
	/**
	 * 创建学期
	 *
	 * @param 数组 $termData        	
	 */
	public function createTerm($termData) {
		$this->model->data ( $termData )->add ();
	}
	/**
	 * 更新学期
	 *
	 * @param 数组 $termData        	
	 */
	public function updateTerm($termData) {
		$this->model->data ( $termData )->save ();
	}
	
	/**
	 * 获取默认开始时间
	 */
	public function getDefaultStime() {
		$m = intval ( date ( 'm' ) );
		if ($m < 2) {
			return (date ( 'Y' ) - 1) . '-08-01';
		} else if ($m < 8) {
			return date ( 'Y' ) . '-02-01';
		} else {
			return date ( 'Y' ) . '-08-01';
		}
	}
	
	/**
	 * 获取默认结束时间
	 */
	public function getDefaultEtime() {
		$m = intval ( date ( 'm' ) );
		if ($m < 2) {
			return date ( 'Y' ) . '-01-31';
		} else if ($m < 8) {
			return date ( 'Y' ) . '-07-31';
		} else {
			return (date ( 'Y' ) + 1) . '-01-31';
		}
	}
}