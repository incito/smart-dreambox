<?php
/**
 * 梦想课程
 * @example
 * 1.用户地区数据未实现
 * 2.用户最后发布微博信息未实现
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'course') {
    //主课程
	$user_sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'adreambox_course`;';
	$user_list = $old_db->query($user_sql);
	foreach($user_list as &$value) {
	    $value = updateValue($value);
	    $data[] = "(".$value['id'].",".$value['sum'].",'".$value['class_name']."','".$value['class_intro']."','".$value['type']
    		    ."','".$value['gid']."','".$value['preview']."','".$value['resources']."','".$value['course_integral']
    		    ."','".$value['imgurl']."','".$value['create_time']."','".$value['vdio']."','".$value['class_devname']
	            ."','".$value['edit_time']."','".$value['uid']."','".$value['is_close']."','".$value['is_del']."')";
	}
	if(!empty($data)){
        $fields = '(id,sum,class_name,class_intro,type,gid,preview,resources,course_integral,imgurl,create_time,vdio,class_devname,edit_time,uid,is_close,is_del)';
    	$insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_course` '.$fields.' VALUES '.implode(',', $data);
    	$result = $db->execute($insert_user);
    	if($result === false) {
    	    writeErrorLog($insert_user);exit;
    	}
	}
	
	//子课程
	$user_sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'adreambox_class_hours`;';
	$user_list = $old_db->query($user_sql);
	foreach($user_list as &$value) {
	    $value = updateValue($value);
	    $hours_data[] = "(".$value['id'].",".$value['hid'].",'".$value['number']."','".$value['title']."','".$value['create_time']."')";
	}
	if(!empty($hours_data)){
    	$fields = '(id,hid,number,title,create_time)';
    	$insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_class_hours` '.$fields.' VALUES '.implode(',', $hours_data);
    	$result = $db->execute($insert_user);
    	if($result === false) {
    	    writeErrorLog($insert_user);exit;
    	}
	}
	
	//年级
	$user_sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'adreambox_grade`;';
	$user_list = $old_db->query($user_sql);
	foreach($user_list as &$value) {
	    $value = updateValue($value);
	    $grade_data[] = "(".$value['id'].",'".$value['grade_name']."','".$value['create_time']."')";
	}
	if(!empty($grade_data)){
    	$fields = '(id,name,ctime)';
    	$insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_grade` '.$fields.' VALUES '.implode(',', $grade_data);
    	$result = $db->execute($insert_user);
    	if($result === false) {
    	    writeErrorLog($insert_user);exit;
    	}
	}
	
	// 跳转操作
	$t = 'user_follow';
	$p = 1;
	echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
	exit;
}