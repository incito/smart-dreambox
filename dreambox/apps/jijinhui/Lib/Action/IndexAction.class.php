<?php
// 相册应用 - indexaction 图片和专辑的列表
class IndexAction extends BaseAction {
	public function _initialize() {
		parent::_initialize ();
		$IsHotList = IsHotList ();
		$this->assign ( 'IsHotList', $IsHotList );
	}
	/**
	 * 所有公告展示
	 */
	public function index() {
		$left = D ( "jijunhui_left" )->findAll ();
		$gid = intval ( $_GET ['id'] );
		$gid = empty ( $gid ) ? $left [0] ['id'] : $gid;
		$page = intval ( $_GET ['page'] );
		$page = empty ( $page ) ? 1 : $page;
		$start = ($page - 1) * 8;
		// 需要按权限细分
		// 获取当前用户权限组
		if ($this->mid != 0) {
			$ids = M ()->query ( 'select user_group_id from ts_user_group_link where uid=' . $this->mid );
			$temp = "(";
			foreach ( $ids as $id ) {
				$temp .= ($id ['user_group_id'] . ',');
			}
			$temp .= '2)';
			$countSql = 'SELECT DISTINCT(c.id),c.title,c.create_time FROM ts_jijunhui_content c INNER JOIN ts_jijinhui_group g ON c.id=g.content_id and g.group_id in' . $temp . ' and c.gid=' . $gid ;
			$sql = 'SELECT DISTINCT(c.id),c.title,c.create_time FROM ts_jijunhui_content c INNER JOIN ts_jijinhui_group g ON c.id=g.content_id and g.group_id in' . $temp . ' and c.gid=' . $gid . ' ORDER BY c.sort asc LIMIT ' . $start . ',8';
		} else {
			$countSql = 'SELECT DISTINCT(c.id),c.title,c.create_time FROM ts_jijunhui_content c INNER JOIN ts_jijinhui_group g ON c.id=g.content_id and g.group_id=2  and c.gid=' . $gid;
			$sql = 'SELECT DISTINCT(c.id),c.title,c.create_time FROM ts_jijunhui_content c INNER JOIN ts_jijinhui_group g ON c.id=g.content_id and g.group_id=2  and c.gid=' . $gid . ' ORDER BY c.sort asc LIMIT ' . $start . ',8';
		}
		$countResult = M()->query($countSql);
		$content = M ()->query ( $sql );
		$this->assign ( "left", $left );
		$this->assign ( "content", $content );
		$this->assign ( "gid", $gid );
		$this->assign ( "count", count ( $countResult ) );
		// 处理分页信息
		$count = ceil ( count ( $countResult ) / 8 );
		$this->assign ( "pageCount", $count );
		$page = min(array($count,$page));
		$page = max(array(1,$page));
		$pages = array ();
		if ($count < 5) {
			for($i = 1; $i <= $count; $i ++) {
				$pages [] = $i;
			}
		} else {
			$temp = ($count - $page) > 2 ? 2 : ($count - $page);
			$pre = 4 - $temp;
			for($i = $page - $pre; $i <= $page; $i ++) {
				if ($i > 0) {
					$pages [] = $i;
				}
			}
			for($i = $page + 1; $i <= $page + 5; $i ++) {
				if ($i > $count) {
					break;
				}
				if (count ( $pages ) >= 4) {
					break;
				}
				if (! in_array ( $i, $pages )) {
					$pages [] = $i;
				}
			}
			if (! in_array ( $count, $pages )) {
				$pages [] = $count;
			}
		}
	
		$this->assign ( "page", $page );
		$this->assign ( "pages", $pages );
		$this->display ();
	}
	/**
	 * 单独公告展示
	 */
	public function descindex() {
		$left = D ( "jijunhui_left" )->findAll ();
		
		$this->assign ( "left", $left );
		
		$id = intval ( $_GET ['id'] );
		$desc = D ( "jijunhui_content" )->where ( "id = $id" )->find ();
		$this->assign ( "content", $desc );
		$this->display ();
	}
}
?>