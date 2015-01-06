<?php
/**
 * 实名认证数据
 * @example
 * 1.用户地区数据未实现
 * 2.用户最后发布微博信息未实现
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
if($_GET['t'] == 'verified') {
	$p = !empty($_GET['p']) ? intval($_GET['p']) : 1;
	$count = 1000;
	$user_sql = 'SELECT uv.*, gm.gid, gm.level FROM `'.$old_db_conf['DB_PREFIX'].'user_verified` AS uv LEFT JOIN `'.$old_db_conf['DB_PREFIX'].'group_member` AS gm ON uv.uid = gm.uid AND gm.level <> 1 LIMIT '.$count * ($p - 1).','.$count.';';
	$user_list = $old_db->query($user_sql);
	if(empty($user_list)) {
		// 跳转操作
		$t = 'course';
		$p = 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	} else {
		$data = array();
		foreach($user_list as &$value) {
		    //是否经过实名认证
		    $verified = 0;
		    if($value['verified'] == 1 && $value['gid'] > 0){//通过实名认证且加入了学校
		        $verified = 1;
		    }else if(empty($value['gid'])){//还没申请加入学校
		        $verified = 2;//驳回状态，用户可以重新提交申请
		    }
		    
		    //是否是学校账号
		    $type = 0;
		    if($value['level'] == 2){
		        $type = 1;
		    }
		    
		    $value = updateValue($value);
		    $data[] = "(".$value['id'].",".$value['uid'].",'".$value['gid']."','".$value['realname']."','".$value['phone']
        		    ."','".$value['reason']."','".$value['info']."','".$verified."','".$value['attachment']."','".$value['idcard']
        		    ."','".$value['bankname']."','".$value['bankaccount']."','".$type."', 1)";

		    //实名认证的附件
		    $sql = 'SELECT * FROM `'.$old_db_conf['DB_PREFIX'].'attach` WHERE `id` = '.$value['attachment'].' LIMIT 1';
		    $attach = $old_db->query($sql);
		    if(!empty($attach))
		    {
		        $att = $attach[0];
		        $attach_data[] = "(".$att['id'].",'".$att['attach_type']."','".$att['userId']."','".time()."','".$att['name']
            		        ."','".$att['type']."','".$att['size']."','".$att['extension']."','".$att['hash']."','".$att['private']
            		        ."','".$att['isDel']."','".$att['savepath']."','".$att['savename']."','".$att['savedomain']."', 0)";
		        
		    }
		    
		    //实名用户组
		    if($verified == 1){
    		    $group_id = 4;//默认为梦想老师
    		    if($type == 1){
    		        $group_id = 3;//梦想中心
    		    }
    		    $group_link_data[] = "(".$value['uid'].", ".$group_id.")";
		    }
		}
		//实名认证
		$fields = '(id,uid,sid,realname,phone,reason,info,verified,attachment,idcard,branchbankname,bankaccount,type,first_login)';
		$insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_verified` '.$fields.' VALUES '.implode(',', $data);
		$result = $db->execute($insert_user);
		if($result === false) {
		    writeErrorLog($insert_user);exit;
		}
		//实名认证的附件
		if(!empty($attach_data)){
    		$fields = '(attach_id,attach_type,uid,ctime,name,type,size,extension,hash,private,is_del,save_path,save_name,save_domain,`from`)';
    		$insert = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'attach` '.$fields.' VALUES '.implode(',', $attach_data);
    		$result = $db->execute($insert);
    		if($result === false) {
    		    writeErrorLog($insert);exit;
    		}
		}
		//用户组
		$insert_group_link = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_group_link` (uid, user_group_id) VALUES '.implode(',', $group_link_data);
		$result = $db->execute($insert_group_link);
		if($result === false) {
		    foreach($group_link_data as $single_value) {
				$single_insert_user = 'INSERT INTO `'.$db_conf['DB_PREFIX'].'user_group_link` (uid, user_group_id) VALUES '.$single_value;
				$result = $db->execute($single_insert_user);
				if($result === false) {
					writeErrorLog($single_insert_user);
				}
			}
		}
		
		// 跳转操作
		$t = 'verified';
		$p = $p + 1;
		echo '<script>window.location.href="'.getJumpUrl($t, $p).'";</script>';
		exit;
	}
}