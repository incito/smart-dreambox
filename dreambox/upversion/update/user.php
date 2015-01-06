<?php
/**
 * 用户相关数据
 * @example
 * 1.用户地区数据未实现
 * 2.用户最后发布微博信息未实现
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'user') {
	require_once('./extends/PinYin.php');
	$Py = new PinYin();
	
	$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$user_sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'user` WHERE email is not null ORDER BY uid LIMIT '.$count * ($p - 1).','.$count.';';
	$user_list = $old_db->query($user_sql);
	if(empty($user_list)) {
		// 跳转操作
		$t = 'school';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
		foreach($user_list as &$value) {
			$salt = rand(11111, 99999);
			$salt = mysql_escape_string($salt);
			$password = md5($value['password'].$salt);
			$password = md5('96e79218965eb72c92a549dd5a330112'.$salt);//临时的固定密码，方便压力测试
			$password = mysql_escape_string($password);
			$sex = ($value['sex'] == 1) ? 1 : 2;
			$sex = mysql_escape_string($sex);
			$first_letter = getFirstLetter($value['uname']);
			$first_letter = mysql_escape_string($first_letter);
			$search_key = $value['uname'].' '.$Py->Pinyin($value['uname']);
			$search_key = mysql_escape_string($search_key);
			//是否禁用
			$is_del = 0;
		    if(in_array($value['uid'], $disabledIds)){
		        $is_del = 1;
		    }
		    
            //所属地区数据转换
		    $result = $old_area->getAreaById($value['city']);
		    $c_title = $result['title'];
		    $p_title = $result['p_title'];
		    $result = $old_area->getAreaById($value['area']);
		    $a_title = $result['title'];
		    //根据名称查询新的地区id
		    $p_id = $area->getProvinceId($p_title);
		    $c_id = $area->getAreaId($c_title, $p_title);
		    $a_id = $area->getAreaId($a_title, $c_title);
		    $location = $p_title.' '.$c_title.' '.$a_title;
		    
			$value = updateValue($value);
			$data[] = "(".$value['uid'].",'".$value['email']."','".$password."','".$salt."','".$value['uname']."','".$value['email']."','".$sex."','".$location."','1','".$value['is_active']."','".$value['is_init']."','".$value['ctime']."','1',null,'".$value['domain']."','".$p_id."','".$c_id."','".$a_id."', '127.0.0.1', 'zh-cn', 'PRC', ".$is_del.", '".$first_letter."', '".$search_key."')";
			
			// 添加用户组信息
			$user_group_id = 2; //默认为普通用户
			$user_group_link_sql = 'SELECT * FROM `ts_user_group_link` AS gl LEFT JOIN `ts_user_group` AS g ON gl.user_group_id = g.user_group_id WHERE `uid` = '.$value['uid'].' LIMIT 1';
			$user_group_info = $old_db->query($user_group_link_sql);
			if(!empty($user_group_info)){
			    $group_new = $db->query("SELECT user_group_id FROM ts_user_group WHERE user_group_name = '".$user_group_info[0]['title']."'");
			    if(!empty($group_new))
			    {
			        $user_group_id = $group_new[0]['user_group_id'];
			    }
			}
			$user_group_data[] = "(".$value['uid'].", ".$user_group_id.")";
		}
		$fields = '(uid, login, password, login_salt, uname, email, sex, location, is_audit, is_active, is_init, ctime, identity, api_key, domain, province, city, area, reg_ip, lang, timezone, is_del, first_letter, search_key)';
		$insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user` '.$fields.' VALUES '.implode(',', $data);
 		$result = $db->execute($insert_user);
		if($result === false) {
		    writeErrorLog($insert_user);exit;
		}
		//用户组
		if(!empty($user_group_data)){
    		$fields = '(uid, user_group_id)';
    		$insert_group_link = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_group_link` '.$fields.' VALUES '.implode(',', $user_group_data);
    		$result = $db->execute($insert_group_link);
    		if($result === false) {
    		    foreach($user_group_data as $single_value) {
    		        $single_insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_group_link` '.$fields.' VALUES '.$single_value;
    		        $result = $db->execute($single_insert_user);
    		        if($result === false) {
    		            writeErrorLog($single_insert_user);
    		        }
    		    }
    		}
		}
		
		// 跳转操作
		$t = 'user';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}