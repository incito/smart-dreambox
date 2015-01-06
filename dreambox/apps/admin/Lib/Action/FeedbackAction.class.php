<?php

/**
 * 签到后台管理
 * @author zjj
 *
 */
// 加载后台控制器
tsload(APPS_PATH . '/admin/Lib/Action/AdministratorAction.class.php');

class FeedbackAction extends AdministratorAction
{

    /**
     * 初始化
     */
    public function _initFeedback ()
    {
        $this->pageTitle['index'] = '签到管理';
        // tab选项
        $this->pageTab[] = array(
                'title' => '签到流水',
                'tabHash' => 'index',
                'url' => U('admin/Feedback/index')
        );
        $this->pageTab[] = array(
                'title' => '签到配置',
                'tabHash' => 'config',
                'url' => U('admin/Feedback/config')
        );
    }
    
    /**
     * 初始化异常统计页面
     */
    public function _initStat ()
    {
        $this->pageTitle['index'] = '签到异常统计';
        // tab选项
        $this->pageTab[] = array(
                'title' => '一个老师超过2门课',
                'tabHash' => 'unusual_1',
                'url' => U('admin/Feedback/unusualStat')
        );
        $this->pageTab[] = array(
                'title' => '一个班超过1门课',
                'tabHash' => 'unusual_2',
                'url' => U('admin/Feedback/unusualStat_2')
        );
        $this->pageTab[] = array(
                'title' => '一个老师超过2个班',
                'tabHash' => 'unusual_3',
                'url' => U('admin/Feedback/unusualStat_3')
        );
        $this->pageTab[] = array(
                'title' => '一个班一周超过1节课',
                'tabHash' => 'unusual_4',
                'url' => U('admin/Feedback/unusualStat_4')
        );
        $this->pageTab[] = array(
                'title' => '一节课超过1个梦想课时',
                'tabHash' => 'unusual_5',
                'url' => U('admin/Feedback/unusualStat_5')
        );
        $this->pageTab[] = array(
                'title' => '一个班上同一课时超过一次',
                'tabHash' => 'unusual_6',
                'url' => U('admin/Feedback/unusualStat_6')
        );
        $this->pageTab[] = array(
                'title' => '上课班级超过实际班级数',
                'tabHash' => 'unusual_7',
                'url' => U('admin/Feedback/unusualStat_7')
        );
    }

    /**
     * 签到流水
     */
    public function index ()
    {
        $this->_initFeedback();
        
        $_REQUEST['tabHash'] = 'index';
        
        $this->pageKeyList = array(
                'id',
                'realname',
                'school_name',
                'term_id',
//                 'week_num',
                'course_name',
                'hours_name',
                'grade_name',
                'class_name',
                'section_num',
                'lesson_time',
                'create_time',
                'client',
                'audit_status'
        );
        
        $this->searchKey = array(
                'realname',
                'school_name',
                'term_id',
                'course_name',
                array(
                        'create_time',
                        'create_time1'
                )
        );
        // 全部课程
        $categoryList = M('db_course')->getHashList('id', 'class_name');
        $categoryList[0] = L('PUBLIC_SYSTEMD_NOACCEPT');
        ksort($categoryList);
        $this->opt['course_name'] = $categoryList;
        // 查询列表数据
        $listData = M("LessonFeedback")->search();
        
        $this->pageButton[] = array(
                'title' => '搜索',
                'onclick' => "admin.fold('search_form')"
        );
        
        $this->displayList($listData);
    }

    /**
     * 超时配置
     */
    public function config ()
    {
        $this->_initFeedback();
        
        $_REQUEST['tabHash'] = 'config';
        
        $this->pageKeyList = array(
                'expired_day'
        );
        
        // 表单URL设置
        $this->savePostUrl = U('admin/Feedback/saveConfig');
        $this->notEmpty = array(
                'expired_day'
        );
        $this->onsubmit = 'admin.configFeedbackCheck(this)';
        
        $condition['key_name'] = 'expired_day';
        $data = M("db_system_config")->where($condition)
            ->limit(1)
            ->getField('value');
        
        $this->displayConfig(array(
                'expired_day' => $data
        ));
    }

    /**
     * 保存配置
     */
    public function saveConfig ()
    {
        if (isset($_POST['expired_day'])) {
            $data['value'] = $_POST['expired_day'];
            $condition['key_name'] = 'expired_day';
            $id = M("db_system_config")->where($condition)
                ->limit(1)
                ->getField('id');
            if ($id) {
                M("db_system_config")->where($condition)
                    ->data($data)
                    ->save();
            } else {
                $data['key'] = 'expired_day';
                $data['description'] = '签到过期天数，0表示永不过期';
                M("db_system_config")->add($data);
            }
        }
        $this->success('修改成功！');
    }
    
    /**
     * 一个老师超过2门课
     */
    public function unusualStat(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_1';
        
        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'realname',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_1_export' ) . '\')'
        );
        
        $listData = M("FeedbackAdmin")->unusualStat_1();
        $this->displayList($listData);
    }
    
    public function unusualStat_2(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_2';

        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'grade_class',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_2_export' ) . '\')'
        );
        
        $listData = M("FeedbackAdmin")->unusualStat_2();
        $this->displayList($listData);
    }
    
    public function unusualStat_3(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_3';

        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'realname',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_3_export' ) . '\')'
        );
        
        $listData = M("FeedbackAdmin")->unusualStat_3();
        $this->displayList($listData);
    }
    
    public function unusualStat_4(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_4';
    
        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'grade_class',
                'week_num',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_4_export' ) . '\')'
        );
    
        $listData = M("FeedbackAdmin")->unusualStat_4();
        $this->displayList($listData);
    }
    
    public function unusualStat_5(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_5';
        
        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'grade_class',
                'date',
                'realname',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_5_export' ) . '\')'
        );
        
        $listData = M("FeedbackAdmin")->unusualStat_5();
        $this->displayList($listData);
    }
    
    public function unusualStat_6(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_6';
        
        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'grade_class',
                'hours_name',
                'realname',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_6_export' ) . '\')'
        );
        
        $listData = M("FeedbackAdmin")->unusualStat_6();
        $this->displayList($listData);
    }
    
    public function unusualStat_7(){
        $this->_initStat();
        $_REQUEST['tabHash'] = 'unusual_7';
        
        $this->pageKeyList = array(
                'term_name',
                'school_name',
                'class_name',
                'class_count',
                'num'
        );
        $this->pageButton [] = array (
                'title' => '导出excel',
                'onclick' => 'admin.exportExcel(\'' . U ( 'admin/Feedback/unusualStat_7_export' ) . '\')'
        );
        
        $listData = M("FeedbackAdmin")->unusualStat_7();
        $this->displayList($listData);
    }


    public function  unusualStat_1_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_1_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'realname' => '老师真实姓名',
                'num' => '课程数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[0]['title'] );
    }


    public function  unusualStat_2_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_2_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'grade_class' => '班级',
                'num' => '课程数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[1]['title'] );
    }


    public function  unusualStat_3_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_3_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'realname' => '老师真实姓名',
                'num' => '班级数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[2]['title'] );
    }


    public function  unusualStat_4_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_4_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'grade_class' => '班级',
                'week_num' => '第几周',
                'num' => '课时数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[3]['title'] );
    }


    public function  unusualStat_5_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_5_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'grade_class' => '班级',
                'date' => '上课时间',
                'realname' => '老师真实姓名',
                'num' => '课时数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[4]['title'] );
    }


    public function  unusualStat_6_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_6_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'grade_class' => '班级',
                'hours_name' => '梦想课程',
                'realname' => '老师真实姓名',
                'num' => '上课次数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[5]['title'] );
    }
    
    public function  unusualStat_7_export(){
        $this->_initStat();
        $listData = M("FeedbackAdmin")->unusualStat_7_export();
        // 载入Excel操作类
        require_once ADDON_PATH . '/library/Excel.class.php';
        $excel = new Excel ();
        $top = array (
                'term_name' => '学期',
                'school_name' => '学校名称',
                'class_name' => '梦想课程',
                'class_count' => '实际班级数',
                'num' => '上课班级数'
        );
        $excel->exportCsv ( $listData, $top, $this->pageTab[6]['title'] );
    }
}
