<?php
/**
 * 全站标签相关数据
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'integral_convert') {
	$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$tag_sql = 'SELECT * FROM ts_db_integral LIMIT '.$count * ($p - 1).','.$count.';';
	$tag_list = $db->query($tag_sql);
	if(empty($tag_list)) {
		// 跳转操作
		$t = 'integral_history';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
	    foreach ($tag_list as $integral){
	        $query = 'SELECT uid, score FROM ts_credit_user WHERE score>0 AND uid = '.$integral['ref_id'].' LIMIT 1';
	        $credit = $old_db->query($query);
	        if(empty($credit)){
	            continue;
	        }
	        $score = $credit[0]['score'];
	        $update = 'UPDATE ts_db_integral SET self_integral = self_integral + '.$score.', sum_integral = sum_integral + '.$score.' WHERE ref_id='.$integral['ref_id'];
	        $result = $db->execute($update);
	        if($result === false) {
	            writeErrorLog($update);
	            continue;
	        }
	        //记录明细
	        $data[] = "(".$integral['id'].",'".$integral['self_integral']."', '".($integral['self_integral']+$score)."', '".$score."', '51', 1, '数据迁移时金币按1:1转成积分', '".time()."', '".time()."')";
	    }
	    if(!empty($data)){
            $fields = '(integral_id,old_integral,new_integral,increase_integral,type,operator_id,comment,ctime,mtime)';
            $insert_tag = 'INSERT INTO `ts_db_integral_history` '.$fields.' VALUES '.implode(',', $data);
            $result = $db->execute($insert_tag);
            if($result === false) {
                writeErrorLog($insert_tag);
            }
	    }
		
		// 跳转操作
		$t = 'integral_convert';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}