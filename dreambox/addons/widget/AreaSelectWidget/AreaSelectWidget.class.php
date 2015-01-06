<?php
/**
 * 地区选择 widget
 * @example W('Area',array('curPro'=>1,'curCity'=>2,'area'=>3,'tpl'=>'loadCity'))
 * @author Jason
 * @version TS3.0
 */
class AreaSelectWidget extends Widget {
	
    /**
     * @param integer curPro 当前省的ID
     * @param integer curCity 当前城市的ID
     * @param integer area 当前地区的ID
     * @param string  tpl 选用的地区选择模版 loadCity(链接方式) loadArea(文本框形式)
     */
	public function render($data) {
		//设置默认值
		if(!empty($data)){
				$var['provId']=$data['provId'];
				$var['provName']=$data['provName'];
				$var['cityId']=$data['cityId'];
				$var['cityName']=$data['cityName'];
				$var['areaId']=$data['areaId'];
				$var['areaName']=$data['areaName'];
				$var['schoolId']=$data['schoolId'];
				$var['schoolName']=$data['schoolName'];
				$var['css']=$data['css'];
				$var['address']=$data['address'];
				$var['td1_css']=$data['td1_css'];
				//设置change事件回调函数
				$var['change']=$data['change'];
				$var['html']=$data['html'];
		}
		// 模式 0：只选择地区，1：选择学校
		$var ['module']=$data ['module']?'1':'0';
		$content = $this->renderFile(dirname(__FILE__)."/loadArea.html", $var);
		return $content;
	}
}