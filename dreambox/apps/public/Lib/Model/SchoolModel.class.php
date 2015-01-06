<?php
class SchoolModel extends Model {
	var $tableName = 'db_school';
	function _initialize() {
		parent::_initialize ();
	}
	function getapi() {
		return $this->api;
	}
	
	// 获取我的梦想中心 包括我管理的和加入的梦想中心
	public function getAllMyGroup($mid, $html = 0, $open = array(), $limit = false) {
		static $_group_list = array ();
		
		if (! $html && isset ( $_group_list [0] [$mid] )) {
			return $_group_list [0] [$mid];
		}
		
		if (! empty ( $open )) {
			foreach ( $open as $key => $value ) {
				$open [$key] = $key . " = " . intval ( $value );
			}
			$openSql = " AND " . implode ( ' AND ', $open );
		}
		$groupList = $this->table ( C ( 'DB_PREFIX' ) . "group_member as member left join " . C ( 'DB_PREFIX' ) . "group as g on g.id = member.gid" )->field ( 'g.id,g.name,g.openWeibo,g.type,g.membercount,g.logo,g.cid0,g.ctime,g.status' )->where ( 'member.uid = ' . $mid . ' and member.level>0 and g.is_del = 0 ' . $openSql )->order ( 'member.level ASC,member.ctime DESC' );
		if ($limit) {
			$groupList = $groupList->limit ( $limit );
		}
		if ($html) {
			$data = $groupList->findPage ();
		} else {
			$data = $groupList->findAll ();
			$_group_list [0] [$mid] = $data;
		}
		return $data;
	}
	
	// 我管理的梦想中心
	public function mymanagegroup($mid, $html = 0) {
		$gidarr = D ( 'Member' )->field ( 'gid' )->where ( '(level=1 OR level=2) AND uid=' . $mid )->findAll ();
		
		if ($gidarr) {
			$in = 'id IN ' . render_in ( $gidarr, 'gid' ) . ' AND is_del=0';
			$groupList = D ( 'Group' )->field ( 'id,name,type,membercount,logo,cid0,ctime,status' )->where ( $in )->findPage ();
			if (! $html)
				return $groupList ['data'];
			return $groupList;
		}
		return false;
	}
	
	// 我加入的梦想中心
	public function myjoingroup($mid, $html = 0) {
		$gidarr = D ( 'Member' )->field ( 'gid' )->where ( 'level > 1 AND status=1 AND uid=' . $mid )->findAll ();
		
		if ($gidarr) {
			$in = 'id IN ' . render_in ( $gidarr, 'gid' ) . ' AND is_del=0';
			$groupList = D ( 'Group' )->field ( 'id,name,type,membercount,logo,cid0,ctime,status' )->where ( $in )->findPage ();
			if (! $html)
				return $groupList ['data'];
			return $groupList;
		}
		return false;
	}
	
	// 好友加入的群
	function friendjoingroup($mid) {
		import ( "ORG.Util.Page" );
		
		$cond = '';
		$group = array ();
		$friendlist = getfriendlist ( $mid ); // 放入缓存当中
		
		if (! empty ( $friendlist ) && is_array ( $friendlist )) {
			$in = 'uid IN ' . render_in ( $friendlist, 'fuid' );
			
			$count = D ( 'Member' )->field ( 'count(distinct(gid)) AS count' )->where ( $in )->find (); // 显示分页总数
			if ($count ['count'] == 0)
				return '';
			
			$p = new Page ( $count ['count'], 10 );
			$friendgroup = D ( 'Member' )->field ( 'gid' )->where ( $in )->group ( 'gid' )->limit ( $p->firstRow . ',' . $p->listRows )->findAll (); // 获取数据
			
			foreach ( $friendgroup as $k => $v ) {
				$group [$v ['gid']] = D ( 'Member' )->where ( $in . " AND gid=" . $v ['gid'] )->findAll (); // 循环显示朋友
				$group [$v ['gid']] ['c'] = D ( 'Member' )->where ( $in . " AND gid=" . $v ['gid'] )->count ();
			}
			return array (
					$group,
					$p->show () 
			);
		}
		return false;
	}
	
	// 某人加入某梦想中心
	function joinGroup($mid, $gid, $level, $incMemberCount = false, $reason = '') {
		if (D ( 'Member' )->where ( "uid=$mid AND gid=$gid" )->find ())
			exit ( '你已经加入过' );
		
		$member ['uid'] = $mid;
		$member ['gid'] = $gid;
		$member ['name'] = getUserName ( $mid );
		$member ['level'] = $level;
		$member ['ctime'] = time ();
		$member ['mtime'] = time ();
		$member ['reason'] = $reason;
		$ret = D ( 'Member' )->add ( $member );
		
		// 不需要审批直接添加，审批就不用添加了。
		if ($incMemberCount) {
			// 成员统计
			D ( 'Group' )->setInc ( 'membercount', 'id=' . $gid );
			// 积分操作
			X ( 'Credit' )->setUserCredit ( $mid, 'join_group' );
		}
		
		return $ret;
	}
	
	// 个人感兴趣的梦想中心
	public function interestingGroup($uid, $pagesize = 4) {
		// 个人标签
		$user_tag = D ( 'UserTag', 'home' )->getUserTagList ( $uid );
		foreach ( ( array ) $user_tag as $v ) {
			$_tag_id [] = $v ['tag_id'];
			$_tag_in_name .= " OR g.name LIKE '%{$v['tag_name']}%' ";
			$_tag_in_intro .= " OR g.intro LIKE '%{$v['tag_name']}%' ";
		}
		// 管理和已经加入的梦想中心
		$my_group = D ( 'Member' )->field ( 'gid' )->where ( '(level >= 1 AND status=1)  AND uid=' . $uid )->findAll ();
		foreach ( ( array ) $my_group as $v ) {
			$_my_group_id [] = $v ['gid'];
		}
		
		$map = 'g.status=1 AND g.is_del=0';
		$_tag_id && $map .= ' AND (t.tag_id IN (' . implode ( ',', $_tag_id ) . ')';
		$_tag_id && $map .= $_tag_in_name;
		$_tag_id && $map .= $_tag_in_intro . ')';
		$_my_group_id && $map .= ' AND g.id NOT IN (' . implode ( ',', $_my_group_id ) . ')';
		
		$group_count = $this->field ( 'count(DISTINCT(g.id)) AS count' )->table ( "{$this->tablePrefix}group AS g LEFT JOIN {$this->tablePrefix}group_tag AS t ON g.id=t.gid" )->where ( $map )->find ();
		$group_list = $this->field ( 'DISTINCT(g.id),g.name,g.logo,g.membercount,g.ctime' )->table ( "{$this->tablePrefix}group AS g LEFT JOIN {$this->tablePrefix}group_tag AS t ON g.id=t.gid" )->where ( $map )->findPage ( $pagesize, $group_count ['count'] );
		// 标签相关的梦想中心不够四个
		if ($group_list ['count'] < 4) {
			$not_in_gids = array_merge ( $_my_group_id, getSubByKey ( $group_list ['data'], 'id' ) );
			$hot_list = $this->getHotList ( true );
			foreach ( ( array ) $hot_list as $v ) {
				if (! in_array ( $v ['id'], $not_in_gids )) {
					$v ['reason'] = '热门梦想中心';
					$group_list ['data'] [] = $v;
					$not_in_gids [] = $v ['id'];
					$group_count ['count'] ++;
				}
				if ($group_count ['count'] >= 4) {
					break;
				}
			}
			
			if ($group_count ['count'] < 4) {
				$gid_map = ' AND id NOT IN (' . implode ( ',', $not_in_gids ) . ') ';
				$_count = $this->where ( 'status=1 AND is_del=0 ' . $gid_map )->count ();
				$rand_list = $this->field ( 'id,name,logo,membercount,ctime' )->where ( 'status=1 AND is_del=0 ' . $gid_map )->limit ( (rand ( 0, $_count - (4 - $group_count ['count']) )) . ',' . (4 - $group_count ['count']) )->findAll ();
				foreach ( $rand_list as $v ) {
					$v ['reason'] = '随机推荐';
					$group_list ['data'] [] = $v;
				}
			}
		}
		return $group_list;
	}
	
	/**
	 * 梦想中心热门排行
	 *
	 * @param unknown_type $reset
	 *        	是否重设缓存
	 * @return unknown
	 */
	public function getHotList($reset = false) {
		// 1分钟锁缓存
		if (! ($cache = S ( 'Cache_Group_Hot_list' )) || $reset) {
			S ( 'Cache_Group_Hot_list_t', time () ); // 缓存未设置 先设置缓存设定时间
		} else {
			if (! ($cacheSetTime = S ( 'Cache_Group_Hot_list_t' )) || $cacheSetTime + 60 <= time ()) {
				S ( 'Cache_Group_Hot_list_t', time () ); // 缓存未设置 先设置缓存设定时间
			} else {
				return $cache;
			}
		}
		// 缓存锁结束
		
		$today = mktime ( 0, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) );
		$yesterday = $today - 24 * 3600;
		$gids ['by_new_weibo'] = D ( 'GroupWeibo', 'group' )->field ( 'gid' )->where ( "ctime>{$yesterday} AND ctime<{$today} AND isdel=0" )->group ( 'gid' )->order ( 'count(gid) DESC' )->limit ( 20 )->findAll ();
		
		$a_week_ago = $today - 7 * 24 * 3600;
		$gids ['by_new_member'] = D ( 'Member', 'group' )->field ( 'gid' )->where ( "ctime>{$a_week_ago} AND ctime<{$today} AND level>1" )->group ( 'gid' )->order ( 'count(gid) DESC' )->limit ( 20 )->findAll ();
		
		$gids ['by_member_count'] = $this->field ( 'id AS gid' )->where ( 'brower_level=-1 AND status=1 AND is_del=0' )->order ( 'membercount DESC' )->limit ( 20 )->findAll ();
		
		foreach ( $gids as $v ) {
			foreach ( ( array ) $v as $_v ) {
				$_gids [] = $_v ['gid'];
			}
		}
		
		// 新微博数权值系数、统计
		$gid_map = $_gids ? ' AND gid IN (' . implode ( ',', $_gids ) . ') ' : '';
		$factor ['new_weibo_count'] = 2;
		$count ['new_weibo_count'] = D ( 'GroupWeibo', 'group' )->field ( 'gid,count(gid) as new_weibo_count' )->where ( "ctime>{$yesterday} AND ctime<{$today} AND isdel=0 " . $gid_map )->group ( 'gid' )->findAll ();
		
		// 新成员数权值系数、统计
		$factor ['new_member_count'] = 3;
		$count ['new_member_count'] = D ( 'Member', 'group' )->field ( 'gid,count(gid) as new_member_count' )->where ( "ctime>{$a_week_ago} AND ctime<{$today} AND level>1 " . $gid_map )->group ( 'gid' )->findAll ();
		
		// 成员数权值系数、统计
		$gid_map = $_gids ? ' AND id IN (' . implode ( ',', $_gids ) . ') ' : '';
		$factor ['membercount'] = 3;
		$count ['membercount'] = $hot_list = $this->field ( 'id,id AS gid,name,logo,membercount,ctime' )->where ( 'status=1 AND is_del=0 ' . $gid_map )->findAll ();
		
		// 计算权值
		foreach ( $count as $k => $v ) {
			foreach ( ( array ) $v as $_v ) {
				$weight [$_v ['gid']] += $_v [$k] * $factor [$k];
			}
		}
		
		// 根据权值倒序排列
		$group_num = count ( $hot_list );
		for($i = 0; $i < $group_num; $i ++) {
			$hot_list [$i] ['weight'] = $weight [$hot_list [$i] ['gid']];
			for($j = $i; $j > 0; $j --) {
				if ($hot_list [$j] ['weight'] > $hot_list [$j - 1] ['weight']) {
					$_temp = $hot_list [$j];
					$hot_list [$j] = $hot_list [$j - 1];
					$hot_list [$j - 1] = $_temp;
				} else {
					break;
				}
			}
		}
		
		// 返回前十热门
		$data = array_slice ( $hot_list, 0, 10 );
		S ( 'Cache_Group_Hot_list', $data );
		return $data;
	}
	
	// 最新话题
	function getnewtopic($uid) {
		$gidarr = D ( 'Member' )->field ( 'gid' )->where ( 'uid=' . $uid )->findAll ();
		if ($gidarr) {
			$in = 'gid IN ' . render_in ( $gidarr, 'gid' );
			return D ( 'Topic' )->where ( "is_del=0 AND " . $in )->order ( 'replytime DESC' )->findPage ();
		}
		return false;
	}
	
	// 获取梦想中心动态
	function getGroupFeed($gid, $appid, $pageLimit = 6) {
		$gid = intval ( $gid );
		// $map = "type!= 'create_group' AND appid={$appid} AND fid={$gid} ";
		// $map['type'] = array('neq','create_group');
		// $map['type'] = array();
		
		// return $this->api->Feed_getApp($appid,'',$pageLimit,array('group_create'),'','',$gid);
	}
	
	// 获取我所在梦想中心的动态
	function getMyJoinGroup($uid, $appid) {
		$feedList = array ();
		$joinGroup = D ( 'Member' )->field ( 'gid' )->where ( 'uid=' . $uid . " AND level != 0 " )->findPage ();
		
		if ($joinGroup ['data']) {
			foreach ( $joinGroup ['data'] as $k => $v ) {
				$feedList [$k] ['gid'] = $v ['gid'];
				$feedList [$k] ['feed'] = $this->getGroupFeed ( $v ['gid'], $appid, 6 );
			}
			$joinGroup ['data'] = $feedList;
		}
		return $joinGroup;
	}
	
	/**
	 * getGroupList
	 */
	public function getGroupList($html = 1, $map = array(), $fields = null, $order = null, $limit = null, $isDel = 0) {
		// 处理where条件
		if (! $isDel)
			$map [] = 'is_del=0';
		else
			$map [] = 'is_del=1';
		$map = implode ( ' AND ', $map );
		
		$function_find = $html ? 'findPage' : 'findAll';
		// 连贯查询.获得数据集
		$result = $this->where ( $map )->field ( $fields )->order ( $order )->$function_find ( $limit );
		
		return $result;
	}
	
	// 回收站 梦想中心，话题，文件，相册，话题回复
	public function remove($id) {
		$id = is_array ( $id ) ? '(' . implode ( ',', $id ) . ')' : '(' . $id . ')'; // 判读是不是数组回收
		
		$uids = D ( 'Group', 'group' )->field ( 'uid' )->where ( 'id IN ' . $id )->findAll (); // 创建者ID
		$res = D ( 'Group', 'group' )->setField ( 'is_del', 1, 'id IN ' . $id ); // 回收梦想中心
		if ($res) {
			// 删除成员
			D ( 'Member', 'group' )->where ( 'gid IN ' . $id )->delete (); // 删除成员
			                                                               // 删除成员
			D ( 'GroupTag', 'group' )->where ( 'gid IN ' . $id )->delete (); // 删除标签
			                                                                 // 回收微博
			D ( 'GroupWeibo', 'group' )->setField ( 'isdel', 1, 'gid IN' . $id ); // 回收微博
			D ( 'WeiboAtme', 'group' )->where ( 'gid IN ' . $id )->delete (); // 回收微博@TA 的
			D ( 'WeiboComment', 'group' )->setField ( 'isdel', 1, 'gid IN' . $id ); // 回收微博评论
			                                                                        // D('WeiboFavorite')->where('gid IN ' . $id)->delete(); //回收微博评论
			D ( 'WeiboTopic', 'group' )->where ( 'gid IN ' . $id )->delete (); // 回收微博帖子
			                                                                   // 回收帖子和文件
			D ( 'Topic', 'group' )->setField ( 'is_del', 1, 'gid IN' . $id ); // 回收话题
			D ( 'Post', 'group' )->setField ( 'is_del', 1, 'gid IN' . $id ); // 回收话题回复
			D ( 'Dir', 'group' )->setField ( 'is_del', 1, 'gid IN' . $id ); // 文件回收
			$dirList = D ( 'Dir', 'group' )->field ( 'attachId' )->where ( 'gid IN' . $id )->findAll ();
			
			if ($dirList) {
				$attachIds = array ();
				foreach ( $dirList as $k => $v ) {
					$attachIds [] = $v ['attachId'];
				}
				
				model ( 'Attach' )->deleteAttach ( $attachIds, true );
				unset ( $attachIds );
				unset ( $dirList );
			}
			
			D ( 'Album', 'group' )->setField ( 'is_del', 1, 'gid IN' . $id ); // 相册回收
			
			D ( 'Photo', 'group' )->setField ( 'is_del', 1, 'gid IN' . $id ); // 图片回收
			$photoList = D ( 'Photo', 'group' )->field ( 'attachId' )->where ( 'gid IN' . $id )->findAll ();
			if ($photoList) {
				$attachIds = array ();
				foreach ( $photoList as $k => $v ) {
					$attachIds [] = $v ['attachId'];
				}
				model ( 'Attach' )->deleteAttach ( $attachIds, true );
				unset ( $attachIds );
				unset ( $photoList );
			}
			// 积分操作
			foreach ( $uids as $vo ) {
				X ( 'Credit' )->setUserCredit ( $vo ['uid'], 'delete_group' );
			}
			S ( 'Cache_MyGroup_' . $this->mid, null );
		}
		
		return $res;
	}
	// 创建课表model
	public function SaveClassName($post, $group_id) {
		$map ['create_time'] = time ();
		$map ['group_id'] = $group_id;
		
		$map ['start_time'] = $post ['starttime'];
		$map ['close_time'] = $post ['closetime'];
		$map ['integral'] = $post ['integral'];
		$map ['studenttotal'] = $post ['studenttotal'];
		$map ['classtotal'] = $post ['classtotal'];
		$map ['studentaverage'] = $_POST ['studentaverage'];
		$map ['usertotal'] = $post ['usertotal'];
		$map ['semester'] = $post ['semester'];
		$map ['maxman'] = 35;
		$map ['uid'] = $this->uid;
		if ($map ['semester'] == 1) {
			$semester = "秋季";
		} else {
			$semester = "春季";
		}
		$className = date ( 'Y', strtotime ( $post ['starttime'] ) ) . $semester . "课表";
		// $map['name'] = t($post['classname']);
		$map ['name'] = $className;
		$res = D ( 'group_classname' )->add ( $map );
		return $res;
	}
	// 保存老师选课
	public function doselectTable($post, $gid, $courid, $uid) {
		$map ['uid'] = $uid;
		$map ['gid'] = $gid;
		$map ['cour_id'] = $courid;
		$map ['class_cour'] = t ( $post ['classCour'] );
		$map ['class_name'] = intval ( $post ['className'] );
		$map ['class_time'] = t ( $post ['classtime'] );
		$map ['create_time'] = time ();
		$map ['gid'] = $gid;
		$map ['class_area'] = intval ( $post ['classarea'] );
		$map ['class_table_id'] = intval ( $post ['classId'] );
		$where = "uid = $uid and class_table_id = " . intval ( $post ['classId'] ) . " and ( class_name = " . intval ( $post ['className'] ) . " and cour_id = $courid and class_cour = " . $map ['class_cour'] . ")";
		$select = D ( "group_selectclass" )->where ( $where )->find ();
		
		if ($select) {
			$res = D ( 'group_selectclass' )->save ( $map );
		} else {
			$res = D ( 'group_selectclass' )->add ( $map );
		}
		return $res;
	}
	// 删除文件
	/*
	 * public function del($id) { $id = in_array($id) ? '('.implode(',',$id).')' : '('.$id.')'; //判读是不是数组回收 D('Group')->where('id IN'.$id)->delete(); //删除梦想中心 D('Topic')->where('gid IN'.$id)->delete(); //回收话题 D('Post')->where('gid IN'.$id)->delete(); //回收话题回复 D('Dir')->where('gid IN'.$id)->delete(); //文件回收 删除文件unlink D('Album')->where('gid IN'.$id)->delete(); D('Photo')->where('gid IN'.$id)->delete(); //图片回收 }
	 */
	/**
	 *获得学校信息
	 * @param unknown $uid
	 * @return string
	 */
	public function getSchoolInfo($uid) {
		$school=$this->join ( 'as s inner join ts_user_verified uv on uv.sid=s.id and uv.type=1 left join ts_area a1 on s.province=a1.area_id left join ts_area a2 on s.city=a2.area_id left join ts_area a3 on s.area=a3.area_id' )->field ( 's.*,a1.title as prov,a2.title as city,a3.title as area' )->where ( 'uv.uid=' . $uid)->find ();
		if($school){
			$admin=$this->query('select uv1.uid,uv1.realname from ts_user_verified uv1 inner join ts_user_group_link gl on uv1.uid=gl.uid and user_group_id='.C('USER_GROUP_DREAM_ADMIN').' where uv1.sid='.$school['id'].' limit 1');
			$school['admin_uid']=$admin[0]['uid'];
			$school['admin_name']=$admin[0]['realname'];
		}
		return $school;
	}
	/**
	 * 获得指定学校的前limit个教师信息，教师按照教师级别、注册时间降序排列
	 * @param number $sid 学校ID
	 * @param number $limit 数量
	 */
	public function getSchoolTeachers($sid,$limit=20){
		$map['uv.sid']=$sid;
		$map['uv.type']=0;
		$map['uv.verified']='1';
		return M('UserVerified')->join('as uv left join ts_user u on uv.uid=u.uid')->field('u.uid,u.uname as realname,uv.level')->order('uv.level desc,u.ctime desc')->where($map)->limit($limit)->select();
	}
	
	public function getSchoolByArea($ids = array()) {
		if ($ids ['province']) {
			$schools = M ( 'School' )->where ( $ids )->field ( 'id,name,first_letter as letter' )->findAll ();
		}
		return $schools;
	}
	
	public function getSchoolDetail($sid=0){
		$school=$this->join ( 'as s left join ts_area a1 on s.province=a1.area_id left join ts_area a2 on s.city=a2.area_id left join ts_area a3 on s.area=a3.area_id left join ts_db_school_category scate on s.cid0=scate.id left join ts_db_school_nature snat on s.school_type=snat.id left join ts_db_educational_nature et on s.educational_type=et.id' )->field ( 's.*,(select group_concat(uv1.realname) from ts_user_verified uv1 inner join ts_user_group_link gl on uv1.uid=gl.uid and user_group_id='.C('USER_GROUP_DREAM_ADMIN').' where uv1.sid=s.id) as admin,a1.title as provName,a2.title as cityName,a3.title as areaName,scate.title as cname,snat.name as school_type_name,et.name as educational_type_name' )->where ( 's.id=' . $sid)->find ();
		$map['sid']=$sid;
		$map['type']='1';
		$school_uid=M('UserVerified')->where($map)->getField('uid');
		if($school_uid){
			$school['avatar_url']=getUserFace($school_uid, 'b');
		}
		if($school){
			$school['applytime']=date('Y年m月d日',$school['applytime']);
			$school['assetstime']=date('Y年m月d日',$school['assetstime']);
			$school['accepttime']=date('Y年m月d日',$school['accepttime']);
			$school['completetime']=date('Y年m月d日',$school['completetime']);
		}
		return $school;
	}
	/**
	 * 获得学校管理员备选列表
	 */
	public function getSchoolAdminList($sid=0){
		$model=M('UserVerified');
		$map['sid']=$sid;
		$map['verified']='1';
		$map['gl.user_group_id']=C('USER_GROUP_DREAM_ADMIN');
		//获得原来的管理员UID
		$old_uid=$model->join('as uv left join ts_user_group_link gl on uv.uid=gl.uid')->where('uv.sid='.$sid.' and gl.user_group_id='.C('USER_GROUP_DREAM_ADMIN'))->field('uv.uid')->select();
		$old_uid=getSubByKey($old_uid,'uid');
		unset($map['gl.user_group_id']);
		$map['type']=array('neq','1');
		if($old_uid){
			//排除原来的管理员
			$map['uid']=array('not in',implode($old_uid, ','));
		}
		return $model->field('uid as id,realname as name')->where($map)->select();
	}
}