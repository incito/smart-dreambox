<?php
/**
 * 全站标签相关数据
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'weibo') {
    
	$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$tag_sql = 'SELECT * FROM `ts_weibo` LIMIT '.$count * ($p - 1).','.$count.';';
	$tag_list = $old_db->query($tag_sql);
	if(empty($tag_list)) {
		// 跳转操作
		$t = 'blog';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
		foreach($tag_list as $value) {
		    $cover = '';
		    $content = $value['content'];
		    $content = str_ireplace("'", "", $content);
		    $type_data = $value['type_data'];
		    if(!empty($type_data)){
		        $image = array();
		        getWeiboImage($type_data, $image);
		        $i = 0;
		        foreach ($image as $im){
		            $i++;
		            if($i == 1){
		                $cover = $im;
		            }
		            $content = $content.'<img src="'.$im.'" />';
		        }
// 		        echo 'cover:'.$cover;
// 		        echo 'content:'.$content;
		    }
		    $status = 1;
		    if($value['isdel'] == 1){
		        $status = 2;
		    }
		    if($value['transpond_id'] > 0){
		        $status = 4;//转载的文章在新版暂时不显示
		    }
		    
			$value = updateValue($value);
			$data[] = "('".$value['weibo_id']."', '".$value['uid']."', '".$value['title']."', '".$cover."', '".$content."', '".$value['ctime']."', '".$status."', '".$value['is_best']."', '".$value['transpond_id']."')";
		}
		$fields = '(id,uid,title,cover,content,cTime,status,hot,ref_id)';
		$insert_tag = 'INSERT INTO ts_blog '.$fields.' VALUES '.implode(',', $data);
        $result = $db->execute($insert_tag);
        if($result === false) {
            foreach($data as $single_value) {
                $single_insert_user = 'INSERT INTO ts_blog '.$fields.' VALUES '.$single_value;
                $result = $db->execute($single_insert_user);
                if($result === false) {
                    writeErrorLog($single_insert_user);
                }
            }
        }
        
		// 跳转操作
		$t = 'weibo';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}