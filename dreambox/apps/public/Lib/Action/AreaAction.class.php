<?php
class AreaAction extends Action {
	public function area() {
		//已选地区
		$pNetwork = D('Area');
		$list = $pNetwork->getAreaList($_REQUEST['pid']);
		$this->ajaxReturn(json_encode($list));
	}
	/**
	 * 返回拼音首字母分组的数据
	 */
	public function getAreaWithFL(){
		$sql='select pinyin as letter,area_id as id,title as name from ( select pinyin,area_id,title from ts_area where pid='.intval($_REQUEST['pid']).' order by sort ) as t group by pinyin,area_id';
		$data=M()->query($sql);
		$res=array(); 
		foreach($data as $v){
			$letter=$v['letter'];
			if(!$res[$letter]){
				$res[$letter]=array();
			}
			array_push($res[$letter], array('id'=>$v['id'],'name'=>$v['name']));
		} 
		$this->ajaxReturn(json_encode($res));
	}
}