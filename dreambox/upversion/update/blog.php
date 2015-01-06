<?php
/**
 * 全站标签相关数据
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'blog') {
    
	$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$tag_sql = 'SELECT * FROM `ts_blog` LIMIT '.$count * ($p - 1).','.$count.';';
	$tag_list = $old_db->query($tag_sql);
	if(empty($tag_list)) {
		echo '数据迁移完成，请查看日志文件：upversion/error.txt';
		exit;
	} else {
		$data = array();
		foreach($tag_list as $value) {
            $cover = $value['covert'];
		    $content = $value['content'];
		    $content = str_ireplace("'", "", $content);
		    $images = matchImages(safe($content));
		    $images[0] && $cover = $images[0];
            
			$value = updateValue($value);
			$data[] = "('".$value['uid']."', '".$value['name']."', '".$value['title']."', '".$value['category']."', '".$value['category_title']."', '".$cover."', '".$content
			          ."', '".$value['readCount']."', '".$value['tags']."', '".$value['cTime']."', '".$value['mTime']."', '".$value['rTime']."', '".$value['isHot']."', '".$value['status']
	                  ."', '".$value['private']."', '".$value['private_data']."', '".$value['hot']."', '".$value['canableComment']."', '".$value['attach']."', 0)";
		}
		$fields = '(uid,name,title,category,category_title,cover,content,readCount,tags,cTime,mTime,rTime,isHot,status,private,private_data,hot,canableComment,attach,ref_id)';
		$insert_tag = 'INSERT INTO ts_blog '.$fields.' VALUES '.implode(',', $data);
        $result = $db->execute($insert_tag);
        if($result === false) {
            foreach($data as $single_value) {
                $single_insert_user = 'INSERT INTO ts_blog '.$fields.' VALUES '.$single_value;
                $result = $db->execute($single_insert_user);
                if($result === false) {
                    writeErrorLog($single_insert_user);exit;
                }
            }
        }
        
		// 跳转操作
		$t = 'blog';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}