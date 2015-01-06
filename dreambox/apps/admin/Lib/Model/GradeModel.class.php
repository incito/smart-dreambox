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

// 班级
class GradeModel extends Model {
	protected $tableName = "db_grade";
	protected $_validate	=	array(
		array('name','require','班级名称必须',0,'',3),
		array('name','','班级名称已存在',0,'unique',1), 
		);

	protected $_auto = array(
		array('create_time','time',1,'function'),
		);

}
?>