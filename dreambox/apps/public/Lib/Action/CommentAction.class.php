<?php
/**
 * 我的评论/消息/通知 控制器
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class CommentAction extends Action {
	
	/**
	 * 站内消息页面
	 */
	public function index() {
		$type = t ( $_GET ['type'] );
		// 默认进入评论页面
		! $type && $type = 'comment';
		$this->assign ( 'type', $type );
		$this->display ();
	}
	/**
	 * 评论信息
	 */
	public function comment() {
		M ( 'UserCount' )->resetUserCount ( $this->mid, 'unread_comment', 0 );
		$m = new Model ();
		$list = $m->query ( 'SELECT t.blog_id,t.id,t.uid,t.mid,t.comment,t.ctime FROM( SELECT * FROM ts_blog_comment bc where (bc.uid=' . $this->mid . ' or bc.mid=' . $this->mid . ') and type=3 ) as t group by blog_id,id order by ctime DESC' );
		// 对查询结果按照blog_id分组
		$res = array ();
		foreach ( $list as &$v ) {
			$blog_id = $v ['blog_id'].'__';
			if (! $res [$blog_id]) {
				$res [$blog_id] = array ();
			}
			$v['ctime']=date('Y年m月d日 H:i:s',$v['ctime']);
			$userInfo = model ( 'User' )->getUserInfo ( $v ['mid'] );
			$user ['uid'] = $v ['mid'];
			$user ['uname'] = $userInfo ['uname'];
			$user ['avatar_url'] = $userInfo ['avatar_small'];
			$v ['sender'] = $user;
			array_push ( $res [$blog_id], $v );
		}
		exit(json_encode($res));
	}
	/**
	 * 评论列表（平铺）
	 */
	public function commentList(){
		M ( 'UserCount' )->resetUserCount ( $this->mid, 'unread_comment', 0 );
		$list=$m = M('blog_comment')->where('(uid=' . $this->mid . ' or mid=' . $this->mid . ') and type=3')->order('ctime desc')->findPage(20);
		//处理时间、头像
		foreach ( $list['data'] as &$v ) {
			$v['ctime']=date('Y年m月d日 H:i:s',$v['ctime']);
			$userInfo = model ( 'User' )->getUserInfo ( $v ['mid'] );
			$user ['uid'] = $v ['mid'];
			$user ['uname'] = $userInfo ['uname'];
			$user ['avatar_url'] = $userInfo ['avatar_small'];
			$v ['sender'] = $user;
		}
		exit(json_encode($list));
	}
	/**
	 * 我的评论中，回复弹窗页面
	 */
	public function reply() {
		$var = $_GET;
		foreach ( $var as $k => $v ) {
			$var [$k] = h ( $v );
		}
		$var ['initNums'] = model ( 'Xdata' )->getConfig ( 'weibo_nums', 'feed' );
		$var ['commentInfo'] = model ( 'Comment' )->getCommentInfo ( intval ( $var ['comment_id'] ), false );
		$var ['canrepost'] = $var ['commentInfo'] ['table'] == 'feed' ? 1 : 0;
		$var ['cancomment'] = 1;
		// 获取原作者信息
		$rowData = model ( 'Feed' )->get ( intval ( $var ['commentInfo'] ['row_id'] ) );
		$appRowData = model ( 'Feed' )->get ( intval ( $rowData ['app_row_id'] ) );
		$var ['user_info'] = $appRowData ['user_info'];
		// 微博类型
		$var ['feedtype'] = $rowData ['type'];
		// $var['cancomment_old'] = ($var['commentInfo']['uid'] != $var['commentInfo']['app_uid'] && $var['commentInfo']['app_uid'] != $this->uid) ? 1 : 0;
		$var ['initHtml'] = L ( 'PUBLIC_STREAM_REPLY' ) . '@' . $var ['commentInfo'] ['user_info'] ['uname'] . ' ：'; // 回复
		
		$this->assign ( $var );
		$this->display ();
	}
}