<?php
require_once('../../../config.php');
require_once('lib.php');
require_once('../../../lib/logVarDump.php');

global $CFG, $DB;

//if (!has_capability('block/hw_tests:viewreport_homework', context_user::instance($USER->id))){
//    $url = new moodle_url('/');
//    redirect($url);
//}

if (!empty($_GET['quiz_attempt_id'])) {
    $quizAttemptID = $_GET['quiz_attempt_id'];
    $urlAdd = '';
    $urlAdd .= (empty($_GET['courseid']) ? '' : '&courseid=' . $_GET['courseid']);
    $urlAdd .= (empty($_GET['groupid']) ? '' : '&groupid=' . $_GET['groupid']);
    $urlAdd .= (empty($_GET['quizid']) ? '' : '&quizid=' . $_GET['quizid']);
    $urlAdd .= (empty($_GET['chboxcourse']) ? '' : '&chboxcourse=' . $_GET['chboxcourse']);
    $urlAdd .= (empty($_GET['chbox_show_all_hw']) ? '' : '&chbox_show_all_hw=' . $_GET['chbox_show_all_hw']);
    $urlAdd .= (empty($_GET['search_query']) ? '' : '&search_query=' . $_GET['search_query']);

//    logVarDump($urlAdd, 'grade -- $urlAdd');
//    logVarDump($_GET['courseid'], 'grade -- courseid');
//    logVarDump($_GET['quizid'], 'grade -- quizid');
//    logVarDump($_GET['search_query'], 'grade -- search_query');


    $urlForvard = $CFG->wwwroot . '/mod/quiz/review.php?attempt=' . $quizAttemptID . '&showall=0' . $urlAdd;
    $urlBack = $CFG->wwwroot . '/blocks/reports/hw_grade/view.php?attempt=' . $quizAttemptID . '&showall=0' . $urlAdd;

    $courseID = $_GET['o_courseid'];
    $quizID = $_GET['o_quizid'];
    $attempt = $_GET['o_attempt'];

    $sql = "SELECT o.user, u.firstname, u.lastname
            FROM mdl_onreview o
            LEFT JOIN mdl_user u ON u.id = o.user
            WHERE o.course = $courseID and o.module = 16 and o.instance = $quizAttemptID and o.attempt = $attempt";
    $record = $DB->get_record_sql($sql, NULL);

    if (empty($record)) {
        $record = array('course' => $courseID,
            'module' => 16,
            'instance' => $quizAttemptID,
            'attempt' => $attempt,
            'user' => $USER->id);
        $DB->insert_record('onreview', $record);
        saveGrade($quizAttemptID, 100);
        redirect($urlForvard);
    }
    else {
        if ($record->user != $USER->id){
            $html  ='<!DOCTYPE html>
                    <html>
                    <head>
                    <link rel="stylesheet" href="' . $CFG->wwwroot . '/blocks/hw_tests/journal.css">
                    </head><body>';
            $html .='<h1>Данное ДЗ уже проверяется - '.$record->firstname.' '.$record->lastname.'</h1>';
            $html .='<br><br>';
            $html .='<div class="button-block">';
                $html .='<a href="'. $urlBack .'"><button class="button-like-adaptable">Назад в журнал ответов</button></a>';
                $html .='<a href="'. $urlForvard .'"><button class="button-like-adaptable">Открыть документ</button></a>';
            $html .='</div></body>';
            echo $html;
        }
        else {
            redirect($urlForvard);
        }
    }
}

function saveGrade($attemptid, $grade) {
    global $DB;

    $sql = "SELECT qa1.id
            FROM mdl_quiz_attempts qa
            LEFT JOIN mdl_quiz_attempts qa1 ON qa1.quiz = qa.quiz and qa1.userid = qa.userid and qa1.attempt = (qa.attempt-1)
            WHERE qa.id = $attemptid";
    $record = $DB->get_record_sql($sql);

    if (empty($record->id)){
        return;
    }

    $prevAttemptID = $record->id;

    $sql = "SELECT sub.id, sub.slot, sub.fraction
            FROM 
            (
                SELECT quest_as.id, quest_a.slot, quest_as.fraction
                FROM mdl_quiz_attempts qa
                LEFT JOIN mdl_question_attempts quest_a ON quest_a.questionusageid = qa.uniqueid
                LEFT JOIN mdl_question_attempt_steps quest_as ON quest_as.questionattemptid = quest_a.id
                WHERE qa.id = $prevAttemptID
                and quest_as.fraction IS NOT NULL
                GROUP BY quest_as.id, quest_a.slot
                ORDER BY quest_as.id DESC
            ) sub
            GROUP BY sub.slot";

    $rs = $DB->get_recordset_sql($sql);

    foreach($rs as $record) {
        if ($record->fraction == 1) {
            $slots[$record->slot]=$record->slot;
        }
    }
    if (empty($slots)) {
        return;
    }
    $slotList = implode(',', $slots);

    $sql = "SELECT ques_a.id, ques_a.maxmark, ques_a.slot, quiz_a.userid
        FROM mdl_quiz_attempts quiz_a
        LEFT JOIN mdl_question_attempts ques_a ON quiz_a.uniqueid = ques_a.questionusageid
        WHERE quiz_a.id = $attemptid and ques_a.behaviour = 'manualgraded' and ques_a.slot IN ($slotList)";

    $records = $DB->get_records_sql($sql, NULL);

    foreach ($records as $record) {
        $questionAttemptID = $record->id;
        $maxmark = empty($record->maxmark) ? 1 : $record->maxmark;
        $fraction = 1;

        $comment = getPrevComment($prevAttemptID, $record->slot);
        $answer = getCurrentAnswer($attemptid, $record->slot);
        $commentCleaned = clean($comment);
        $answerCleaned = clean($answer);

        if ($commentCleaned != $answerCleaned) {
            continue;
        }
        saveQuestionAttemptSteps($questionAttemptID, $record->slot, $fraction, $maxmark, $answer);
    }
}

function clean($str) {
    $str = strip_tags($str);
    $str = str_replace(array("\n\r","\r\n"),'',$str);
    $str = str_replace(array("\r","\n"),'',$str);
    $str = str_replace(array('   ','  ',' '),'',$str);
    $str = str_replace(" ",'',$str);

    return $str;
}

function saveQuestionAttemptSteps($questionAttemptID, $slot, $fraction, $maxmark, $comment)
{
    global $DB, $USER;

    switch ($fraction) {
        case 0      :   $state = 'mangrwrong'; break;
        case 1    :   $state = 'mangrright'; break;
        default     :   $state = 'mangrpartial'; break;
    }

    $record = array('questionattemptid' => $questionAttemptID,
        'sequencenumber' => findSequenceNumber($questionAttemptID),
        'state' => $state,
        'fraction' => $fraction,
        'timecreated' => time(),
        'userid' => $USER->id);
    $lastInsertID = $DB->insert_record('question_attempt_steps', $record);

    saveQuestionAttemptStepsData($lastInsertID, $questionAttemptID, $slot, $fraction*$maxmark, $comment);
}

function findSequenceNumber($questionAttemptID)
{
    global $DB;

    $sql = "SELECT Max(sequencenumber) as max_seq
            FROM mdl_question_attempt_steps
            WHERE questionattemptid = $questionAttemptID";

    $record = $DB->get_record_sql($sql, NULL);

    return $record->max_seq+1;
}

function saveQuestionAttemptStepsData($questionAttemptStepsID, $questionAttemptID, $slot, $grade, $comment)
{
    global $DB;

    $record = array('attemptstepid' => $questionAttemptStepsID,
        'name' => '-comment',
        'value' => $comment);
    $DB->insert_record('question_attempt_step_data', $record);

    $record = array('attemptstepid' => $questionAttemptStepsID,
        'name' => '-commentformat',
        'value' => 1);
    $DB->insert_record('question_attempt_step_data', $record);

    $record = array('attemptstepid' => $questionAttemptStepsID,
        'name' => '-mark',
        'value' => $grade);
    $DB->insert_record('question_attempt_step_data', $record);

    $record = array('attemptstepid' => $questionAttemptStepsID,
        'name' => '-maxmark',
        'value' => $grade);
    $DB->insert_record('question_attempt_step_data', $record);
}

function getQuizAttemptSumGrades($quizAttemptID)
{
    global $DB;

    $sql = "SELECT SUM(sub.fraction*sub.maxmark) totalgrade
            FROM 
                (SELECT ques_a.id, ques_a.maxmark, ques_a.slot,
                ques_a_s.sequencenumber, ques_a_s.fraction,
                MAX(ques_a_s.sequencenumber) max_seq
                FROM mdl_quiz_attempts quiz_a
                LEFT JOIN mdl_question_attempts ques_a ON quiz_a.uniqueid = ques_a.questionusageid
                LEFT JOIN mdl_question_attempt_steps ques_a_s ON ques_a_s.questionattemptid = ques_a.id
                WHERE quiz_a.id = $quizAttemptID and ques_a_s.fraction IS NOT NULL
                GROUP BY ques_a.id) sub";
    $record = $DB->get_record_sql($sql);

    return $record->totalgrade;

}

function getLastComment($questionAttemptID, $slot) {
    global $DB;

    $sql = "SELECT quest_a_s_d.value
            FROM mdl_question_attempt_step_data quest_a_s_d
            LEFT JOIN mdl_question_attempt_steps quest_a_s ON quest_a_s.id = quest_a_s_d.attemptstepid
            LEFT JOIN mdl_question_attempts quest_a ON quest_a.id = quest_a_s.questionattemptid
            WHERE quest_a.id = $questionAttemptID and quest_a_s_d.name='-comment'
            ORDER BY quest_a_s_d.attemptstepid DESC
            LIMIT 1";
    $record = $DB->get_record_sql($sql, NULL);

    if (!empty($record)) {
        return $record->value;
    }

    return '';
}

function getCurrentAnswer($attemptID, $slot) {
    global $DB;

    $sql = "SELECT quest_a_s_d.value
            FROM mdl_question_attempt_step_data quest_a_s_d
            LEFT JOIN mdl_question_attempt_steps quest_a_s ON quest_a_s.id = quest_a_s_d.attemptstepid
            LEFT JOIN mdl_question_attempts quest_a ON quest_a.id = quest_a_s.questionattemptid
            LEFT JOIN mdl_quiz_attempts q_a ON q_a.uniqueid = quest_a.questionusageid
            WHERE q_a.id = $attemptID and quest_a.slot = $slot and quest_a_s_d.name='answer' 
            ORDER BY quest_a_s_d.attemptstepid DESC
            LIMIT 1";
    $record = $DB->get_record_sql($sql, NULL);

    if (!empty($record)) {
        return $record->value;
    }

    return '';
}

function getPrevComment($prevAttemptID, $slot) {
    global $DB;

    $sql = "SELECT quest_a_s_d.value
            FROM mdl_question_attempt_step_data quest_a_s_d
            LEFT JOIN mdl_question_attempt_steps quest_a_s ON quest_a_s.id = quest_a_s_d.attemptstepid
            LEFT JOIN mdl_question_attempts quest_a ON quest_a.id = quest_a_s.questionattemptid
            LEFT JOIN mdl_quiz_attempts q_a ON q_a.uniqueid = quest_a.questionusageid
            WHERE q_a.id = $prevAttemptID and quest_a.slot = $slot and quest_a_s_d.name='-comment' 
            ORDER BY quest_a_s_d.attemptstepid DESC
            LIMIT 1";
    $record = $DB->get_record_sql($sql, NULL);

    if (!empty($record)) {
        return $record->value;
    }

    return '';
}