<?php
// 加载后台控制器
tsload ( APPS_PATH . '/admin/Lib/Action/AdministratorAction.class.php' );
class CouponConfigAction extends AdministratorAction {
	
	/**
	 * 初始化
	 */
	public function _initTab() {
		$this->pageTitle ['index'] = '红包配置';
		// tab选项
		$this->pageTab [] = array (
				'title' => '红包配置',
				'tabHash' => 'index',
				'url' => U ( 'admin/CouponConfig/index' ) 
		);
		$this->pageTab [] = array (
				'title' => '中奖纪录',
				'tabHash' => 'couponDetail',
				'url' => U ( 'admin/CouponConfig/couponDetail' ) 
		);
	}
	
	/**
	 * 梦想盒子
	 * 后台管理
	 */
	public function index() {
		$result = M ( 'db_coupon_set' )->where ( 'id=1' )->find ();
		if ($result) {
			$result ['total'] = $result ['total'] / 100;
			$result ['balance'] = $result ['balance'] / 100;
			$result ['cost'] = $result ['cost'] / 100;
			$result ['c_max'] = $result ['c_max'] / 100;
			$result ['c_min'] = $result ['c_min'] / 100;
			$result ['unit_val'] = $result ['unit_val'] / 100;
			$result ['probability'] = $result ['probability'] * 100;
			$result ['stime'] = date ( 'Y-m-d H:i:s', $result ['stime'] );
			$result ['etime'] = date ( 'Y-m-d H:i:s', $result ['etime'] );
		} else {
			$result ['total'] = 0.0;
			$result ['balance'] = 0.0;
			$result ['cost'] = 0.0;
			$result ['c_max'] = 0.0;
			$result ['c_min'] = 0.0;
			$result ['unit_val'] = 0.0;
		}
		$this->assign ( 'title', '红包配置' );
		$this->assign ( 'result', $result );
		$this->display ();
	}
	
	/**
	 * 梦想盒子
	 * 后台管理
	 */
	public function couponDetail() {
		$this->_initTab ();
		$_REQUEST ['tabHash'] = 'couponDetail';
		$this->pageKeyList = array (
				'userid',
				'realname',
				'school_name',
				'ctime',
				'money' 
		);
		
		$this->searchKey = array (
				'userid',
				'realname',
				'school_name',
				array (
						'ctime',
						'ctime1' 
				) 
		);
		
		$this->pageButton [] = array (
				'title' => '搜索',
				'onclick' => "admin.fold('search_form')" 
		);
		$this->pageButton [] = array (
				'title' => '导出excel',
				'onclick' => 'admin.exportExcel(\'' . U ( 'admin/CouponConfig/exportExcel' ) . '\')' 
		);
		$this->displayList ( $this->getListData () );
	}
	/**
	 * 梦想盒子
	 * 后台管理
	 */
	public function saveCouponConfig() {
		$data ['switch'] = $_POST ['switch'];
		$phone_reg = '/^[0-9]+(.[0-9]+)?$/';
		// if (! preg_match ( $phone_reg, $_POST ['total'] )) {
		// $this->error ( '累计账户总额输入不合法，必须为数字' );
		// }
		// $data ['total'] = $_POST ['total'] * 100;
		// if (! preg_match ( $phone_reg, $_POST ['balance'] )) {
		// $this->error ( '当前剩余金额输入不合法，必须为数字' );
		// }
		// $data ['balance'] = $_POST ['balance'] * 100;
		if (! preg_match ( '/^[0-9]{1,7}(.[0-9]{1,2})?$/', $_POST ['toupdate'] )) {
			$this->error ( '变更金额输必须为数字，并且每次变更不能大于10000000元' );
		}
		if (! preg_match ( $phone_reg, $_POST ['c_max'] )) {
			$this->error ( '单个红包最大金额输入不合法，必须为数字' );
		}
		$data ['c_max'] = $_POST ['c_max'] * 100;
		if (! preg_match ( $phone_reg, $_POST ['c_min'] )) {
			$this->error ( '单个红包最小金额输入不合法，必须为数字' );
		}
		$data ['c_min'] = $_POST ['c_min'] * 100;
		if (! preg_match ( $phone_reg, $_POST ['unit_val'] )) {
			$this->error ( '单个红包随机粒度输入不合法，必须为数字' );
		}
		$data ['unit_val'] = $_POST ['unit_val'] * 100;
		if (! preg_match ( $phone_reg, $_POST ['probability'] )) {
			$this->error ( '红包获得概率输入不合法，必须为数字' );
		}
		$data ['probability'] = $_POST ['probability'] / 100;
		$data ['stime'] = strtotime ( $_POST ['stime'] );
		$data ['etime'] = strtotime ( $_POST ['etime'] );
		$data ['last_time'] = time ();
		$toupdate = $_POST ['toupdate'] * 100;
		$config = M ( 'db_coupon_set' );
		$result = $config->where ( 'id=1' )->find ();
		$log ['op_type'] = 0;
		$log ['amount'] = $result ['total'];
		if ($result) {
			$log ['op_type'] = $data ['total'] - $result ['total'] > 0 ? 1 : $data ['total'] - $result ['total'] == 0 ? 0 : 2;
			$log ['amount'] = abs ( $data ['total'] - $result ['total'] );
			$ret = $config->execute ( "update ts_db_coupon_set set switch={$data ['switch']},balance=balance+$toupdate,total=total+$toupdate,c_max={$data ['c_max']},c_min={$data ['c_min']},unit_val={$data ['unit_val']},probability={$data ['probability']},stime={$data ['stime']},etime={$data ['etime']},last_time={$data ['last_time']} where id=1 " );
			$config->where ( 'id=1' )->save ( $data );
		} else {
			$data ['balance'] = $toupdate;
			$data ['total'] = $toupdate;
			$data ['cost'] = 0;
			$ret = $config->add ( $data );
		}
		if ($ret) {
			// 清空缓存
			M ( 'Cache' )->rm ( 'coupon_set' );
			// 写日志
			$log ['operator'] = $this->mid;
			$log ['ip'] = get_client_ip ();
			$log ['ctime'] = time ();
			$log ['before_conf'] = $result ? serialize ( $result ) : '';
			$log ['after_conf'] = serialize ( $data );
			M ( 'db_coupon_set_log' )->add ( $log );
		}
		$this->assign ( 'title', '红包配置' );
		$this->assign ( 'result', $result );
		$this->success ( "保存成功" );
	}
	
	/**
	 * 导出excel
	 */
	public function exportExcel() {
		$listData = $this->getListData ( 99999999 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( '暂无数据' );
		}
		// 载入Excel操作类
		require_once ADDON_PATH . '/library/Excel.class.php';
		$excel = new Excel ();
		$top = array (
				'userid' => '用户ID',
				'realname' => '用户姓名',
				'school_name' => '学校名称',
				'ctime' => '中奖时间',
				'money' => '中奖金额' 
		);
		$excel->exportCsv ( $listData ['data'], $top, '中奖纪录' );
	}
	private function getListData($limit = 20) {
		if (! empty ( $_POST )) {
			$_POST ['userid'] && $map ['l.userid'] = array (
					'LIKE',
					'%' . t ( $_POST ['userid'] ) . '%' 
			);
			$_POST ['realname'] && $map ['v.realname'] = array (
					'LIKE',
					'%' . t ( $_POST ['realname'] ) . '%' 
			);
			$_POST ['school_name'] && $map ['s.name'] = array (
					'LIKE',
					'%' . t ( $_POST ['school_name'] ) . '%' 
			);
			// 流水时间判断，ctime为数组格式
			if (! empty ( $_POST ['ctime'] )) {
				if (! empty ( $_POST ['ctime'] [0] ) && ! empty ( $_POST ['ctime'] [1] )) {
					// 时间区间条件
					$map ['l.ctime'] = array (
							'BETWEEN',
							array (
									strtotime ( $_POST ['ctime'] [0] ),
									strtotime ( $_POST ['ctime'] [1] ) 
							) 
					);
				} else if (! empty ( $_POST ['ctime'] [0] )) {
					// 时间大于条件
					$map ['l.ctime'] = array (
							'GT',
							strtotime ( $_POST ['ctime'] [0] ) 
					);
				} elseif (! empty ( $_POST ['ctime'] [1] )) {
					// 时间小于条件
					$map ['l.ctime'] = array (
							'LT',
							strtotime ( $_POST ['ctime'] [1] ) 
					);
				}
			}
		}
		return M ( 'db_coupon_log' )->field ( 'l.userid,
		v.realname,
		s.`name` school_name,
		FROM_UNIXTIME(l.ctime) ctime,
		ROUND(l.change_val/100,2) money' )->join ( 'as l LEFT JOIN ts_user_verified v ON l.userid = v.uid
		LEFT JOIN ts_db_school s ON v.sid = s.id' )->where ( $map )->order('l.ctime desc')->findPage ( $limit );
	}
}