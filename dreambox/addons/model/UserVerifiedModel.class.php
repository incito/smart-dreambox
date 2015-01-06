<?php
/**
 * 用户认证模型 - 数据对象模型
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class UserVerifiedModel extends Model {
	protected $tableName = 'user_verified';
	
	/**
	 * 获取指定用户的认证信息
	 *
	 * @param array $uids
	 *        	用户ID
	 * @return array 指定用户的认证信息
	 */
	public function getUserVerifiedInfo($uids) {
		if (empty ( $uids )) {
			return array ();
		}
		$map ['uid'] = array (
				'IN',
				$uids 
		);
		$data = $this->join ( 'left join ts_db_school s on sid=s.id ' )->field ( array_merge ( $this->fields, array (
				'name' => 'school' 
		) ) )->where ( $map )->select ();
		return $data;
	}
	/**
	 * 
	 * 获取用户信息
	 */
	public function getUserInfo($uid) {
		return $this->where ( 'uid=' . $uid )->find ();
	}
	/**
	 * 查询实名认证的状态
	 * 
	 * @param unknown $uid        	
	 */
	public function getVerifyStatus($uid) {
		return $this->where ( 'uid=' . $uid )->getField ( 'verified' );
	}
	
	/**
	 * 根据用户ID查询老师真实名字
	 * 
	 * @param unknown $uid
	 *        	用户ID
	 */
	public function getRealname($uid) {
		return $this->where ( 'uid=' . $uid )->getField ( 'realname' );
	}
	
	/**
	 * 根据用户ID查询所属学校名称
	 * 
	 * @param unknown $uid        	
	 */
	public function getSchoolName($uid) {
		$school = $this->getSchoolInfo ( $uid );
		return $school ['name'];
	}
	
	/**
	 * 根据用户查询所属学校信息,必须是实名认证通过的
	 * 
	 * @param unknown $uid        	
	 */
	public function getSchoolInfo($uid) {
		return $this->join ( 'as t left join ts_db_school s on t.sid=s.id ' )->where ( "t.uid=" . $uid ." AND t.verified = '1'" )->field ( 's.id as id, s.name as name' )->find ();
	}
	
	/**
	 * 根据学校ID查询对应的学校账号的uid
	 * 
	 * @param unknown $sid        	
	 */
	public function getUidBySid($sid) {
		$map ['sid'] = $sid;
		$map ['type'] = 1;
		return $this->where ( $map )->getField ( 'uid' );
	}
}