<?php
/**
 * 全站标签相关数据
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'integral_history') {
	$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$tag_sql = 'SELECT * FROM `db_integral_history` LIMIT '.$count * ($p - 1).','.$count.';';
	$tag_list = $db_bank->query($tag_sql);
	if(empty($tag_list)) {
	    
		// 跳转操作
		$t = 'weibo';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
// 	    $sql = 'select a.email, b.id from ts_user a, ts_db_integral b where a.uid = b.ref_id';
// 	    $integrals = $db->query($sql);
	    
		$data = array();
		foreach($tag_list as $value) {
		    $sql = "SELECT id FROM ts_db_integral WHERE ref_id = (select uid from ts_user where email = '".$value['EMAIL']."')";
		    $integral = $db->query($sql);
		    if(empty($integral)){
		        continue;
		    }
		    $integral_id = $integral[0]['id'];

// 		    $integral_id = 0;
// 		    foreach ($integrals as $integral){
// 		        if($integral['email'] == $value['EMAIL']){
// 		            $integral_id = $integral['id'];
// 		        }
// 		    }
// 		    if($integral_id == 0){
// 		        continue;
// 		    }
		    
		    $type = 50;//标识为迁移积分
		    $ctime = strtotime($value['GMT_CREATE']);
		    $mtime = strtotime($value['GMT_MODIFIED']);
		    
			$value = updateValue($value);
			$data[] = "(".$integral_id.",'".$value['OLD_INTEGRAL']."', '".$value['NEW_INTEGRAL']."', '".$value['INTEGRAL']."', '".$type."', 1, '".$value['COMMENT']."', '".$ctime."', '".$mtime."')";
		}
		$fields = '(integral_id,old_integral,new_integral,increase_integral,type,operator_id,comment,ctime,mtime)';
		$insert_tag = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_integral_history` '.$fields.' VALUES '.implode(',', $data);
        $result = $db->execute($insert_tag);
        if($result === false) {
            writeErrorLog($insert_tag);exit;
        }
        
		// 跳转操作
		$t = 'integral_history';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}