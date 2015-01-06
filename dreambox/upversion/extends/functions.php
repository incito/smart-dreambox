<?php
// 浏览器友好的输出
function dump($var, $echo = true, $label = null, $strict = true)
{
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if(!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre style="text-align:left">'.$label.htmlspecialchars($output,ENT_QUOTES).'</pre>';
        } else {
            $output = $label . " : " . print_r($var, true);
        }
    }else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if(!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre style="text-align:left">'. $label. htmlspecialchars($output, ENT_QUOTES). '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else {
        return $output;
    }
}

// 获取字串首字母
function getFirstLetter($s0) {
    $firstchar_ord = ord(strtoupper($s0{0}));
    if($firstchar_ord >= 65 and $firstchar_ord <= 91) return strtoupper($s0{0});
    if($firstchar_ord >= 48 and $firstchar_ord <= 57) return '#';
    $s = iconv("UTF-8", "gb2312", $s0);
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc>=-20319 and $asc<=-20284) return "A";
    if($asc>=-20283 and $asc<=-19776) return "B";
    if($asc>=-19775 and $asc<=-19219) return "C";
    if($asc>=-19218 and $asc<=-18711) return "D";
    if($asc>=-18710 and $asc<=-18527) return "E";
    if($asc>=-18526 and $asc<=-18240) return "F";
    if($asc>=-18239 and $asc<=-17923) return "G";
    if($asc>=-17922 and $asc<=-17418) return "H";
    if($asc>=-17417 and $asc<=-16475) return "J";
    if($asc>=-16474 and $asc<=-16213) return "K";
    if($asc>=-16212 and $asc<=-15641) return "L";
    if($asc>=-15640 and $asc<=-15166) return "M";
    if($asc>=-15165 and $asc<=-14923) return "N";
    if($asc>=-14922 and $asc<=-14915) return "O";
    if($asc>=-14914 and $asc<=-14631) return "P";
    if($asc>=-14630 and $asc<=-14150) return "Q";
    if($asc>=-14149 and $asc<=-14091) return "R";
    if($asc>=-14090 and $asc<=-13319) return "S";
    if($asc>=-13318 and $asc<=-12839) return "T";
    if($asc>=-12838 and $asc<=-12557) return "W";
    if($asc>=-12556 and $asc<=-11848) return "X";
    if($asc>=-11847 and $asc<=-11056) return "Y";
    if($asc>=-11055 and $asc<=-10247) return "Z";
    return '#';
}

function getJumpUrl($type, $page)
{
    $site_url = SITE_URL.'/index.php?t='.$type.'&p='.$page;

    return $site_url;
}

function truncateTable($conn, $table)
{
    $sql = 'TRUNCATE TABLE `'.$table.'`;';
    $conn->execute($sql);
}

function updateValue($value)
{
    $data = array();
    foreach($value as $key => $val) {
        $data[$key] = mysql_escape_string($val);
    }

    return $data;
}

function writeErrorLog($errorSql, $pk = '')
{
    $filename = './error.txt';
    !empty($pk) && $pk .= "\n\r";
    $errorSql .= "\n\r";
    $content = $pk.$errorSql;
    if (is_writable($filename)) {
        if(!$handle = fopen($filename, 'a')) {
             echo "不能打开文件 $filename";
             exit;
        }
        if(fwrite($handle, $content) === false) {
            echo "不能写入到文件 $filename";
            exit;
        }
//         echo "成功地将 $content 写入到文件$filename";
        fclose($handle);
    } else {
        echo "文件 $filename 不可写";
    }
}

function clearAllData($conn)
{
    $sqls = array();
    $sqls[] = 'TRUNCATE TABLE `ts_app_tag`';
    $sqls[] = 'TRUNCATE TABLE `ts_atme`';
    $sqls[] = 'TRUNCATE TABLE `ts_attach`';
    $sqls[] = 'TRUNCATE TABLE `ts_collection`';
    $sqls[] = 'TRUNCATE TABLE `ts_comment`';
    $sqls[] = 'TRUNCATE TABLE `ts_credit_user`';
    $sqls[] = 'TRUNCATE TABLE `ts_feed`';
    $sqls[] = 'TRUNCATE TABLE `ts_feed_data`';
    $sqls[] = 'TRUNCATE TABLE `ts_feed_topic`';
    $sqls[] = 'TRUNCATE TABLE `ts_feed_topic_link`';
    $sqls[] = 'TRUNCATE TABLE `ts_login`';
    $sqls[] = 'TRUNCATE TABLE `ts_login_record`';
    $sqls[] = 'TRUNCATE TABLE `ts_message_content`';
    $sqls[] = 'TRUNCATE TABLE `ts_message_list`';
    $sqls[] = 'TRUNCATE TABLE `ts_message_member`';
    $sqls[] = 'TRUNCATE TABLE `ts_tag`';
    $sqls[] = 'TRUNCATE TABLE `ts_user`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_blacklist`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_follow`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_follow_group`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_follow_group_link`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_group_link`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_privacy`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_verified`';
    //add for box
    $sqls[] = 'TRUNCATE TABLE `ts_user_visited`';
    $sqls[] = 'TRUNCATE TABLE `ts_user_data`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_class_hours`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_course`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_grade`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_course_follow`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_course_download`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_integral`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_integral_history`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_lession_video`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_lesson_feedback`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_lesson_file`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_select_course`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_school`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_term`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_video_intro`';
    $sqls[] = 'TRUNCATE TABLE `ts_db_week`';
    $sqls[] = 'TRUNCATE TABLE `ts_blog`';
    $sqls[] = 'TRUNCATE TABLE `ts_blog_comment`';
    $sqls[] = 'TRUNCATE TABLE `ts_blog_tag`';
    $sqls[] = 'TRUNCATE TABLE `ts_notify_email`';
    $sqls[] = 'TRUNCATE TABLE `ts_notify_message`';
    $sqls[] = 'TRUNCATE TABLE `ts_message_content`';
    $sqls[] = 'TRUNCATE TABLE `ts_message_list`';
    $sqls[] = 'TRUNCATE TABLE `ts_message_member`';
    foreach($sqls as $sql) {
        $conn->execute($sql);
    }
}

/**
 * t函数用于过滤标签，输出没有html的干净的文本
 * @param string text 文本内容
 * @return string 处理后内容
 */
function t($text){
	$text = nl2br($text);
	$text = real_strip_tags($text);
	//$text = htmlspecialchars($text,ENT_QUOTES);
	$text = str_ireplace(array("\r","\n","\t","&nbsp;"),'',$text);
	$text = trim($text);
	return $text;
}

function real_strip_tags($str, $allowable_tags="") {
	$str = stripslashes(htmlspecialchars_decode($str));
	return strip_tags($str, $allowable_tags);
}

function getFlashVar ($host, $flashvar, $source) {
    if (strpos($flashvar, '/') !== false) {
        return $flashvar;
    }
    $return = '';

    switch ($host) {
        case 'youku.com':
            $return = 'http://player.youku.com/player.php/sid/'.$flashvar.'/v.swf';
            break;
        case 'ku6.com':
            $return = 'http://player.ku6.com/refer/'.$flashvar.'/v.swf';
            break;
        case 'tudou.com':
            if (strpos($source, 'www.tudou.com/albumplay') !== false) {
                $return = 'http://www.tudou.com/a/'.$flashvar.'/&autoPlay=true/v.swf';
            } else if (strpos($source, 'www.tudou.com/programs') !== false) {
                $return = 'http://www.tudou.com/v/'.$flashvar.'/&autoPlay=true/v.swf';
            } else if (strpos($source, 'www.tudou.com/listplay') !== false) {
                $return = 'http://www.tudou.com/l/'.$flashvar.'/&autoPlay=true/v.swf';
            } else if (strpos($source, 'douwan.tudou.com') !== false) {
                $return = 'http://www.tudou.com/v/'.$flashvar.'/&autoPlay=true/v.swf';
            }
            break;
        case 'youtube.com':
            $return = 'http://www.youtube.com/embed/'.$flashvar;
            break;
        case 'sohu.com':
            $return = 'http://share.vrs.sohu.com/'.$flashvar.'/v.swf&autoplay=false';
            $return = $flashvar;
            break;
        case 'qq.com':
            $return = 'http://static.video.qq.com/TPout.swf?vid='.$flashvar.'&auto=1';
            break;
        case 'sina.com.cn':
            break;
        case 'yinyuetai.com':
            $return = 'http://player.yinyuetai.com/video/player/'.$flashvar.'/v_0.swf';
            break;
    }

    return $return;
}

function changeBracket ($content) {
    $content = str_replace('&#091;', '[', $content);
    $content = str_replace('&#093;', ']', $content);
    return $content;
}

function getWeiboImage($str, & $image){
    $keyword = '"picurl";s:30:"';
    $start = strpos($str, $keyword);
    if(!$start){
        return $image;
    }
    $prefix = 'http://www.adreambox.net/data/uploads/';
    $substr = substr($str, $start + strlen($keyword));
    $path = strstr($substr, '"', true);
    $image[] = $prefix.$path;
    getWeiboImage($substr, $image);
}


function matchImages($content = '') {
    $src = array ();
    preg_match_all ( '/<img.*src=(.*)[>|\\s]/iU', $content, $src );
    if (count ( $src [1] ) > 0) {
        foreach ( $src [1] as $v ) {
            $images [] = trim ( $v, "\"'" ); //删除首尾的引号 ' "
        }
        return $images;
    } else {
        return false;
    }
}

function safe($text){
    return h($text);
}

/**
 * h函数用于过滤不安全的html标签，输出安全的html
 * @param string $text 待过滤的字符串
 * @param string $type 保留的标签格式
 * @return string 处理后内容
 */
function h($text, $type = 'html'){
    // 无标签格式
    $text_tags  = '';
    //只保留链接
    $link_tags  = '<a>';
    //只保留图片
    $image_tags = '<img>';
    //只存在字体样式
    $font_tags  = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
    //标题摘要基本格式
    $base_tags  = $font_tags.'<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
    //兼容Form格式
    $form_tags  = $base_tags.'<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
    //内容等允许HTML的格式
    $html_tags  = $base_tags.'<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
    //专题等全HTML格式
    $all_tags   = $form_tags.$html_tags.'<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
    //过滤标签
    $text = real_strip_tags1($text, ${$type.'_tags'});
    // 过滤攻击代码
    if($type != 'all') {
        // 过滤危险的属性，如：过滤on事件lang js
        while(preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i',$text,$mat)){
            $text = str_ireplace($mat[0], $mat[1].$mat[3], $text);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text = str_ireplace($mat[0], $mat[1].$mat[3], $text);
        }
    }
    return $text;
}

function real_strip_tags1($str, $allowable_tags="") {
    $str = html_entity_decode($str,ENT_QUOTES,'UTF-8');
    return strip_tags($str, $allowable_tags);
}