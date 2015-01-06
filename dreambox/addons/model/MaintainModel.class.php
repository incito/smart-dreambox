<?php
/**
 * 梦想中心管理
 * @author admin
 *
 */
class MaintainModel extends Model {
	
	/**
	 * 查询教师或者学校信息
	 *
	 * @param number $limit        	
	 * @return unknown
	 */
	public function getSearchData($tableName, $tableFields, $limit = 20) {
		if (! empty ( $_POST )) {
			$_POST ['school_name'] && $map ['s.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_name'] ) . '%' 
			);
			$_POST ['school_number'] && $map ['s.school_number'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_number'] ) . '%' 
			);
			$_POST ['term_name'] && $map ['term.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['term_name'] ) . '%' 
			);
			$_POST ['teacher_name'] && $map ['t.teacher_name'] = array (
					'LIKE',
					'%' . t ( $_POST ['teacher_name'] ) . '%' 
			);
		}
		$joinSql = 'as t LEFT JOIN ts_db_term term ON t.term_id = term.id LEFT JOIN ts_db_school s ON t.sid = s.id LEFT JOIN ts_db_school_nature n ON s.school_type = n.id LEFT JOIN ts_db_school_category c ON c.id = s.cid0 LEFT JOIN hz_area a1 ON s.area = a1.`code` LEFT JOIN hz_area a2 ON s.city = a2.`code` LEFT JOIN hz_area a3 ON s.province = a3.`code` LEFT JOIN ts_area ta1 ON s.province=ta1.area_id LEFT JOIN ts_area ta2 ON s.city=ta2.area_id LEFT JOIN ts_area ta3 ON s.area=ta3.area_id';
		$fields = 'term.`name` AS term_name, s.school_number, s.`name` AS school_name, c.title AS school_type, n.`name` AS school_nature, CASE WHEN a1.area IS NOT NULL THEN a1.area WHEN a2.area IS NOT NULL THEN a2.area WHEN a3.area IS NOT NULL THEN a3.area END AS region, ta1.title as province, ta2.title as city, ta3.title as area, s.location' . $tableFields;
		$list = M ( $tableName )->field ( $fields )->where ( $map )->join ( $joinSql )->findPage ( $limit );
		return $list;
	}
	/**
	 * 获取学期数据
	 *
	 * @param unknown $tableName        	
	 * @param unknown $tableFields        	
	 * @param number $limit        	
	 */
	public function getTermData($tableName, $tableFields, $limit = 20) {
		if (! empty ( $_POST )) {
			$_POST ['term_name'] && $map ['term_name'] = array (
					'LIKE',
					'%' . t ( $_POST ['term_name'] ) . '%' 
			);
			$_POST ['course_name'] && $map ['course_name'] = array (
					'LIKE',
					'%' . t ( $_POST ['course_name'] ) . '%' 
			);
		}
		
		// 查询列表数据
		$list = M ( $tableName )->field ( $tableFields )->where ( $map )->findPage ( $limit );
		return $list;
	}
}

?>