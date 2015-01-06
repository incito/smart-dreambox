<?php
/**
 * 学校数据
 * @example
 * 1.用户地区数据未实现
 * 2.用户最后发布微博信息未实现
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'school') {
	$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$user_sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'group` LIMIT '.$count * ($p - 1).','.$count.';';
	$user_list = $old_db->query($user_sql);
	if(empty($user_list)) {
		// 跳转操作
		$t = 'verified';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
		foreach($user_list as &$value) {
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
		    
		    $first_letter = getFirstLetter($value['name']);
		    $first_letter = mysql_escape_string($first_letter);
		    $value = updateValue($value);
		    $data[] = "(".$value['id'].",".$value['uid'].",'".$value['name']."','".$first_letter."','".$value['intro']."','".$value['logo']."','".$value['announce']."','".$value['cid0']."','".$value['cid1']."','".$value['membercount']."','".$value['threadcount']."','".$value['type']
		            ."','".$value['need_invite']."','".$value['need_verify']."','".$value['actor_level']."','".$value['brower_level']."','".$value['openWeibo']."','".$value['openBlog']."','".$value['openUploadFile']."','".$value['whoUploadFile']."','".$value['whoDownloadFile']."','".$value['openAlbum']
                    ."','".$value['whoCreateAlbum']."','".$value['whoUploadPic']."','".$value['anno']."','".$value['ipshow']."','".$value['invitepriv']."','".$value['createalbumpriv']."','".$value['uploadpicpriv']."','".$value['ctime']."','".$value['mtime']."','".$value['status']
		            ."','".$value['isrecom']."','".$value['is_del']."','".$value['location']."','".$value['coordinate_lng']."','".$value['coordinate_lat']."','".$value['sponsors']."','".$value['schoolmaster']."','".$value['school_number']."','".$value['school_type']
		            ."','".$value['school_alias']."','".$value['admin_email']."','".$value['phone']."','".$value['zip_code']."','".$value['contact']."','".$value['tel']."','".$value['tel2']."','".$value['salon']."','".$p_id."','".$c_id."','".$a_id."')";
		}
		$fields = '(id, uid, name, first_letter, intro, logo,announce,cid0,cid1,membercount,threadcount,type,need_invite,need_verify,actor_level,brower_level,openWeibo,openBlog,openUploadFile,whoUploadFile,whoDownloadFile,openAlbum,whoCreateAlbum,whoUploadPic,anno,ipshow,invitepriv,createalbumpriv,uploadpicpriv,ctime,mtime,status,isrecom,is_del,location,coordinate_lng,coordinate_lat,sponsors,remark,school_number,school_type,school_alias,email,phone,zip_code,contact,tel,tel2,salon,province,city,area)';
		$insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'db_school` '.$fields.' VALUES '.implode(',', $data);
		$result = $db->execute($insert_user);
		if($result === false) {
		    writeErrorLog($insert_user);exit;
		}
		// 跳转操作
		$t = 'school';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}