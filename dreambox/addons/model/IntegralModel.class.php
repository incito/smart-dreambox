<?php
class IntegralModel extends Model {
	protected $tableName = 'db_integral';
	public $error;
	/**
	 * 用户积分信息列表
	 *
	 * @param number $limit        	
	 * @return unknown
	 */
	public function getUserIntegralList($limit = 20) {
		// 权限验证
		if (! CheckPermission ( 'dreambox_admin', 'integral_teachers' )) {
			$this->error = '你没有该浏览权限';
			return false;
		}
		
		$map ['is_deleted'] = '0';
		$map ['is_frozen'] = '0';
		// 搜索条件
		if (isset ( $_POST )) {
			$_POST ['realname'] && $map ['realname'] = array (
					'LIKE',
					'%' . t ( $_POST ['realname'] ) . '%' 
			);
			$_POST ['email'] && $map ['u.email'] = array (
					'LIKE',
					'%' . t ( $_POST ['email'] ) . '%' 
			);
			$_POST ['phone'] && $map ['uv.phone'] = array (
					'LIKE',
					'%' . t ( $_POST ['phone'] ) . '%' 
			);
			$_POST ['school'] && $map ['s.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['school'] ) . '%' 
			);
			$_POST ['is_deleted'] && $map ['is_deleted'] = t ( $_POST ['is_deleted'] );
			$_POST ['is_frozen'] && $_POST ['is_frozen'] != '-1' && $map ['is_frozen'] = t ( $_POST ['is_frozen'] );
		}
		// 查询
		$listData = $this->join ( 'inner join ts_user_verified uv on ts_db_integral.ref_id=uv.uid and uv.type=\'0\' inner join ts_db_school s on uv.sid=s.id and uv.verified=\'1\' inner join ts_user u on uv.uid=u.uid' )->field ( 'ts_db_integral.id,s.name as school,uv.uid,u.email,uv.phone,uv.realname,ts_db_integral.self_integral,ts_db_integral.trans_integral,ts_db_integral.sum_integral,ts_db_integral.is_frozen' )->where ( $map )->order ( 'uv.id asc' )->findPage ( $limit );
		
		// 格式化数据
		foreach ( $listData ['data'] as & $value ) {
			$value ['is_frozen'] = $value ['is_frozen'] ? '已冻结' : '未冻结';
			// 可用积分=现有个人积分+现有累计积分
			$value ['score'] = $value ['self_integral'] + $value ['trans_integral'];
		}
		return $listData;
	}
	
	/**
	 * 学校积分信息列表
	 *
	 * @param number $limit        	
	 * @return unknown
	 */
	public function getSchoolIntegralList($limit = 20) {
		// 权限验证
		if (! CheckPermission ( 'dreambox_admin', 'integral_schools' )) {
			$this->error = '你没有该浏览权限';
			return false;
		}
		// 搜索条件
		if (isset ( $_POST )) {
			$_POST ['school_number'] && $map ['school_number'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_number'] ) . '%' 
			);
			$_POST ['school'] && $map ['s.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['school'] ) . '%' 
			);
			$_POST ['email'] && $map ['email'] = array (
					'LIKE',
					'%' . t ( $_POST ['email'] ) . '%' 
			);
			$_POST ['is_deleted'] && $map ['is_deleted'] = t ( $_POST ['is_deleted'] );
			$_POST ['is_frozen'] && $_POST ['is_frozen'] != '-1' && $map ['is_frozen'] = t ( $_POST ['is_frozen'] );
		} else {
			$map ['is_deleted'] = '0';
			$map ['is_frozen'] = '0';
		}
		// 查询
		$listData = $this->join ( 'as i inner join ts_user_verified uv on i.ref_id=uv.uid and uv.type=\'1\' inner join ts_db_school s on uv.sid=s.id and uv.verified=\'1\' inner join ts_user u on uv.uid=u.uid' )->field ( 'u.email,s.school_number,s.name as school,s.phone as phone,i.self_integral,i.trans_integral,i.sum_integral,i.is_frozen' )->where ( $map )->order ( 's.id asc' )->findPage ( $limit );
		
		// 格式化数据
		foreach ( $listData ['data'] as & $value ) {
			$value ['is_frozen'] = $value ['is_frozen'] ? '已冻结' : '未冻结';
			// 可用积分=现有个人积分+现有累计积分
			$value ['score'] = $value ['self_integral'] + $value ['trans_integral'];
		}
		return $listData;
	}
	
	/**
	 * 获得教师积分流水
	 */
	public function getTeachersIntegralHistory($limit = 20) {
		// 权限验证
		if (! CheckPermission ( 'dreambox_admin', 'integral_teachers' )) {
			$this->error = '你没有该浏览权限';
			return false;
		}
		
		// 搜索条件
		if (isset ( $_POST )) {
			if (! empty ( $_POST ['ctime'] )) {
				if (! empty ( $_POST ['ctime'] [0] ) && ! empty ( $_POST ['ctime'] [1] )) {
					$map ['ih.ctime'] = array (
							'BETWEEN',
							array (
									strtotime ( t ( $_POST ['ctime'] [0] ) ),
									strtotime ( t ( $_POST ['ctime'] [1] ) ) 
							) 
					);
				} else if (! empty ( $_POST ['ctime'] [0] )) {
					$map ['ih.ctime'] = array (
							'GT',
							strtotime ( t ( $_POST ['ctime'] [0] ) ) 
					);
				} else if (! empty ( $_POST ['ctime'] [1] )) {
					$map ['ih.ctime'] = array (
							'LT',
							strtotime ( t ( $_POST ['ctime'] [1] ) ) 
					);
				}
			}
			if (! empty ( $_POST ['integral_type'] )) {
				if ($_POST ['integral_type'] == '1') {
					$map ['increase_integral'] = array (
							'GT',
							'0' 
					);
				} else if ($_POST ['integral_type'] == '2') {
					$map ['increase_integral'] = array (
							'LT',
							'0' 
					);
				}
			}
			$_POST ['name'] && $map ['uv.realname'] = array (
					'LIKE',
					'%' . t ( $_POST ['name'] ) . '%' 
			);
			$_POST ['email'] && $map ['u.email'] = array (
					'LIKE',
					'%' . t ( $_POST ['email'] ) . '%' 
			);
		}
		// 查询
		$listData = $this->join ( 'inner join ts_db_integral_history ih on ts_db_integral.id=ih.integral_id  inner join ts_user_verified uv on ts_db_integral.ref_id=uv.uid and uv.type=\'0\' left join ts_user_verified uv1 on ih.operator_id=uv1.uid inner join ts_user u on ts_db_integral.ref_id=u.uid left join ts_db_credit_setting cs on ih.type=cs.id' )->field ( 'ih.id,uv.realname as name,u.email,ifnull(uv1.realname,(select uname from ts_user where uid=ih.operator_id)) as operator,ih.old_integral,ih.increase_integral,ih.new_integral,cs.title as type,ih.comment,ih.ctime' )->where ( $map )->order ( 'ih.id desc' )->findPage ( $limit );
		
		// 格式化数据
		foreach ( $listData ['data'] as & $value ) {
			$value ['ctime'] = gmdate ( 'Y-m-d H:i:s', $value ['ctime'] + 3600 * 8 );
		}
		return $listData;
	}
	
	/**
	 * 获得学校积分流水
	 */
	public function getSchoolIntegralHistory($limit = 20) {
		// 权限验证
		if (! CheckPermission ( 'dreambox_admin', 'integral_schools' )) {
			$this->error = '你没有该浏览权限';
			return false;
		}
		
		// 搜索条件
		if (isset ( $_POST )) {
			if (! empty ( $_POST ['ctime'] )) {
				if (! empty ( $_POST ['ctime'] [0] ) && ! empty ( $_POST ['ctime'] [1] )) {
					$map ['ih.ctime'] = array (
							'BETWEEN',
							array (
									strtotime ( t ( $_POST ['ctime'] [0] ) ),
									strtotime ( t ( $_POST ['ctime'] [1] ) ) 
							) 
					);
				} else if (! empty ( $_POST ['ctime'] [0] )) {
					$map ['ih.ctime'] = array (
							'GT',
							strtotime ( t ( $_POST ['ctime'] [0] ) ) 
					);
				} else if (! empty ( $_POST ['ctime'] [1] )) {
					$map ['ih.ctime'] = array (
							'LT',
							strtotime ( t ( $_POST ['ctime'] [1] ) ) 
					);
				}
				$_POST ['school'] && $map ['s.name'] = array (
						'LIKE',
						'%' . t ( $_POST ['school'] ) . '%' 
				);
			}
			if (! empty ( $_POST ['integral_type'] )) {
				if ($_POST ['integral_type'] == '1') {
					$map ['increase_integral'] = array (
							'GT',
							'0' 
					);
				} else if ($_POST ['integral_type'] == '2') {
					$map ['increase_integral'] = array (
							'LT',
							'0' 
					);
				}
			}
			$_POST ['school_number'] && $map ['school_number'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_number'] ) . '%' 
			);
		}
		// 查询
		$listData = $this->join ( 'as i inner join ts_db_integral_history ih on i.id=ih.integral_id inner join ts_user_verified uv1 on i.ref_id=uv1.uid and uv1.type=\'1\' left join ts_db_school s on uv1.sid=s.id left join ts_user u on uv1.uid=u.uid left join ts_user_verified uv2  on ih.operator_id=uv2.uid left join ts_db_credit_setting cs on ih.type=cs.id' )->field ( 'ih.id,s.name as school,u.email,ifnull(uv2.realname,(select uname from ts_user where uid=ih.operator_id)) as operator,ih.old_integral,ih.increase_integral,ih.new_integral,cs.title as type,ih.comment,ih.ctime' )->where ( $map )->order ( 'ih.id desc' )->findPage ( $limit );
		
		// 格式化数据
		foreach ( $listData ['data'] as & $value ) {
			$value ['ctime'] = gmdate ( 'Y-m-d H:i:s', $value ['ctime'] + 3600 * 8 );
		}
		return $listData;
	}
	
	/**
	 * 变动积分
	 *
	 * @param array $data
	 *        	传入的数据，必要的数据有：<p><li>id 积分变动对象的id。针对不同对象，可能是用户id，也可能是学校id。<li>type 积分变动类型。如签到时该值为'sign'，所有可选值请参考表ts_db_credit_setting中的类型为积分的记录。</p>
	 *        	其他可选数据：<p><li>comment 变动原因</p>
	 * @return boolean Ambigous Ambigous, boolen>
	 */
	public function updateIntegral(array $data = array()) {
		if ($this->checkData ( $data )) {
			return false;
		}
		$res = false;
		switch ($data ['type']) {
			case 'now_sign' :
			case 'sign' :
				$res = $this->doSign ( $data, $data ['type']);
				break;
			case 'trans' :
				break;
		}
		return $res;
	}
	
	/**
	 * 可用积分
	 *
	 * @param unknown $condition        	
	 */
	public function getAvailableIntegral($id) {
		$condition ['ref_id'] = $id;
		$integral = $this->field ( 'self_integral,trans_integral' )->where ( $condition )->find ();
		return $integral ['self_integral'] + $integral ['trans_integral'];
	}
	
	/**
	 * 累计积分
	 *
	 * @param unknown $condition        	
	 */
	public function getSumIntegral($id) {
		$condition ['ref_id'] = $id;
		$value = $this->where ( $condition )->getField ( 'sum_integral' );
		if (empty ( $value )) {
			return 0;
		}
		return $value;
	}
	
	/**
	 * 查询积分日志
	 */
	public function getIntegralHistory($uid) {
		if (empty ( $uid )) {
			return false;
		}
		return $this->join ( 'as i ts_db_integral_history ih on ih.integral_id=i.id' )->where ( 'i.uid=\'' . $uid . '\'' )->findPage ();
	}
	/**
	 * 积分导入
	 *
	 * @param unknown $data        	
	 * @return boolean
	 */
	public function importHistory($data) {
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		
		$PHPExcel = Excel::readForUpload ( $data, $_SESSION ['mid'] );
		
		if (! $PHPExcel) {
			$this->error = '文件格式不正确，导入失败';
			return false;
		}
		$sheet = $PHPExcel->getSheet ( 0 );
		$rows = $sheet->getRowIterator ( 2 );
		$checkData = $this->checkRowData ( $rows );
		// 如果返回非数组，校验通过，可以导入。
		if (! is_array ( $checkData )) {
			// 指针复位
			$rows->rewind ();
			$credit = model ( 'Cache' )->get ( 'credit_setting' );
			$userM = M ( 'User' );
			$this->startTrans ();
			try {
				foreach ( $rows as $row ) {
					$cells = $row->getCellIterator ();
					$email = trim ( $cells->current ()->getValue () );
					$cells->next ();
					$score = trim ( $cells->current ()->getValue () );
					$cells->next ();
					$type = trim ( $cells->current ()->getValue () );
					$cells->next ();
					$comment = trim ( $cells->current ()->getValue () );
					$typeId = array_search ( $type, $credit );
					$hisData ['ref_id'] = $userM->where ( 'email=\'' . $email . '\'' )->getField ( 'uid' );
					$hisData ['increase_integral'] = $score;
					$hisData ['type'] = $typeId;
					$hisData ['operator_id'] = intval ( $_SESSION ['mid'] );
					$hisData ['comment'] = $comment;
					$hisData ['ctime'] = time ();
					$hisData ['mtime'] = $hisData ['ctime'];
					try {
						if (! $this->saveIntegral ( $hisData, $type == '转赠' )) {
							$this->error = array (
									array (
											'email' => $email,
											'info' => '该帐户积分导入时出现异常' 
									) 
							);
							$this->rollback ();
							return false;
						}
					} catch ( Exception $e ) {
						$this->error = array (
								array (
										'email' => $email,
										'info' => '该帐户积分导入时出现异常' 
								) 
						);
						$this->rollback ();
						return false;
					}
				}
				$this->commit ();
				return true;
			}			// 捕获异常
			catch ( Exception $e ) {
				$this->rollback ();
				return false;
			}
			// 校验失败
		} else {
			$this->error = $checkData;
			return false;
		}
	}
	/**
	 * 获得指定用户的积分排名信息
	 * 
	 * @param unknown $uid        	
	 * @param number $type        	
	 * @return boolean
	 */
	public function getIntegralRankByUid($uid, $type = 0) {
		if (empty ( $uid )) {
			return false;
		}
		return $this->field ( '(self_integral+trans_integral) as integral,sum_integral,(select count(1) from ts_db_integral i left join ts_user_verified uv on i.ref_id=uv.uid where uv.type=' . $type . ' and i.self_integral+i.trans_integral<=integral) as less,(select count(1) from ts_db_integral i left join ts_user_verified uv on i.ref_id=uv.uid where uv.type=' . $type . ' and i.self_integral+i.trans_integral>integral) as over,(select count(1) from ts_db_integral i left join ts_user_verified uv on i.ref_id=uv.uid where uv.type=' . $type . ') as count' )->where ( 'ref_id=' . $uid )->find ();
	}
	/**
	 * 校验Excel数据
	 *
	 * @param unknown $rows        	
	 */
	private function checkRowData($rows) {
		$msg = array ();
		// 查询积分类型缓存数据
		$credit = model ( 'Cache' )->get ( 'credit_setting' );
		if (! $credit) {
			$credit = M ( 'CreditSetting' )->getField ( 'id,title' );
			model ( 'Cache' )->set ( 'credit_setting', $credit );
		}
		foreach ( $rows as $row ) {
			$cells = $row->getCellIterator ();
			$email = $cells->current ()->getValue ();
			$cells->next ();
			$score = $cells->current ()->getValue ();
			$cells->next ();
			$type = $cells->current ()->getValue ();
			$cells->next ();
			$comment = $cells->current ()->getValue ();
			// 校验email
			$findEmail = $this->join ( 'as i inner join ts_user u on ref_id=u.uid' )->where ( 'u.email=\'' . $email . '\'' )->field ( 'i.self_integral,i.trans_integral' )->find ();
			$info = '';
			if (! $findEmail) {
				$info = 'email:该帐户不存在或非实名用户；';
			}
			// 校验分值
			if (is_numeric ( $score )) {
				$score = doubleval ( $score );
				// 积分必须足够扣除
				if ($score < 0) {
					$nowScore = $findEmail ['self_integral'] + $findEmail ['trans_integral'];
					if ($nowScore + $score < 0) {
						$info .= 'score:积分不够扣除，扣减积分：' . abs ( $score ) . '，现有积分：' . $nowScore . '；';
					}
				}
			} else {
				$info .= 'score:不是数字；';
			}
			// 校验变动类型
			if (! array_search ( $type, $credit )) {
				$info .= 'type:变动类型有误，不存在该类型；';
			}
			// 校验变动原因
			if (strlen ( $comment ) > 500) {
				$info .= 'comment变动原因过长，应该少于500个字符；';
			}
			// 如果有校验失败信息，保存
			if ($info) {
				$msg [] = array (
						'email' => $email,
						'info' => $info 
				);
			}
		}
		// 如果没有错误信息，校验通过，返回true
		if (count ( $msg )) {
			return $msg;
		}
		return true;
	}
	
	/**
	 * 签到
	 *
	 * @param unknown $is_teacher        	
	 * @param unknown $data        	
	 * @return boolean
	 */
	private function doSign($data,$name) {
		$map ['name'] = $name;
		$map ['type_name'] = 'integral';
		// 积分操作type_id,value
		$type=$data['score_set'];
		empty($type) && $type = M ( 'db_credit_setting' )->where ( $map )->field ( 'id,value,rate' )->find ();
		if (empty ( $type ['id'] )) {
			return false;
		}
		$hisData ['ref_id'] = intval ( $data ['id'] );
		$hisData ['increase_integral'] = intval ( $type ['value'] );
		$hisData ['type'] = $type ['id'];
		$hisData ['operator_id'] = intval ( $_SESSION ['mid'] );
		$hisData ['comment'] = $data ['comment'];
		$hisData ['ctime'] = time ();
		$hisData ['mtime'] = $hisData ['ctime'];
		$ret = $this->saveIntegral ( $hisData, false );
		// 成功与否标志
		$flag = $ret;
		// 签到成功后，按比率增加对应学校的积分
		if ($flag && $type ['rate']) {
			$data ['type'] = $type ['id'];
			if (floatval ( $type ['rate'] ) === floatval ( 0 )) {
				return $ret;
			}
			$data ['increase_integral'] = floor ( $type ['value'] * $type ['rate'] * 100 ) / 100;
			$flag = $flag && $this->rateToSchool ( $data );
		}
		return $flag ? $ret : 0;
	}
	/**
	 * 教师获得积分后，相应的学校也要获得积分
	 * 
	 * @param unknown $data        	
	 */
	public function rateToSchool($data = array()) {
		$uvModel = model ( 'UserVerified' );
		// 用户所在学校ID
		$smap ['sid'] = $data['sid']?$data['sid']:$uvModel->where ( 'uid=' . $data ['id'] )->getField ( 'sid' );
		$smap ['type'] = '1';
		$smap ['verified'] = '1';
		$school_uid = $uvModel->where ( $smap )->getField ( 'uid' );
		if ($school_uid && $data ['id'] != $school_uid) {
			$schoolData ['ref_id'] = $school_uid;
			$schoolData ['increase_integral'] = $data ['increase_integral'];
			$schoolData ['type'] = $data ['type'];
			$schoolData ['operator_id'] = '1';
			$schoolData ['comment'] = $data ['comment'];
			$schoolData ['ctime'] = time ();
			$schoolData ['mtime'] = $schoolData ['ctime'];
			$ret = $this->saveIntegral ( $schoolData, false );
		}
		return $ret;
	}
	/**
	 * 新增积分记录并更新积分统计
	 *
	 * @param array $data
	 *        	need:ref_id
	 * @param unknown $is_trans        	
	 * @return Ambigous <boolean, boolen>|boolean
	 */
	public function saveIntegral(array $data, $is_trans) {
		$hisModel = M ( 'db_integral_history' );
		
		// 开启事务
		// $hisModel->startTrans ();
		// 必须加锁，防止并发安全问题。
		// 当前积分信息
		$integral = $this->where ( array (
				'ref_id' => $data ['ref_id'] 
		) )->field ( 'id,self_integral,trans_integral' )->find ();
		if (empty ( $integral ['id'] )) {
			$this->error = '没有积分记录';
			return false;
		}
		
		$data ['integral_id'] = $integral ['id'];
		$data ['old_integral'] = $integral ['self_integral'] + $integral ['trans_integral'];
		$data ['new_integral'] = $data ['old_integral'] + $data ['increase_integral'];
		// 如果转增后新积分为负，操作失败
		if ($is_trans && $data ['new_integral'] < 0) {
			$this->error = '可转赠积分不足';
			return false;
		}
		$res = $hisModel->add ( $data );
		// 扣分，优先扣除转赠积分
		if ($data ['increase_integral'] < 0) {
			// 转赠积分足够扣除
			if ($data ['increase_integral'] + $integral ['trans_integral'] >= 0) {
				$data ['new_integral'] = $data ['old_integral'] + $data ['increase_integral'];
				$res = $res && $this->setInc ( 'trans_integral', 'id=' . $data ['integral_id'], $data ['increase_integral'] );
				// 转赠积分不够扣除，先扣除转赠积分，再扣除自有积分
			} else {
				// 清零转赠积分
				$this->where ( 'id=' . $data ['integral_id'] )->setField ( 'trans_integral', 0 );
				// 扣除转赠积分后还差的积分
				$sy = $data ['increase_integral'] + $integral ['trans_integral'];
				$res = $res && $this->setInc ( 'self_integral', 'id=' . $data ['integral_id'], $sy );
			}
		} else {
			$res = $res && $this->setInc ( $is_trans ? 'trans_integral' : 'self_integral', 'id=' . $data ['integral_id'], $data ['increase_integral'] );
			// 累计积分不包括转赠积分
			if (! $is_trans) {
				$res = $res && $this->setInc ( 'sum_integral', 'id=' . $data ['integral_id'], $data ['increase_integral'] );
			}
		}
		// 如果都执行成功，提交事务
		// if ($res) {
		// return $hisModel->commit ();
		// }
		// 如果失败，回滚事务
		// $hisModel->rollback ();
		return $res ? $data ['increase_integral'] : 0;
		// return false;
	}
	private function checkData($data = array()) {
		return empty ( $data ['type'] ) || empty ( $data ['id'] );
	}
	
	/**
	 * 前台查询积分明细
	 */
	public function integralDetail($map, $pageSize) {
		$list = $this->join ( 'inner join ts_db_integral_history ih on ts_db_integral.id=ih.integral_id left join ts_db_credit_setting cs on cs.id = ih.type' )
// 	            ->field ( 'ih.id,uv.realname as name,u.email,uv1.realname as operator,ih.old_integral,ih.increase_integral,ih.new_integral,cs.title as type,ih.comment,ih.ctime' )
                ->field( 'ih.*, cs.title as type' )->where ( $map )->order ( 'ih.ctime desc' )->findPage ( $pageSize );
		foreach ( $list ['data'] as & $obj ) {
			$obj ['ctime'] = date ( 'Y-m-d H:i:s', $obj ['ctime'] );
			$obj ['type'] = $obj ['type'] == null ? "" : $obj ['type'];
			$obj ['comment'] = $obj ['comment'] == null ? "" : $obj ['comment'];
		}
		return $list;
	}
	
	/**
	 * 积分明细的总条数
	 */
	public function integralDetailCount($map) {
		return $this->join ( 'inner join ts_db_integral_history ih on ts_db_integral.id=ih.integral_id  inner join ts_user_verified uv on ts_db_integral.ref_id=uv.uid and uv.type=\'0\' inner join ts_user_verified uv1 on ih.operator_id=uv1.uid inner join ts_user u on ts_db_integral.ref_id=u.uid left join ts_db_credit_setting cs on ih.type=cs.id' )->where ( $map )->count ();
	}
	
	/**
	 * 积分规则
	 */
	public function integralSetting() {
		return M ( "db_credit_setting" )->findAll ();
	}
	/**
	 * 根据类型获得对应积分配置信息
	 * @param unknown $type
	 */
	public function getIntegralSet($type,$fields='*'){
		return M('db_credit_setting')->field($fields)->where('name=\''.$type.'\'')->find();
	}
}