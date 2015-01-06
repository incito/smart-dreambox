<?php
/**
 * 分页控件
 **/
class PageWidget extends Widget{

 	/**
 	 * @param string type 报表类型 分为pieChart(饼状图)、pointLabelsChart(折线图)、barChart(柱状图)
 	 * @param integer id //未知
 	 * @param integer pWidth 图表宽度
 	 * @param integer pHeight 图表高度
 	 * @param array key //好像没用
 	 * @param array value
 	 * @param array color
 	 */
	public function render($data) {
		$var['data']  = isset($data['data']) ? $data['data'] : array('count'=>'1','totalPages'=>1,'totalRows'=>1,'nowPage'=>1);
		$var['href']=isset($data['href']) ?$data['href']:U('public/Index/index');
		//判断url中是否已有参数
		$pos=stripos($var['href'],'?');
		if($pos){
			$var['href']=$var['href'].($pos<strlen($var['href'])-1?'&':'');
		}else{
			$var['href'].='?';
		}
	    //渲染模版
	    $content = $this->renderFile(dirname(__FILE__).'/page.html', $var);


		return $content;
	}
}