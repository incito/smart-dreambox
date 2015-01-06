<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2008 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

// 课时
class ClassHoursModel extends Model {
	protected $tableName = 'db_class_hours';
	protected $_validate = array ();
	protected $_auto = array (
			array (
					'create_time',
					'time',
					1,
					'function' 
			) 
	);
	public function getAllClassHoursByCourseId($courseId, $field = "", $isContainUnvalid = true) {
		// TODO 查询某一课程对应的所有课时；
		$ClassHoursList = $this->where ( "hid={$courseId}" )->field ( "{$field}" )->findAll ();
		
		return $ClassHoursList;
	}
}
?>