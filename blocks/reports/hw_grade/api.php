<?php
require_once('../../../config.php');
require_once('../../../lib/logVarDump.php');

require_once('lib.php');


//echo 444333; exit;
$action = optional_param('action', '', PARAM_TEXT);

$courseid = optional_param('courseid', 0, PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_INT);
$quizid = optional_param('quizid', 0, PARAM_INT);
$chboxcourse = optional_param('chboxcourse', 0, PARAM_INT);
$chbox_show_all_hw = optional_param('chbox_show_all_hw', 0, PARAM_INT);
$search_query = optional_param('search_query', '', PARAM_TEXT);

$DEV_MODE = true;
$cxboxCourse = true;
if ($DEV_MODE) {
    header("Access-Control-Allow-Origin: *");
    $cxboxCourse = false;
}

$response = null;

if ($action === 'getInitialData') {
    $response = new stdClass();
    $response->courseid = $courseid;
    $response->quizid = $quizid;
    $response->search_query = $search_query;
}

if ($action === 'getCourses') {
//    $response = getTeachersCoursesIDList($cxboxCourse);
    $response = getTeachersCoursesID($cxboxCourse);
}

if ($action === 'getHeaderData') {
    $teacherCoursesIDList = getTeachersCoursesIDList($chboxcourse);
    $userIDList = getUserIDList($search_query);

    $t_24h = time() - 86400;

    $response = new stdClass();
    $response->total = getTotalHW(0, 0, $teacherCoursesIDList, $userIDList);
    $response->attention = getTotalHW(0, $t_24h, $teacherCoursesIDList, $userIDList);
}

if ($action === 'getTableData') {
    $teacherCoursesIDList = getTeachersCoursesIDList($chboxcourse);
    $userIDList = getUserIDList($search_query);

    list ($request, $usersGroups, $usersSubCourses, $usersPosts, $userQuizFirstAttempt, $userQuizAllAttempt, $userOnReview, $plagiatByQuizAttemptsID, $quizAttemptSections) = getHWforTable($courseid, $groupid, $quizid, $teacherCoursesIDList, $userIDList, $chbox_show_all_hw, $chboxcourse);

    $arrayForReturn = array(
//        'courseid' => $courseid,
        'groupid' => $groupid,
//        'quizid' => $quizid,
        'chboxcourse' => $chboxcourse,
        'chbox_show_all_hw' => $chbox_show_all_hw,
//        'search_query' => $search_query
    );

    $resp = [];
    foreach ($request as $item) {
        $timeSpent = round((time() - $item->timefinish) / 3600, 0);
        $keyForumSection = $item->course_section . '_' .$item->userid;

        $ar = array('course_name' => $item->shortname,
            'course_id' => $item->course,
            'quiz_attempt_id' => $item->quiz_a_id,
            'quiz_id' => $item->quiz_id,
            'quiz_name' => $item->quiz_name,
            'user_id' => $item->userid,
            'user_name' => $item->lastname . ' ' . $item->firstname,
            'user_email' => $item->email,
            'user_company' => $usersGroups[$item->userid . '_' . $item->course],
//            'user_link' => createUserLink($item['user_id'], $item['user_name'], $item['course_id']),
            'attempt' => $item->attempt,
            'time_start' => $item->timestart,
            'time_end' => $item->timefinish,
            'time_spent' => $timeSpent,
            'teacher' => $userQuizAllAttempt[$item->userid . '_' . $item->quiz_id . '_' . $item->attempt],
            'teacher1' => $userQuizFirstAttempt[$item->userid . '_' . $item->quiz_id . '_' . 1],
            'onreview' => $userOnReview[$item->course . '_' . $item->quiz_a_id . '_' . $item->attempt],
//            'onreview' => createOnReview($item, $arrayForReturn),
            'grade' => $item->grade,
            'time_graded' => $item->time_graded,
            'subcourses' => $usersSubCourses[$item->userid . '_' . $item->course],
            'posts' => $usersPosts[$item->userid . '_' . $item->course],
            'str_len' => $item->str_len,
//        'plagiat_slot' => $plagiatByQuizAttemptsID[$item->quiz_a_id]->slot,
//        'plagiat_percent' => $plagiatByQuizAttemptsID[$item->quiz_a_id]->plagiat_percent,
//        'plagiat_quiz_attempt_id' => $plagiatByQuizAttemptsID[$item->quiz_a_id]->plagiat_quiz_attempt_id,
            'plagiat_slot' => 0,
            'plagiat_percent' => 0,
            'plagiat_quiz_attempt_id' => 0,
            'disc_id' => $forumSections[$keyForumSection]->disc_id,
            'post_id' => $forumSections[$keyForumSection]->max_id,
        );
        $ar['user_link'] = createUserLink($ar['user_id'], $ar['user_name'], $ar['course_id']);
        list ($url, $status, $grader) = createOnReview($ar, $arrayForReturn);
        $ar['onreview_link'] = $url;
        $ar['onreview_status'] = $status;
        $ar['onreview_grader'] = $grader;
        array_push($resp, $ar);
    }

    $response = $resp;

}


echo json_encode($response);

function createUserLink($userId, $userName, $courseId) {
    global $CFG;
    $url = $CFG->wwwroot . '/user/view.php?id=' . $userId . '&course=' . $courseId;

    return $url;
}

function createOnReview($item, $arrayForReturn) {
    global $CFG;
    $param = [
        'quiz_attempt_id' => $item['quiz_attempt_id'],
//        'courseid' => $arrayForReturn['courseid'],
        'groupid' => $arrayForReturn['groupid'],
//        'quizid' => $arrayForReturn['quizid'],
        'chboxcourse' => $arrayForReturn['chboxcourse'],
        'chbox_show_all_hw' => $arrayForReturn['chbox_show_all_hw'],
//        'search_query' => $arrayForReturn['search_query'],
        'o_courseid' => $item['course_id'],
        'o_quizid' => $item['quiz_id'],
        'o_attempt' => $item['attempt']
    ];

    $status = '';
    $grader = '';
    if ($item['grade'] != NULL) {
        $url_ = '/blocks/reports/hw_grade/view_action_open.php';
//        $status = 'Открыть';
        $status = 'OPEN';
    }
    else {
        $url_ = '/blocks/reports/hw_grade/view_action_grade.php';
//        $status = 'Проверить';
        $status = 'NEED_GRADE';
    }

    $url = $CFG->wwwroot . $url_ .
        '?quiz_attempt_id=' . $item['quiz_attempt_id'] .
//        '&courseid=' . $arrayForReturn['courseid'] .
        '&groupid=' . $arrayForReturn['groupid'] .
//        '&quizid=' . $arrayForReturn['quizid'] .
        '&chboxcourse=' . $arrayForReturn['chboxcourse'] .
        '&chbox_show_all_hw=' . $arrayForReturn['chbox_show_all_hw'] .
//        '&search_query=' . $arrayForReturn['search_query'] .
        '&o_courseid=' . $item['course_id'] .
        '&o_quizid=' . $item['quiz_id'] .
        '&o_attempt=' . $item['attempt'];
//    static $ee = 0;
//    $ee++;
//    logVarDump($item['onreview'], $ee . ' onreview');
//    logVarDump($status, $ee . ' status before');

    if (empty($item['grade'])) {
        $lastGrader = $item['onreview'];
//        $status = empty($item['onreview']) ? $status : 'на проверке <br>' . $lastGrader;
        if (!empty($item['onreview'])) {
            $status = 'ON_REVIEW';
            $grader = $lastGrader;
        }
    }
    else {
        $status = 'AUTOTEST';
        if (!empty($item['teacher'])) {
            $status = 'GRADED';
            $grader = $item['teacher'];
        }
    }

//    logVarDump($status, $ee . ' status after');


    return array($url, $status, $grader);
}
