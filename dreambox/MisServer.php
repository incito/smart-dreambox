<?php
class service {
	private $connect = null;
	private $log = null;
	private $allResult = array ();
	private $oneResult = array ();
	private $timeArr = array (
			"applytime",
			"assetstime",
			"accepttime",
			"completetime",
			"create_time",
			"use_time" 
	);
	
	/**
	 * 同步学校信息表
	 *
	 * @param string $string        	
	 * @return 执行结果
	 */
	public function syncSchoolInfo($string) {
		require_once 'core/OpenSociax/functions.inc.php';
		$this->syncTableData ( "ts_db_school", $string );
		return array (
				'out' => json_encode ( $this->allResult ) 
		);
	}
	/**
	 * 同步课程表
	 *
	 * @param string $string        	
	 * @return 执行结果
	 */
	public function syncCourse($string) {
		$this->syncTableData ( "ts_db_course", $string );
		return array (
				'out' => json_encode ( $this->allResult ) 
		);
	}
	private function syncTableData($tableName, $data) {
		try {
			if (! file_exists ( '/mislog/mis.log' )) {
				mkdir ( 'mislog', 0777, true );
				chmod ( "/mislog/mis.log", 0777 );
			}
			// 打开日志
			$this->log = fopen ( "mislog/mis.log", "ab++" );
		} catch ( Exception $e ) {
		}
		// 写入日志
		$showtime = date ( "Y-m-d H:i:s" );
		$this->writeStr ( "\n\n\nDate = " . $this->getFormatDate () );
		$config = require 'config/config.inc.php';
		// 连接服务器数据库
		$hosts = explode ( ',', $config ['DB_HOST'] );
		$this->connect = mysql_connect ( $hosts [0], $config ['DB_USER'], $config ['DB_PWD'] );
		if (! $this->connect) {
			$this->writeStr ( "\nError = can not connect server." );
			fclose ( $this->log );
			$this->allResult ['result'] = 'Failed';
			$this->allResult ['code'] = 0;
			$this->allResult ['msg'] = "Can not connect mysql.";
			$this->writeStr ( "\n" . json_encode ( $this->allResult ) );
			return;
		}
		mysql_selectdb ( $config ['DB_NAME'], $this->connect );
		// 获取mis系统传过来的json数据
		$json = $data->in;
		$this->writeStr ( "\nData = " . $json );
		// 将json数据转换为数组
		$rows = json_decode ( $json, true );
		$this->writeStr ( "\nRows length = " . count ( $rows ) );
		if (empty ( $rows )) {
			$this->writeStr ( "\nError = The data format is incorrect." );
			$this->allResult ['result'] = 'Failed';
			$this->allResult ['code'] = 0;
			$this->allResult ['msg'] = "The data format is incorrect.";
			$this->writeStr ( "\n" . json_encode ( $this->allResult ) );
			fclose ( $this->log );
			mysql_close ( $this->connect );
			return;
		}
		// 设置编码格式
		mysql_query ( "set names 'utf8'" );
		// 开启事务
		$result = false;
		foreach ( $rows as $row ) {
			$this->writeStr ( "\nForeach start " );
			$this->writeStr ( is_array ( $row ) ? 'array' : 'not array' );
			$sql = "INSERT INTO " . $tableName . " ";
			
			// 如果传入过来的是一整张表
			if (is_array ( $row )) {
				$this->syncOneRow ( $row, $tableName, $sql );
			} else {
				$this->syncOneRow ( $rows, $tableName, $sql );
				break;
			}
		}
		$this->writeStr ( "\n" . json_encode ( $this->allResult ) );
		fclose ( $this->log );
		mysql_close ( $this->connect );
	}
	private function syncOneRow($row, $tableName, $sql) {
		if ($tableName == "ts_db_school") {
			$this->oneResult ['dream_number'] = $row ['dream_number'];
		} else {
			$this->oneResult ['class_number'] = $row ['class_number'];
		}
		$this->oneResult ['result'] = 'Success';
		$this->oneResult ['code'] = 1;
		$this->oneResult ['msg'] = 'OK';
		// 开启事务
		mysql_query ( "BEGIN" );
		$columns = $this->getInitColumns ( $tableName );
		$values = $this->getInitValues ( $tableName, $row );
		$result = $this->executeSql ( $row, $columns, $values, $sql );
		$this->writeStr ( "\nResult = " . $result );
		if (! $result) {
			$this->writeStr ( "\nError sql = " . $sql );
			$this->oneResult ['result'] = 'Failed';
			$this->oneResult ['code'] = 0;
			$this->oneResult ['msg'] = 'Insert into ' . $tableName . ' failed,This data needs to resynchronize.';
		} else {
			// 处理学校账号
			$result = $this->addSchoolAccount ( $tableName, $result, $row );
			// 如果创建学校账号失败
			if (! $result) {
				$this->writeStr ( "\nError add school failed" );
				// 回滚
				mysql_query ( "ROLLBACK" );
			} else {
				// 提交
				mysql_query ( "COMMIT" );
			}
		}
		array_push ( $this->allResult, $this->oneResult );
	}
	private function misSub($string) {
		return substr ( $string, 0, strlen ( $string ) - 1 );
	}
	private function executeSql(&$row, $columns, $values, $sql) {
		// 先处理特殊字符串 比如email
		$this->handleSpecialValue ( $row );
		foreach ( $row as $column => $value ) {
			$columns .= $column . ",";
			$values .= "'" . $this->getIntValue ( $column, $value ) . "'" . ",";
		}
		// 拼装sql语句
		$sql .= $this->misSub ( $columns ) . ") values " . $this->misSub ( $values ) . ")";
		$this->writeStr ( "\nSql = " . $sql );
		$result = mysql_query ( $sql );
		$id = mysql_insert_id ();
		$this->writeStr ( "\nInsert_id = " . $id );
		// 执行sql语句
		return $id;
	}
	private function getFormatDate() {
		return date ( "Y-m-d H:i:s" );
	}
	private function getIntValue($column, $value) {
		if (! empty ( $value )) {
			// 判断是否为time格式
			if (in_array ( $column, $this->timeArr )) {
				return strtotime ( $value );
			}
		}
		return $value;
	}
	private function getInitColumns($tableName) {
		if ($tableName == "ts_db_school") {
			return "(first_letter,uid,";
		} else {
			return "(";
		}
	}
	private function getInitValues($tableName, $row) {
		if ($tableName == "ts_db_school") {
			return "('" . getFirstLetter ( $row ['name'] ) . "','2',";
		} else {
			return "(";
		}
	}
	private function addSchoolAccount($tableName, $sid, $row) {
		$this->writeStr ( "\n----addSchoolAccount----" );
		if ($tableName == "ts_db_school" && $sid > 0) {
			if (trim ( $row ['email'] ) == "") {
				$this->writeStr ( "Email can not be empty" );
				$this->oneResult ['result'] = 'Failed';
				$this->oneResult ['code'] = 0;
				$this->oneResult ['msg'] = 'Email can not be empty';
				return 0;
			}
			// 添加学校信息
			// 注册
			$user_data ['email'] = $row ['email'];
			$user_data ['login'] = $row ['email'];
			$user_data ['login_salt'] = rand ( 11111, 99999 );
			$user_data ['password'] = md5 ( md5 ( '888888' ) . $user_data ['login_salt'] );
			$user_data ['uname'] = $row ['name'];
			$user_data ['search_key'] = $user_data ['uname'];
			$user_data ['province'] = $row ['province'];
			$user_data ['city'] = $row ['city'];
			$user_data ['area'] = $row ['area'];
			$user_data ['ctime'] = time ();
			$user_data ['is_active'] = 1;
			$user_data ['is_init'] = 1;
			$user_data ['reg_ip'] = get_client_ip ();
			$sql = "select count(0) as count from ts_user where email='" . $row ['email'] . "' or uname='" . $user_data ['uname'] . "'";
			$result = mysql_query ( $sql );
			$result = mysql_fetch_assoc ( $result );
			$this->writeStr ( "\nSql = " . $sql );
			$this->writeStr ( "\nCount = " . $result ['count'] );
			if ($result ['count'] == 0) {
				$this->writeStr ( "\n----ts_user----" );
				$uid = $this->insert ( $user_data, 'ts_user' );
				if ($uid) {
					// 添加学校积分
					$integral ['ref_id'] = $uid;
					$intid = $this->insert ( $integral, 'ts_db_integral' );
					if (! $intid) {
						$this->oneResult ['result'] = 'Failed';
						$this->oneResult ['code'] = 0;
						$this->oneResult ['msg'] = 'Create integral failed.';
						return 0;
					}
					$user_verified_data = array (
							'uid' => $uid,
							'sid' => $sid,
							'realname' => $user_data ['uname'],
							'reason' => $row ['name'] . '管理员',
							'phone' => $row ['phone'],
							'verified' => '1',
							'type' => '1' 
					);
					$this->writeStr ( "\n----ts_user_verified----" );
					$vid = $this->insert ( $user_verified_data, 'ts_user_verified' );
					if ($vid) {
						$codes = require 'config/code.inc.php';
						$link_data = array (
								'uid' => $uid,
								'user_group_id' => $codes ['USER_GROUP_DREAM_CENTER'] 
						);
						$this->writeStr ( "\n----ts_user_group_link----" );
						$lid = $this->insert ( $link_data, 'ts_user_group_link' );
						return $lid;
					} else {
						$this->writeStr ( "\nCreate user_verified failed " );
						$this->delete ( 'id', $vid, 'ts_user_verified' );
						$this->oneResult ['result'] = 'Failed';
						$this->oneResult ['code'] = 0;
						$this->oneResult ['msg'] = 'Create user_verified failed.';
						return 0;
					}
				} else {
					$this->writeStr ( "\nCreate user failed " );
					$this->oneResult ['result'] = 'Failed';
					$this->oneResult ['code'] = 0;
					$this->oneResult ['msg'] = 'Create user failed.';
					return 0;
				}
			}
			$this->oneResult ['result'] = 'Failed';
			$this->oneResult ['code'] = 99;
			$this->oneResult ['msg'] = 'Email: ' . $row ['email'] . ' is repeated';
			return 0;
		}
		return 1;
	}
	private function insert($data, $tableName) {
		foreach ( $data as $key => $val ) {
			$values [] = $val;
			$fields [] = $key;
		}
		$sql = 'INSERT INTO ' . $tableName . ' (' . implode ( ',', $fields ) . ') VALUES (\'' . implode ( '\',\'', $values ) . '\')';
		$this->writeStr ( "\nInsert = " . $sql );
		mysql_query ( $sql, $this->connect );
		$id = mysql_insert_id ();
		$this->writeStr ( "\nInsert_id = " . $id );
		return $id;
	}
	private function delete($key, $id, $tableName) {
		$sql = 'delete from ' . $tableName . ' where ' . $key . '=' . $id;
		$this->writeStr ( "\nDelete = " . $sql );
		mysql_query ( $sql, $this->connect );
	}
	private function writeStr($str) {
		try {
			fwrite ( $this->log, $str );
		} catch ( Exception $e ) {
		}
	}
	private function handleSpecialValue(&$row) {
		if ($row ['email']) {
			preg_match_all ( '/\d+/', $row ['email'], $match );
			$row ['email'] = 'dream' . $match [0] [0] . '@adreambox.net';
		}
	}
}
$server = new SoapServer ( 'mis.wsdl', array (
		'soap_version' => SOAP_1_2 
) );
$server->setClass ( "service" );
$server->handle ();

?>