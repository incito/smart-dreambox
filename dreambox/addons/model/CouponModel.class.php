<?php
/**
 * 红包发放模型
 * @author yingliao
 */
class CouponModel extends Model {
	protected $fields = array (
			'id',
			'userid',
			'c_old',
			'c_now',
			'change_val',
			'change_type',
			'feedback_ids',
			'ctime' ,
			'comment'
	);
	/**
	 * 签到后发放红包
	 *
	 * @param unknown $uid        	
	 * @param unknown $signIds        	
	 * @return status 是否成功; amount 红包金额
	 */
	public function sendAfterSign($uid, $signIds) {
		if (empty ( $uid ) || empty ( $signIds )) {
			$this->error = "参数错误";
			return array (
					'status' => 0,
					'amount' => 0 
			);
		}
		! is_array ( $signIds ) && $signIds = explode ( ',', $signIds );
		// 签到数量
		$count = count ( $signIds );
		$num = $this->getLuckyNum ( $count );
		// 未中奖直接返回
		if ($num <= 0) {
			return array (
					'status' => 1,
					'amount' => 0 
			);
		}
		// 开启事务
		$this->db->startTrans ();
		try {
			// queryFromMaster方法调用主数据库，从而锁定主数据库中的该记录并阻塞其他并发。只适用于单一主数据库的情况。
			$conf = $this->db->queryFromMaster ( 'select switch,total,balance,cost,probability,c_max,c_min,stime,etime,last_time,unit_val from ts_db_coupon_set where id=1 limit 1 for update' );
			$conf = $conf [0];
			if ($this->validCoupon ( $conf )) {
				$amount = $this->getRandomAmount ( $conf, $num );
				// 新增/修改积分
				$userCoupon = $this->db->query ( 'select id,c_balance from ts_db_coupon where userid=' . $uid . ' limit 1' );
				// 时间戳
				$now = time ();
				if (empty ( $userCoupon )) {
					$couponSql = "insert into ts_db_coupon (userid,c_sum,c_balance,status,username,ctime) values($uid,$amount,$amount,1,(select realname from ts_user_verified where uid= $uid ), $now )";
				} else {
					$couponSql = "update ts_db_coupon set c_sum=c_sum+$amount, c_balance=c_balance+$amount where id={$userCoupon[0][id]}";
				}
				if ($this->db->execute ( $couponSql )) {
					// 记录积分流水
					$c_old = intval ( $userCoupon [0] ['c_balance'] );
					$c_now = $c_old + $amount;
					$signIds = implode ( ",", $signIds );
					if ($this->db->execute ( "insert into ts_db_coupon_log(userid,c_old,c_now,change_val,change_type,feedback_ids,ctime,comment)values($uid,$c_old,$c_now,$amount,1,'$signIds',$now,'签到抽奖')" )) {
						// 修改积分配置中帐户余额
						if ($this->db->execute ( "update ts_db_coupon_set set balance=balance-$amount,cost=cost+$amount where id=1" )) {
							$this->commit ();
							// 清空配置缓存
							M ( 'Cache' )->rm ( 'coupon_set' );
							return array (
									'status' => 1,
									'amount' => $amount 
							);
						} else {
							$this->db->rollback ();
							$this->error = "更新红包配置信息时发生异常";
							return array (
									'status' => 0,
									'amount' => 0 
							);
						}
					} else {
						$this->db->rollback ();
						$this->error = "记录红包流水时发生异常";
						return array (
								'status' => 0,
								'amount' => 0 
						);
					}
				} else {
					$this->db->rollback ();
					$this->error = "更新红包时发生异常";
					return array (
							'status' => 0,
							'amount' => 0 
					);
				}
			} else {
				$this->db->rollback ();
				return array (
						'status' => 0,
						'amount' => 0 
				);
			}
		} catch ( Exception $e ) {
			$this->db->rollback ();
			return array (
					'status' => 0,
					'amount' => 0 
			);
		}
	}
	public function getListByUid($uid, $fields = '*', $pageNum = 1, $pageSize = 10) {
		$pageNum <= 0 && $pageNum = 1;
		$count = $this->db->query ( 'select count(id) c from ts_db_coupon_log where userid=' . $uid );
		$count=$count[0]['c'];
		if (($pageNum - 1) * $pageSize >= $count) {
			return false;
		}
		// 处理字段
		$fields = $this->db->parseField ( $fields );
		$fields == '*' && $fields = implode ( ',', $this->fields );
		$ret = $this->db->query ( 'select ' . $fields . ' from ts_db_coupon_log where userid=' . $uid . ' order by ctime desc limit ' . ($pageNum - 1) * $pageSize . ',' . $pageSize );
		return array('pageNum'=>$pageNum,'pageCount'=>ceil($count/$pageSize),'pageSize'=>$pageSize,'count'=>$count,'data'=>$ret);
	}
	public function getError() {
		return $this->error;
	}
	/**
	 * 检测红包是否可用
	 *
	 * @param
	 *        	conf 红包配置
	 *        	count 红包发放数量
	 * @return boolean
	 */
	public function validCoupon($conf, $count = 1) {
		if (! conf) {
			$conf = $this->_getSet ();
		}
		if (empty ( $conf )) {
			$this->error = "红包发放规则不存在";
			return false;
		}
		// 校验红包开关
		if ($conf ['switch'] == 0) {
			$this->error = "红包功能未开启";
			return false;
		}
		// 校验红包有效期
		$now = time ();
		if ($now < $conf ['stime'] || $now > $conf ['etime']) {
			$this->error = "不在活动时间内";
			return false;
		}
		// 校验余额
		if ($conf ['balance'] < $count * $conf ['c_max']) {
			$this->error = "帐户余额不足";
			return false;
		}
		return true;
	}
	/**
	 * 获得随机红包金额
	 *
	 * @param unknown $conf
	 *        	红包配置
	 * @param number $count
	 *        	红包个数
	 * @return Ambigous <number, unknown>
	 */
	private function getRandomAmount($conf, $count = 1) {
		$total = 0;
		$ucount = ceil ( ($conf ['c_max'] - $conf ['c_min']) / $conf ['unit_val'] );
		$balance = intval ( $conf ['balance'] );
		for($i = 0; $i < $count; $i ++) {
			// 粒度跨越数
			// 红包面值
			$amount = $conf ['c_min'] + mt_rand ( 0, $ucount ) * $conf ['unit_val'];
			$amount > $conf ['c_max'] && $amount = $conf ['c_max'];
			$balance -= $amount;
			// 余额不足停止发红包
			if ($balance <= 0) {
				break;
			}
			$total += $amount;
		}
		return $total;
	}
	/**
	 * 抽奖，返回中奖次数
	 *
	 * @param
	 *        	count 抽奖次数
	 * @return boolean
	 */
	private function getLuckyNum($count = 1) {
		$conf = $this->_getSet ();
		// 校验红包是否可用
		if (! $this->validCoupon ( $conf, 1 )) {
			return 0;
		}
		$probability = $conf ['probability'];
		$balance = intval ( $conf ['balance'] );
		$num = 0;
		for($i = 0; $i < $count; $i ++) {
			// 若余额不足，停止抽奖
			if ($balance <= 0) {
				break;
			}
			// 是否中奖
			if (mt_rand ( 1, 100000000 ) <= $probability * 100000000) {
				$num ++;
				// 中奖一次，红包帐户余额扣减一次最大中奖值，用于作为是否可以继续抽奖的标志
				$balance -= $conf ['c_max'];
			}
		}
		return $num;
	}
	private function _getSet() {
		$cache = M ( 'Cache' );
		$key = 'coupon_set';
		$conf = $cache->get ( $key );
		if (empty ( $conf )) {
			$conf = $this->db->queryFromMaster ( 'select switch,total,balance,cost,probability,c_max,c_min,stime,etime,last_time,unit_val from ts_db_coupon_set where id=1 limit 1 ' );
			$conf=$conf [0];
			if($conf){
				$cache->set ( $key, $conf );
			}
		}
		return $conf;
	}
}