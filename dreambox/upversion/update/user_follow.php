<?php
/**
 * 用户关系
 * @example
 * 1.用户组信息未实现
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'user_follow') {
	$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'weibo_follow` WHERE type = 0 ORDER BY follow_id LIMIT '.$count * ($p - 1).','.$count.';';
	$result = $old_db->query($sql);
	if(empty($result)) {
	    // 跳转操作
	    $t = 'tag';
	    $p = 1;
	    echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
	    exit;
	} else {
    	$data = array();
    	foreach($result as $value) {
    		$value = updateValue($value);$ids[] = $value['follow_id'];
    		$data[] = "('".$value['follow_id']."','".$value['uid']."','".$value['fid']."')";
    	}
    	$insert = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_follow` (follow_id, uid, fid) VALUES '.implode(',', $data);
    	$result = $db->execute($insert);
    	//关注存在重复的数据，采用单条插入
    	if($result === false) {
    	    foreach($data as $single_value) {
				$single_insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_follow` (follow_id, uid, fid) VALUES '.$single_value;
				$result = $db->execute($single_insert_user);
				if($result === false) {
					writeErrorLog($single_insert_user);
				}
			}
    	}
    	// 跳转
    	$t = 'user_follow';
    	$p = $p + 1;
    	echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
    	exit;
	}
}