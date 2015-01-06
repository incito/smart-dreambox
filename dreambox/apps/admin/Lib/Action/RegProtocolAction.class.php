<?php
/**
 * 账号设置控制器
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class RegProtocolAction extends AdministratorAction {
	/**
	 * 保存注册协议
	 */
	public function saveRegProtocol() {
		$map ['content'] = $_POST ['content'];
		$model = M ( 'db_register_protocol' );
		$data = $model->where ( 'id=1' )->find ();
		if ($data) {
			$map['id'] = $data['id'];
			$model->save ( $map );
		} else {
			$model->add ( $map );
		}
		$this->success ( "保存成功" );
	}
}