<?php
class MoneyAction extends WechatAction {
	
	/**
	 * 红包流水
	 */
	public function winningList(){
		//openid
		$openid=$this->postData->openId;		
		//页码
		$page=intval($this->postData->page);
		$uid=getUidByOid($openid);
		//如果未绑定，返回错误消息
		if(!uid){
			exit(json_encode(array('code'=>0,'msg'=>'不存在的用户')));
		}
		$list=M('Coupon')->getListByUid($uid, 'ctime,comment,change_val', $page);
		$tmp=array();
		foreach ($list['data'] as $v){
			$day=date('Y-m-d',$v['ctime']);
			$tmp[$day][]=array('time'=>date('H:i',$v['ctime']),'from'=>$v['comment'],'money'=>$v['change_val']/100);
		}
		$ret=array();
		foreach ($tmp as $k=>$v){
			$ret[]=array($k=>$v);
		}
		exit(json_encode(array('code'=>1,'data'=>array('num'=>$list['pageNum'],'count'=>$list['pageCount'],'list'=>$ret))));
	}
}

?>