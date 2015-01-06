<?php
class FeedbackAdminModel extends Model {
    

    protected $tableName = 'db_lesson_feedback';

    private function getUnusualStat_1_sql(){
        return "SELECT term_name, school_name, realname, count(DISTINCT course_id) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, v.realname, t.course_id, t.user_id FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, user_id HAVING num > 2 ORDER BY term_name, school_name";
    }
    
    /**
     * 一个老师超过2门课
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_1(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT count(DISTINCT course_id) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, v.realname, t.course_id, t.user_id FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, user_id HAVING num > 2) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
        
        $result = $this->findPageBySql($this->getUnusualStat_1_sql(), $count, 100);

        return $result;
    }
    
    public function unusualStat_1_export(){
        $sql = $this->getUnusualStat_1_sql();
        return $this->query($sql);
    }


    private function getUnusualStat_2_sql(){
        return "SELECT term_name, school_name, grade_class, count(DISTINCT course_id) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, concat( grade. NAME, t.class_num, ' 班' ) AS grade_class, t.course_id FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, grade_class HAVING num > 1 ORDER BY term_name, school_name, grade_class";
    }
    
    /**
     * 一个班超过1门课
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_2(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT count(DISTINCT course_id) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, concat( grade. NAME, t.class_num, ' 班' ) AS grade_class, t.course_id FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, grade_class HAVING num > 1) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
        
        $result = $this->findPageBySql($this->getUnusualStat_2_sql(), $count, 100);

        return $result;
    }
    
    public function unusualStat_2_export(){
        $sql = $this->getUnusualStat_2_sql();
        return $this->query($sql);
    }


    private function getUnusualStat_3_sql(){
        return "SELECT term_name, school_name, realname, count(DISTINCT grade_id, class_num) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, v.realname, t.grade_id, t.class_num FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, realname HAVING num > 2 ORDER BY term_name, school_name";
    }
    /**
     * 一个老师给2个以上的班级上课
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_3(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT count(DISTINCT grade_id, class_num) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, v.realname, t.grade_id, t.class_num FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, realname HAVING num > 2) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
    
        $result = $this->findPageBySql($this->getUnusualStat_3_sql(), $count, 100);
    
        return $result;
    }
    
    public function unusualStat_3_export(){
        $sql = $this->getUnusualStat_3_sql();
        return $this->query($sql);
    }
    
    private function getUnusualStat_4_sql(){
        return "SELECT term_name, school_name, grade_class, CONCAT('第', week_num, '周') AS week_num, count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, concat( grade. NAME, t.class_num, ' 班' ) AS grade_class, t.week_num FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, grade_class, week_num HAVING num > 1 ORDER BY term_name, school_name, grade_class";
    }
    
    /**
     * 一个班一周超过1节梦想课
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_4(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, concat( grade. NAME, t.class_num, ' 班' ) AS grade_class, t.week_num FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON s.id = term.school_id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON t.user_id = v.uid AND v.type = 0 ) AS tmp GROUP BY term_id, grade_class, week_num HAVING num > 1) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
        
        $result = $this->findPageBySql($this->getUnusualStat_4_sql(), $count, 100);
        
        return $result;
    }
    
    public function unusualStat_4_export(){
        $sql = $this->getUnusualStat_4_sql();
        return $this->query($sql);
    }
    
    private function getUnusualStat_5_sql(){
        return "SELECT term_name, school_name, concat( grade_name, class_num, ' 班' ) AS grade_class, concat( '第', week_num, '周星期', week_day, '第', section_num, '节' ) AS date, realname, count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, t.grade_id, grade. NAME AS grade_name, t.class_num, t.week_num, t.week_day, t.section_num, v.realname FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON term.school_id = s.id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON v.uid = t.user_id AND v.type = 0 ) AS tmp GROUP BY term_id, grade_id, class_num, week_num, week_day, section_num HAVING num > 1 ORDER BY term_name, school_name";
    }
    
    /**
     * 一个班同一时间超过1名老师上课
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_5(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, t.grade_id, grade. NAME AS grade_name, t.class_num, t.week_num, t.week_day, t.section_num, v.realname FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON term.school_id = s.id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON v.uid = t.user_id AND v.type = 0 ) AS tmp GROUP BY term_id, grade_id, class_num, week_num, week_day, section_num HAVING num > 1) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
        
        $result = $this->findPageBySql($this->getUnusualStat_5_sql(), $count, 100);
        
        return $result;
    }
    
    public function unusualStat_5_export(){
        $sql = $this->getUnusualStat_5_sql();
        return $this->query($sql);
    }
    
    private function getUnusualStat_6_sql(){
        return "SELECT term_name, school_name, realname, CONCAT(course_name, '-', hours_name) AS hours_name, concat( grade_name, class_num, ' 班' ) AS grade_class, count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, t.grade_id, grade. NAME AS grade_name, t.class_num, t.week_num, t.week_day, t.section_num, feedback.course_name, feedback.hours_name, v.realname, t.user_id FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON term.school_id = s.id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON v.uid = t.user_id AND v.type = 0 ) AS tmp GROUP BY term_id, grade_id, class_num, course_name, hours_name HAVING num > 1 ORDER BY term_name, school_name";
    }
    
    /**
     * 一个班上同一节课超过一次
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_6(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, t.grade_id, grade. NAME AS grade_name, t.class_num, t.week_num, t.week_day, t.section_num, feedback.course_name, feedback.hours_name, v.realname, t.user_id FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id INNER JOIN ts_db_school s ON term.school_id = s.id INNER JOIN ts_db_grade grade ON grade.id = t.grade_id INNER JOIN ts_user_verified v ON v.uid = t.user_id AND v.type = 0 ) AS tmp GROUP BY term_id, grade_id, class_num, course_name, hours_name HAVING num > 1) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
        
        $result = $this->findPageBySql($this->getUnusualStat_6_sql(), $count, 100);
        
        return $result;
    }
    
    public function unusualStat_6_export(){
        $sql = $this->getUnusualStat_6_sql();
        return $this->query($sql);
    }


    private function getUnusualStat_7_sql(){
        return "SELECT term_name, school_name, class_name, class_count, count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, term.class_num AS class_count, t.course_id, c.class_name FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id AND term.class_num > 0 INNER JOIN ts_db_school s ON term.school_id = s.id INNER JOIN ts_db_course c ON c.id = t.course_id INNER JOIN ts_user_verified v ON v.uid = t.user_id AND v.type = 0 ) AS tmp GROUP BY term_id, course_id HAVING num > class_count ORDER BY term_name, school_name";
    }
    
    /**
     * 上课班级超过真实班级数
     * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
     */
    public function unusualStat_7(){
        $count_sql = "SELECT count(1) AS count FROM (SELECT class_count, count(1) AS num FROM ( SELECT term.id AS term_id, term. NAME AS term_name, s. NAME AS school_name, term.class_num AS class_count, t.course_id, c.class_name FROM ts_db_select_course t INNER JOIN ts_db_lesson_feedback feedback ON feedback.course_id = t.id AND feedback.`status` = 1 INNER JOIN ts_db_term term ON term.id = t.term_id AND term.class_num > 0 INNER JOIN ts_db_school s ON term.school_id = s.id INNER JOIN ts_db_course c ON c.id = t.course_id INNER JOIN ts_user_verified v ON v.uid = t.user_id AND v.type = 0 ) AS tmp GROUP BY term_id, course_id HAVING num > class_count) AS tmp";
        $count = $this->query($count_sql);
        $count = $count[0]['count'];
    
        $result = $this->findPageBySql($this->getUnusualStat_7_sql(), $count, 100);
    
        return $result;
    }
    
    public function unusualStat_7_export(){
        $sql = $this->getUnusualStat_7_sql();
        return $this->query($sql);
    }
}
?>