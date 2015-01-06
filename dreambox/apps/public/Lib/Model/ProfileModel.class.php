<?php

/**
 * 个人主页业务逻辑
 * @author zjj
 *
 */
class ProfileModel extends Model
{

    /**
     * 地区信息
     * 
     * @param unknown $uid            
     * @return unknown
     */
    public function baseInfo ($uid)
    {
        $user = M('User')->getUserInfo($uid);
        
        $data['province'] = M('area')->where('area_id=' . $user['province'])->getField(
                'title');
        $data['city'] = M('area')->where('area_id=' . $user['city'])->getField(
                'title');
        $data['area'] = M('area')->where('area_id=' . $user['area'])->getField(
                'title');
        
        return $data;
    }

    /**
     * 星座
     * 
     * @param unknown $uid            
     */
    public function getConstellation ($birthday)
    {
        if (! empty($birthday)) {
            $day = date('m.d', strtotime($birthday));
            $data = M('db_constellation')->select();
            foreach ($data as $con) {
                if ($con['name'] == '魔羯座') {
                    if ($day >= $con['start_date'] || $day <= $con['end_date']) {
                        return $con['name'];
                    }
                } else {
                    if ($day >= $con['start_date'] && $day <= $con['end_date']) {
                        return $con['name'];
                    }
                }
            }
        }
        return "";
    }
    
    /**
     * 获取访客的数量
     * 
     * @param unknown $uid            
     */
    public function getVisitedCount ($uid)
    {
        $data = M('user_visited')->where("fid=$uid")->count();
        return (int)$data;
    }
    
    /**
     * 记录访客
     * @param unknown $mid
     * @param unknown $uid
     */
    public function addVisitRecord ($mid, $uid)
    {
        $condition['uid'] = $mid;
        $condition['fid'] = $uid;
        
        $data['uid'] = $mid;
        $data['fid'] = $uid;
        $data['ctime'] = time();
        
        $exist = M('user_visited')->where($condition)->find();
        if ($exist) {
            M('user_visited')->where($condition)->save($data);
        } else {
            M('user_visited')->add($data);
        }
        // M()->execute("replace into ".C('DB_PREFIX')."user_visited
    // (`uid`,`fid`,`ctime`) VALUES ('".$mid."','".$uid."',".time().")");
    }
    
    /**
     * 博客转载、喜欢、评论
     * @param unknown $data
     * @param unknown $type
     */
    public function addBlogComment($data, $type)
    {
        $this->startTrans();//事务开始
        try{
            $status = M('blog_comment')->add($data);
            if(!$status){
                $this->rollback();
                return -1;
            }
    
            $field = "";
            $condition['id'] = $data['blog_id'];
            //转载
            if($type == 1){
                $field = "republishCount";
                $blog_data = M('blog')->where($condition)->find();
                $add_data['title'] = $blog_data['title'];
                $add_data['category'] = $blog_data['category'];
                $add_data['category_title'] = $blog_data['category_title'];
                $add_data['cover'] = $blog_data['cover'];
                $add_data['content'] = $blog_data['content'];
                
                $add_data['uid'] = $data['mid'];
                $add_data['status'] = 1;
                $add_data['cTime'] = time();
                $add_data['mTime'] = time();
                $add_data['ref_id'] = $data['blog_id'];
                $status = M('blog')->add($add_data);
                if(!$status){
                    $this->rollback();
                    return -1;
                }
            }else if($type == 2){
                $field = "likeCount";
            }else if($type == 3){
                $field = "commentCount";
                //发通知给相关的用户
                $to[] = $data ['uid'];
                if(!empty($data ['author'])){
                    $to[] = $data ['author'];
                }
    //             $author = model('User')->getUserInfo($data['mid']);
    //             $config['name'] = $author['uname'];
    //             $config['space_url'] = $author['space_url'];
    //             $config['face'] = $author['avatar_small'];
    //             $config['content'] = $data['content'];
    //             $config['ctime'] = date('Y-m-d H:i:s',$data['mtime']);
    //             model('Notify')->sendNotify($to, 'comment', $config);
                //记录未读评论条数
                foreach ($to as $uid)
                {
                    model('UserData')->updateKey('unread_comment', 1, true, $uid);
                }
            }
            $status = M('blog')->setInc($field, $condition, $step=1);
            if(!$status){
                $this->rollback();
                return -1;
            }
    		$this->commit ();//提交事务
    		return M('blog')->where($condition)->getField($field);
		}
		//捕获异常
		catch(Exception $e)
		{
		    $this->rollback ();
            return -1;
		}
    }
}
?>