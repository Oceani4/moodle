<?php
require_once($CFG->dirroot.'/lib/logVarDump.php');

function getGroups($courseID = 0)
{
    global $DB;

    $sql = "SELECT id, name
            FROM mdl_groups
            WHERE courseid = :course_id";
    $records = $DB->get_records_sql($sql, array('course_id'=>$courseID));

    return $records;
}

function getTeachersCoursesIDList($cxboxCourse)
{
    global $DB, $USER;

    $sql = "SELECT c.id
             FROM mdl_role_assignments ra 
             LEFT JOIN mdl_user u ON u.id = ra.userid
             LEFT JOIN mdl_context context ON context.id = ra.contextid and context.contextlevel=50
             LEFT JOIN mdl_course c ON c.id = context.instanceid
             WHERE  ra.roleid < 5 " . (($cxboxCourse == 1) ? " and u.id = :user_id" : "");

    $records = $DB->get_records_sql($sql, array('user_id'=>$USER->id));
    $coursesList = implode(',', array_keys($records));

    return $coursesList;
}


function getUserIDList($searchQuery)
{
    global $DB;

    $searchQuery = str_replace(' ', '', $searchQuery);
    if (empty($searchQuery)) return '';

    $searchQuery = '%' . $searchQuery . '%';

    $sql = "SELECT id
            FROM mdl_user
            WHERE firstname LIKE :fname or
                  lastname LIKE :lname or
                  email LIKE :mail";

    $records = $DB->get_records_sql($sql, array('fname'=>$searchQuery, 'lname'=>$searchQuery, 'mail'=>$searchQuery));

    $userIDList = implode(',', array_keys($records));

    return $userIDList;
}


function getTotalHW($courseID = 0, $time = 0, $coursesIDList, $usersIDList)
{
    global $DB;

    list($courseIDsql, $courseIDParams) = $DB->get_in_or_equal(explode(',',$coursesIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);
    list($userIDsql, $userIDParams) = $DB->get_in_or_equal(explode(',',$usersIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);

    $sql = "SELECT Count(q_a.id) as totalhw
            FROM mdl_quiz_attempts q_a
            LEFT JOIN mdl_quiz q ON q_a.quiz = q.id
            LEFT JOIN mdl_course c ON c.id = q.course
            WHERE q_a.state ='finished' and (q_a.sumgrades IS NULL or q_a.sumgrades/(q_a.currentpage+1)=5)"
        . (($courseID == 0) ? "" : " and c.id = :course_id")
        . (empty($coursesIDList) ? "" : " and c.id " . $courseIDsql )
        . (empty($usersIDList) ? "" : " and q_a.userid " . $userIDsql )
        . (($time != 0) ? " and q_a.timefinish < :time" : "");

    $params = array('course_id'=>$courseID,'time'=>$time);
    $params = array_merge($params, $courseIDParams, $userIDParams);

    $record = $DB->get_record_sql($sql, $params);

    if (isset($record->totalhw)) {
        return $record->totalhw;
    }
    return 0;
}


function getHWforTable($courseID, $groupid = 0, $quizid, $coursesIDList, $usersIDList, $showAllHW, $chboxcourse)
{
    global $DB;

    if (empty($coursesIDList) and $chboxcourse) {
        return;
    }

    list($courseIDsql, $courseIDParams) = $DB->get_in_or_equal(explode(',',$coursesIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);
    list($userIDsql, $userIDParams) = $DB->get_in_or_equal(explode(',',$usersIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);

    $sql = "SELECT quiz_a.id as quiz_a_id, quiz_a.timestart, quiz_a.timefinish, quiz_a.sumgrades/quiz.sumgrades*quiz.grade as grade,
            quiz.name as quiz_name, quiz.id as quiz_id, quiz.course, course.shortname, quiz_a.attempt, 
            u.id as userid, u.firstname, u.lastname, u.email, 
            MAX(LENGTH(question_a.responsesummary)) as str_len,
            question_a.id, c_s.id as course_section"
            .($showAllHW == 1 ? ", MAX(question_a_s.timecreated) as time_graded" : "")
            ."
            FROM mdl_quiz_attempts quiz_a
            LEFT JOIN mdl_quiz quiz ON quiz.id = quiz_a.quiz
            LEFT JOIN mdl_course course ON course.id = quiz.course
            LEFT JOIN mdl_course_modules c_m ON c_m.course = course.id and c_m.module = 16 and c_m.instance = quiz_a.quiz
            LEFT JOIN mdl_course_sections c_s ON c_s.id = c_m.section
            LEFT JOIN mdl_user u ON u.id = quiz_a.userid "
            .($groupid == 0 ? "" : " LEFT JOIN mdl_groups g ON g.courseid = course.id 
            LEFT JOIN mdl_groups_members g_m ON g_m.groupid = g.id ")
            ." 
            LEFT JOIN mdl_question_attempts question_a ON question_a.questionusageid = quiz_a.uniqueid
            LEFT JOIN mdl_question question ON question.id = question_a.questionid
            LEFT JOIN mdl_question_attempt_steps question_a_s ON question_a_s.questionattemptid = question_a.id 
            WHERE quiz_a.state ='finished' "
        . (($showAllHW == 1) ? "" : "  and quiz_a.sumgrades IS NULL")
        . (($courseID == 0) ? "" : " and course.id = :course_id")
        . (empty($coursesIDList) ? "" : " and course.id " . $courseIDsql )
        . (empty($groupid) ? "" : " and g_m.userid = u.id and g_m.groupid = :group_id")
        . (empty($usersIDList) ? "" : " and u.id " . $userIDsql )
        . (($quizid != 0) ? " and c_m.id = :quiz_id" : "")
        . " GROUP BY quiz_a.id"
        . (($showAllHW == 1) ? " ORDER BY time_graded DESC, c_s.section, quiz.name, userid, quiz_a.attempt limit 1000" : " ORDER BY quiz_a.timefinish");

    $params = array('course_id'=>$courseID,'group_id'=>$groupid, 'quiz_id'=>$quizid);
    $params = array_merge($params, $courseIDParams, $userIDParams);

    $rs = $DB->get_recordset_sql($sql, $params);

    foreach ($rs as $record) {
        $records[] = $record;
        $users[$record->userid] = $record->userid;
        $courses[$record->course] = $record->course;
        $quizes[$record->quiz_id]=$record->quiz_id;
        $quizAttempts[$record->quiz_a_id]=$record->quiz_a_id;
        $attempts[$record->attempt]=$record->attempt;
        $sections[$record->quiz_a_id]= $record->course_section . '_' . $record->userid;
    }

    $usersList = implode(',', array_keys($users));
    $coursesList = implode(',', array_keys($courses));
    $quizesList = implode(',', array_keys($quizes));
    $quizAttemptsList = implode(',', array_keys($quizAttempts));
    $attemptsList = implode(',', array_keys($attempts));

    $usersGroups = [];
    $usersSubCourses = [];
    $usersPosts = [];
    $userQuizFirstAttempt = [];
    $userQuizAllAttempt = [];
    $userOnReview = [];
    $plagiatByQuizAttemptsID = [];

    if (empty($users)) {
        return array($records, $usersGroups, $usersSubCourses, $usersPosts, $userQuizFirstAttempt, $userQuizAllAttempt);
    }

    $sql = "SELECT u.id, u.firstname, u.lastname, q_a.quiz, q_a.userid, q_a.attempt
            FROM mdl_question_attempt_steps qu_a_s
            LEFT JOIN mdl_question_attempts qu_a ON qu_a.id = qu_a_s.questionattemptid
            LEFT JOIN mdl_quiz_attempts q_a ON q_a.uniqueid = qu_a.questionusageid and q_a.attempt=1
            LEFT JOIN mdl_user as u ON u.id = qu_a_s.userid 
            WHERE q_a.quiz IN ($quizesList) and qu_a_s.fraction IS NOT NULL and qu_a_s.fraction !=0.05 and q_a.userid IN ($usersList) 
                  and qu_a.behaviour = 'manualgraded'
            GROUP BY q_a.userid, q_a.quiz
            ORDER BY qu_a_s.id ";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->userid . '_' . $record->quiz . '_' . $record->attempt;
        $value = $record->firstname.' '.$record->lastname;
        $userQuizFirstAttempt[$key]= (array_key_exists($key, $userQuizFirstAttempt) ?($userQuizFirstAttempt[$key].';;'. $value) : $value);
    }

    $sql = "SELECT u.id, u.firstname, u.lastname, q_a.quiz, q_a.userid, q_a.attempt
            FROM mdl_question_attempt_steps qu_a_s
            LEFT JOIN mdl_question_attempts qu_a ON qu_a.id = qu_a_s.questionattemptid
            LEFT JOIN mdl_quiz_attempts q_a ON q_a.uniqueid = qu_a.questionusageid 
            LEFT JOIN mdl_user as u ON u.id = qu_a_s.userid 
            WHERE q_a.quiz IN ($quizesList) and qu_a_s.fraction IS NOT NULL and q_a.userid IN ($usersList)
                  and qu_a.behaviour = 'manualgraded'
            GROUP BY q_a.quiz, q_a.attempt, qu_a_s.timecreated
            ORDER BY qu_a_s.id DESC";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->userid . '_' . $record->quiz . '_' . $record->attempt;
        $value = $record->firstname.' '.$record->lastname;
        $userQuizAllAttempt[$key]= (array_key_exists($key, $userQuizAllAttempt) ?($userQuizAllAttempt[$key]) : $value);
    }

    $sql = "SELECT role_a.userid, context.instanceid as course, role.name
            FROM mdl_role role
            LEFT JOIN mdl_role_assignments role_a ON role_a.roleid = role.id
            LEFT JOIN mdl_context context ON context.id = role_a.contextid
            WHERE role_a.userid IN ($usersList) and context.contextlevel = 50 and context.instanceid IN ($coursesList)";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->userid.'_'.$record->course;
        $value = $record->name;
        $usersSubCourses[$key]= (array_key_exists($key, $usersSubCourses) ? ($usersSubCourses[$key].';;'. $value) : $value);
    }

    $sql = "SELECT u.id, g.courseid, g.name as company
            FROM mdl_groups_members gm
            LEFT JOIN mdl_groups g ON g.id = gm.groupid
            LEFT JOIN mdl_user u ON u.id = gm.userid
            WHERE u.id IN ($usersList) and g.courseid IN ($coursesList)
            GROUP BY u.id, g.courseid, g.name";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->id.'_'.$record->courseid;
        $value = $record->company;
        if (!array_key_exists($key, $usersGroups)) {
            $usersGroups[$key]= $value;
        }
    }

    $sql = "SELECT post.userid, post.courseid, post.content
            FROM mdl_post post
            WHERE post.userid IN ($usersList) and post.courseid IN ($coursesList)";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->userid.'_'.$record->courseid;
        $value = $record->content;
        $usersPosts[$key]= (array_key_exists($key, $usersPosts) ? ($usersPosts[$key].';;'. $value) : $value);
    }

    $sql = "SELECT o.course, o.instance as quiz, o.attempt,  
            u.id as userid, u.firstname, u.lastname
            FROM mdl_onreview o
            LEFT JOIN mdl_user u ON u.id = o.user
            WHERE o.course IN ($coursesList) and o.module = 16 and o.instance IN ($quizAttemptsList) and o.attempt IN ($attemptsList)";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->course . '_' . $record->quiz . '_' . $record->attempt;
        $value = $record->firstname.' '.$record->lastname;
        $userOnReview[$key]= (array_key_exists($key, $userOnReview) ?($userOnReview[$key]) : $value);
    }

    $sql = "SELECT p.*
            FROM mdl_plagiat p
            WHERE p.quiz_attempt_id IN ($quizAttemptsList)";

    $rs = $DB->get_recordset_sql($sql);

    foreach ($rs as $record) {
        $key = $record->quiz_attempt_id;
        $value = $record;
        $plagiatByQuizAttemptsID[$key]= (array_key_exists($key, $plagiatByQuizAttemptsID) ? max($plagiatByQuizAttemptsID[$key],$value) : $value);
    }

    return array($records, $usersGroups, $usersSubCourses, $usersPosts, $userQuizFirstAttempt, $userQuizAllAttempt, $userOnReview, $plagiatByQuizAttemptsID, $sections);
}

/**
 * Получить все домашние работы, требующие проверки (по курсам ) --  для Side-Menu
 *
 * @param $time
 * @param $coursesIDList
 * @param $usersIDList
 * @return array
 */
function getHWforSideMenuByCourses($time, $coursesIDList, $usersIDList, $groupID, $showAllHW, $chboxcourse)
{
    global $DB;

    if (empty($coursesIDList) and $chboxcourse) {
        return;
    }

    list($courseIDsql, $courseIDParams) = $DB->get_in_or_equal(explode(',',$coursesIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);
    list($userIDsql, $userIDParams) = $DB->get_in_or_equal(explode(',',$usersIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);

    $sql = "SELECT q_a.id, COUNT(q_a.id) as count_hw,
            SUM(IF(q_a.timefinish<:time and (q_a.sumgrades IS NULL or q_a.sumgrades/(q_a.currentpage+1)=5),1,0)) as count_hw24h,
            q.course, 
            c.id as c_id, c.shortname
            FROM mdl_quiz_attempts q_a
            LEFT JOIN mdl_quiz q ON q_a.quiz = q.id
            LEFT JOIN mdl_course c ON c.id = q.course "
        . (empty($groupID) ? "" : " LEFT JOIN mdl_groups g ON g.courseid = c.id
            LEFT JOIN mdl_user u ON q_a.userid = u.id
            LEFT JOIN mdl_groups_members g_m ON g_m.groupid = g.id and g_m.userid = u.id ")
        . ($showAllHW == 1 ? " WHERE q_a.state ='finished' " : " WHERE q_a.state ='finished' and (q_a.sumgrades IS NULL or q_a.sumgrades/(q_a.currentpage+1)=5) ")
        . (empty($groupID) ? "" : " and g_m.groupid = :group_id")
        . (empty($coursesIDList) ? "" : " and c.id " . $courseIDsql )
        . (empty($usersIDList) ? "" : " and q_a.userid " . $userIDsql )
        . " GROUP BY c.id
            ORDER BY c.id";

    $params = array('time'=>$time, 'group_id'=>$groupID );
    $params = array_merge($params, $courseIDParams, $userIDParams);

    $records = $DB->get_records_sql($sql, $params);
    return $records;
}

//Получить все домашние работы, требующие проверки (по курсам / по тестам) --  для Side-Menu
function getHWforSideMenuByQuiz($time, $courseID, $usersIDList, $groupID, $showAllHW)
{
    global $DB;

    list($userIDsql, $userIDParams) = $DB->get_in_or_equal(explode(',',$usersIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);

    $sql = "SELECT q_a.id, COUNT(q_a.id) as count_hw,
            SUM(IF(q_a.timefinish<:time and (q_a.sumgrades IS NULL or q_a.sumgrades/(q_a.currentpage+1)=5),1,0)) as count_hw24h,
            q.course, q.name,
            c.id as c_id, c.shortname as lesson_name,
            c_m.id as c_m_id, c_m.instance
            FROM mdl_quiz_attempts q_a
            LEFT JOIN mdl_quiz q ON q_a.quiz = q.id
            LEFT JOIN mdl_course c ON c.id = q.course 
            LEFT JOIN mdl_course_modules c_m ON c.id = c_m.course and c_m.module = 16 and c_m.instance = q_a.quiz
            LEFT JOIN mdl_course_sections c_s ON c_s.id = c_m.section "
        . (empty($groupID) ? "" : " LEFT JOIN mdl_groups g ON g.courseid = c.id
            LEFT JOIN mdl_user u ON q_a.userid = u.id
            LEFT JOIN mdl_groups_members g_m ON g_m.groupid = g.id and g_m.userid = u.id ")
        . ($showAllHW == 1 ? " WHERE q_a.state ='finished' and c.id = :course_id1" : " WHERE q_a.state ='finished' and (q_a.sumgrades IS NULL or q_a.sumgrades/(q_a.currentpage+1)=5) and c.id = :course_id2" )
        . (empty($usersIDList) ? "" : " and q_a.userid " . $userIDsql )
        . (empty($groupID) ? "" : " and g_m.groupid = :group_id")
        . " GROUP BY c_m.instance
            ORDER BY c_s.section, q.name";

    $params = array('time'=>$time,'course_id1'=>$courseID,'course_id2'=>$courseID, 'group_id'=>$groupID);
    $params = array_merge($params, $userIDParams);

    $records = $DB->get_records_sql($sql, $params);

    return $records;
}

function getNotAnsweredForumMessage($courseID, $groupid = 0, $forumID, $coursesIDList, $usersIDList, $showAllHW, $chboxcourse)
{
    global $DB;

    if (empty($coursesIDList) and $chboxcourse) {
        return;
    }

    list($courseIDsql, $courseIDParams) = $DB->get_in_or_equal(explode(',',$coursesIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);
    list($userIDsql, $userIDParams) = $DB->get_in_or_equal(explode(',',$usersIDList), $type=SQL_PARAMS_NAMED, $prefix='param', $equal=true, $onemptyitems=false);

    $sql = "SELECT u.firstname, u.lastname, u.email, u.id as user_id, posts.message, course.shortname as course_name,
                   c_s.id as course_section, 
                   sub.*
            FROM 
            (
                SELECT disc.id as disc_id, disc.course as course_id, disc.forum as forum_id, f.name as forum_name, 	
                MAX(posts1.created) as created,
                MAX(posts1.id) as max_id
                FROM mdl_forum_posts posts1
                LEFT JOIN mdl_forum_discussions disc ON disc.id = posts1.discussion
                LEFT JOIN mdl_forum f ON f.id = disc.forum
                GROUP BY disc.course, disc.forum, disc.id
                ORDER BY disc.course ASC, disc.forum ASC, disc.id ASC, posts1.id DESC
            ) sub
            LEFT JOIN mdl_forum_posts posts ON posts.id = sub.max_id
            LEFT JOIN mdl_user u ON u.id = posts.userid
            LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id 
            LEFT JOIN mdl_context context ON context.id = ra.contextid 
            LEFT JOIN mdl_course course ON course.id = sub.course_id
            LEFT JOIN mdl_course_modules c_m ON c_m.course = course.id and c_m.module = 9 and c_m.instance = sub.forum_id
            LEFT JOIN mdl_course_sections c_s ON c_s.id = c_m.section
            LEFT JOIN mdl_groups g ON g.courseid = sub.course_id
            LEFT JOIN mdl_groups_members g_m ON g_m.groupid = g.id and g_m.userid = u.id
            WHERE ra.roleid > 4 and context.instanceid = sub.course_id and context.contextlevel = 50"
        . (empty($coursesIDList) ? "" : " and sub.course_id " . $courseIDsql )
        . (empty($groupid) ? "" : " and g_m.groupid = :group_id")
        . (empty($usersIDList) ? "" : " and u.id " . $userIDsql )
        . " GROUP BY sub.forum_id, u.id, sub.disc_id"
        . (($showAllHW == 1) ? " ORDER BY c_s.section, u.id, sub.disc_id" : " ORDER BY sub.created");

    $params = array('course_id'=>$courseID,'group_id'=>$groupid, 'forum_id'=>$forumID);
    $params = array_merge($params, $courseIDParams, $userIDParams);

    $rs = $DB->get_recordset_sql($sql, $params);

    foreach ($rs as $record) {
        $key = $record->course_section . '_' .$record->user_id;
        $sections[$key] = $record;
    }

    return $sections;
}