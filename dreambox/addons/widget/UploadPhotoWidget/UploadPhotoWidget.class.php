<?php
/**
 * 照片批量上传控件
 * @author yKF48801
 *
 */
class UploadPhotoWidget extends Widget{
	public function render($data){
		// 获取相册配置信息
		$var = photo_getConfig();
		$var['photo_max_limit'] = $var['photo_max_limit'] * 1024;
		//创建相册元素的ID
		$data['createAlbumId']&&$var['createAlbumId']=$data['createAlbumId'];
		//相册ID
		$data['albumId']&&$var['albumId']=$data['albumId'];
		//flash或普通上传
		$data['type']=='normal'&&$var['type']='normal';
		    //渲染模版
		$content = $this->renderFile(dirname(__FILE__)."/UploadPhoto.html",$var);
        //输出数据
		return $content;
    }
    /**
     * 获取应用配置参数
     * @param string $key 指定的配置KEY值
     * @return mixed(array|string) 应用配置参数
     */
    function photo_getConfig ($key = null) {
    	$config = model('Xdata')->lget('photo');
    	$config['album_raws'] || $config['album_raws'] = 6;
    	$config['photo_raws'] || $config['photo_raws'] = 8;
    	$config['photo_preview'] == 0 || $config['photo_preview'] = 1;
    	$config['photo_max_limit'] = $config['photo_max_size'];
    	($config['photo_max_size'] = floatval($config['photo_max_size']) * 1024 * 1024) || $config['photo_max_size'] = -1;
    	$config['photo_file_ext'] || $config['photo_file_ext'] = 'jpeg,gif,jpg,png';
    	$config['max_flash_upload_num'] || $config['max_flash_upload_num'] = 10;
    	$config['open_watermark']==0 || $config['open_watermark'] = 1;
    	$config['watermark_file'] || $config['watermark_file'] = 'public/images/watermark.png';
    	if ($key == null) {
    		return $config;
    	} else {
    		return $config[$key];
    	}
    }
}
?>