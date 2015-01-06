<?php
/**
 * 积分规则
 */
class CreditSettingAction extends AdministratorAction {
	public $pageTitle = array ();
	/**
	 * 初始化
	 *
	 * @see AdministratorAction::_initialize()
	 */
	public function _initialize() {
		$this->pageTitle ['integralRules'] = '积分规则';
		$this->pageTitle ['addRule'] = '编辑积分规则';
		parent::_initialize ();
	}
	
	/**
	 * 积分规则配置展示
	 */
	public function integralRules() {
		$integralModel = M ( 'CreditSetting' );
		$listData = $integralModel->getIntegralRules ( 20 );
		// 无权限跳转到错误提示页面
		if (! $listData) {
			$this->error ( 'error!' );
		}
		$this->pageKeyList = array (
				'name',
				'title',
// 				'type_name',
// 				'type_title',
				'value',
				'rate',
				'DOACTION' 
		);
		$this->pageButton [] = array (
				'title' => '添加积分规则',
				'onclick' => "admin.addRule()" 
		);
		$this->pageButton [] = array (
				'title' => '删除积分规则',
				'onclick' => "admin.delRule()" 
		);
		foreach ( $listData ['data'] as &$value ) {
			$do = '<a href=\'' . U ( 'admin/CreditSetting/addRule', array (
					'id' => $value ['id'] 
			) ) . '\'>编辑</a>&nbsp;';
			$do = $do . '<a href=\'javascript:void(0)\' onclick=\'admin.delRule(' . $value ['id'] . ')\'>删除</a>&nbsp;';
			$value ['DOACTION'] = $do;
		}
		
		$this->displayList ( $listData );
	}
	/**
	 * 新增/修改积分规则
	 */
	public function addRule() {
		$integralModel = M ( 'CreditSetting' );
		if (! empty ( $_POST )) {
			$res = $integralModel->addIntegralRule ( $_POST );
			if ($res) {
				$this->assign ( U ( 'admin/CreditSetting/integralRules' ) );
				$this->success ( '保存成功！' );
			} else {
				$this->error ( '保存失败！' );
			}
		}
		$detail = array ();
		if (! empty ( $_REQUEST ['id'] )) {
			$map ['id'] = t ( $_REQUEST ['id'] );
			$detail = $integralModel->where ( $map )->find ();
		} else {
			$this->pageTitle [ACTION_NAME] = '添加积分规则';
		}
		
		$this->savePostUrl = U ( 'admin/CreditSetting/addRule' );
		$this->pageKeyList = array (
				'id',
				'name',
				'title',
				// 'type_name',
				// 'type_title',
				'value',
				'rate',
		);
		$this->notEmpty = array (
				'name',
				'title',
// 				'type_name',
// 				'type_title' 
		);
		$this->onsubmit = 'admin.checkCredit(this)';
		$this->displayConfig ( $detail );
	}
	public function delRule() {
		$res = M ( 'CreditSetting' )->delRule ( $_POST );
		if ($res) {
			$return ['status'] = 1;
			$return ['data'] = '删除成功';
			exit ( json_encode ( $return ) );
		} else {
			$return ['status'] = 0;
			$return ['data'] = '删除失败';
			exit ( json_encode ( $return ) );
		}
	}
}