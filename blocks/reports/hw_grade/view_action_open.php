<?php
require_once('../../../config.php');
require_once('lib.php');

global $CFG;

//if (!has_capability('block/hw_tests:viewreport_homework', context_user::instance($USER->id))){
//    $url = new moodle_url('/');
//    redirect($url);
//}

if (!empty($_GET['quiz_attempt_id'])) {
    $quizAttemptID = $_GET['quiz_attempt_id'];
    $url = $CFG->wwwroot . '/mod/quiz/review.php?attempt=' . $quizAttemptID . '&showall=0';
    $url .= (empty($_GET['courseid']) ? '' : '&courseid=' . $_GET['courseid']);
    $url .= (empty($_GET['groupid']) ? '' : '&groupid=' . $_GET['groupid']);
    $url .= (empty($_GET['quizid']) ? '' : '&quizid=' . $_GET['quizid']);
    $url .= (empty($_GET['chboxcourse']) ? '' : '&chboxcourse=' . $_GET['chboxcourse']);
    $url .= (empty($_GET['chbox_show_all_hw']) ? '' : '&chbox_show_all_hw=' . $_GET['chbox_show_all_hw']);
    $url .= (empty($_GET['search_query']) ? '' : '&search_query=' . $_GET['search_query']);

    redirect($url);
}