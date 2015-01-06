<?php
class CreditSettingModel extends Model {
	var $tableName = 'db_credit_setting';
	
	/**
	 * 积分规则
	 */
	public function getIntegralRules($limit) {
		$listData = M ( 'db_credit_setting' )->order ( 'id asc' )->findPage ( $limit );
		return $listData;
	}
	
	/**
	 * 增加/编辑积分规则
	 *
	 * @param unknown $_POST        	
	 */
	public function addIntegralRule($_POST) {
		if (! empty ( $_POST )) {
			$_POST ['id'] && $data ['id'] = t ( $_POST ['id'] );
			$_POST ['name'] && $data ['name'] = t ( $_POST ['name'] );
			$_POST ['title'] && $data ['title'] = t ( $_POST ['title'] );
			// 配置可能只针对积分，所以积分类型字段暂时屏蔽
			
			// $_POST ['type_name'] && $data ['type_name'] = t ( $_POST ['type_name'] );
			// $_POST ['type_title'] && $data ['type_title'] = t ( $_POST ['type_title'] );
			$data ['type_name'] = 'integral';
			$data ['type_title'] = '积分';
			$data ['value'] = floatval( $_POST ['value'] );
			$data ['rate'] = floatval ( $_POST ['rate'] );
			// 修改
			if ($data ['id']) {
				$map ['id'] = $data ['id'];
				$res = $this->where ( $map )->save ( $data );
			} else {
				// 新增
				$res = $this->add ( $data );
			}
			model('cache')->rm('credit_setting');
		}
		return $res;
	}
	/**
	 * 删除积分规则
	 *
	 * @param unknown $_POST        	
	 */
	public function delRule($_POST) {
		$ids = $_POST ['id'];
		if (is_array ( $ids )) {
			$ids = implode ( ',', $ids );
		}
		$map ['id'] = array (
				'IN',
				$ids 
		);
		return $this->where ( $map )->delete ();
	}
}