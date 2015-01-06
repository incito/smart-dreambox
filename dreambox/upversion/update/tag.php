<?php
/**
 * 全站标签相关数据
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'tag') {
	$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$tag_sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'tag` LIMIT '.$count * ($p - 1).','.$count.';';
	$tag_list = $old_db->query($tag_sql);
	if(empty($tag_list)) {
		// 跳转操作
		$t = 'integral';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
		foreach($tag_list as $value) {
			$value = updateValue($value);
			$data[] = "('".$value['tag_id']."', '".$value['tag_hid']."', '".$value['tag_name']."', '".$value['type']."')";
		}
		$insert_tag = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'tag` (tag_id, tag_hid, name, type) VALUES '.implode(',', $data);
		$db->execute($insert_tag);

		// 跳转操作
		$t = 'tag';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}