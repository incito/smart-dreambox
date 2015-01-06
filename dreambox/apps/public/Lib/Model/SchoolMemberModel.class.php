<?php
class SchoolMemberModel extends Model
{
	var $tableName = 'db_school_member';

	function getNewMemberList($gid, $limit = 4)
	{
		$gid = intval($gid);
		$new_member_list = $this->field('id,uid,level,ctime')->where("gid={$gid} AND level>1")->order('ctime DESC')->limit($limit)->findAll();
		return $new_member_list;
	}

	function memberCount($gid)
	{
		return $this->where("gid=".$gid)->count();
	}
}
