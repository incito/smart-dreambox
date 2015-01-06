<?php
/**
 * 积分信息
 * @author xucaibing
 *
 */
class IntegralAction extends WechatAction {
	/**
	 * 查询最近一周信息
	 */
	public function queryWeek() {
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$param = json_decode ( $raw_post_data );
		$openId = trim ( $param->openId );
		$uid = getUidByOid ( $openId );
		// 查询总积分
		$inteInfo = M ( 'db_integral' )->field ( '(self_integral+trans_integral) as integral,sum_integral ' )->where ( array (
				'ref_id' => $uid 
		) )->find ();
		$detailSql = "SELECT
					FROM_UNIXTIME(h.ctime, '%Y-%m-%d') time,
					SUM(h.increase_integral) integral
				FROM
					ts_db_integral i
				LEFT JOIN ts_db_integral_history h ON i.id = h.integral_id
				WHERE
					i.ref_id =" . $uid . " AND h.ctime >= UNIX_TIMESTAMP(
					DATE_SUB(CURDATE(), INTERVAL 1 WEEK)
				)
				GROUP BY time";
		// 获取积分详情
		$detailInfo = M ()->query ( $detailSql );
		$result = array (
				'code' => 1,
				'msg' => 'ok',
				'data' => array (
						'validIntegral' => $inteInfo ['integral'],
						'sumIntegral' => $inteInfo ['sum_integral'],
						'integarlDetail' => $detailInfo 
				) 
		);
		$this->ajaxReturn ( $result );
	}
	
	/**
	 * 查询所有积分信息
	 */
	public function queryAll() {
		$raw_post_data = file_get_contents ( 'php://input', 'r' );
		$param = json_decode ( $raw_post_data );
		$openId = trim ( $param->openId );
		$uid = getUidByOid ( $openId );
		// 查询总积分
		$inteInfo = M ( 'db_integral' )->field ( '(self_integral+trans_integral) as integral,sum_integral ' )->where ( array (
				'ref_id' => $uid 
		) )->find ();
		$start = max ( 0, (intval ( $param->page ) - 1) * 20 );
		$detailSql = "SELECT
					FROM_UNIXTIME(h.ctime, '%Y-%m-%d') time,
					SUM(h.increase_integral) integral
				FROM
					ts_db_integral i
				LEFT JOIN ts_db_integral_history h ON i.id = h.integral_id
				WHERE
					i.ref_id =" . $uid . " GROUP BY time LIMIT " . $start . ",20";
		// 获取积分详情
		$detailInfo = M ()->query ( $detailSql );
		$result = array (
				'code' => 1,
				'msg' => 'ok',
				'data' => array (
						'validIntegral' => $inteInfo ['integral'],
						'sumIntegral' => $inteInfo ['sum_integral'],
						'integarlDetail' => $detailInfo 
				) 
		);
		$this->ajaxReturn ( $result );
	}
}

?>