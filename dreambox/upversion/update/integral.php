<?php
/**
 * 全站标签相关数据
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'integral') {
	$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$tag_sql = 'SELECT * FROM `db_user` LIMIT '.$count * ($p - 1).','.$count.';';
	$tag_list = $db_bank->query($tag_sql);
	if(empty($tag_list)) {
	    //处理没有积分的实名认证用户
	    $sql = "SELECT uid FROM ts_user_verified WHERE verified = '1' AND uid NOT IN (SELECT ref_id FROM ts_db_integral )";
	    $uids = $db->query($sql);
	    foreach ($uids as $uid){
	        $integral_data[] = "(0, 0, 0, '0', '0', 0, ".$uid['uid'].")";
	    }
	    $fields = '(self_integral,trans_integral,sum_integral,is_frozen,is_deleted,ref_type,ref_id)';
	    $insert_integral = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_integral` '.$fields.' VALUES '.implode(',', $integral_data);
	    $result = $db->execute($insert_integral);
	    if($result === false) {
	        writeErrorLog($insert_integral);exit;
	    }
	    
		// 跳转操作
		$t = 'integral_convert';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
		foreach($tag_list as $value) {
		    $uid = $user_email[$value['EMAIL']]; 
		    if(empty($uid)){
		        continue;
		    }
		    $is_frozen = $value['IS_FROZEN'] == 'y' ? 1 : 0; 
		    $is_deleted = $value['IS_DELETED'] == 'y' ? 1 : 0; 
		    
			$value = updateValue($value);
			$data[] = "('".$value['SELF_INTEGRAL']."', '".$value['USABLE_INTEGRAL']."', '".$value['SUM_INTEGRAL']."', '".$is_frozen."', '".$is_deleted."', 0, '".$uid."')";
		}
		$fields = '(self_integral,trans_integral,sum_integral,is_frozen,is_deleted,ref_type,ref_id)';
		$insert_tag = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_integral` '.$fields.' VALUES '.implode(',', $data);
		$result = $db->execute($insert_tag);
		if($result === false) {
		    writeErrorLog($insert_tag);exit;
		}
		
		// 跳转操作
		$t = 'integral';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}