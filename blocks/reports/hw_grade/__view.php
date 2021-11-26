<?php
require_once('../../../config.php');
require_once('lib.php');
require_once('side_menu.php');
require_once('table.php');

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = optional_param('courseid', 0, PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_INT);
$quizid = optional_param('quizid', 0, PARAM_INT);
$chboxcourse = optional_param('chboxcourse', 0, PARAM_INT);
$chbox_show_all_hw = optional_param('chbox_show_all_hw', 0, PARAM_INT);
$search_query = optional_param('search_query', '', PARAM_TEXT);

require_login();

if (!has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))){
    $url = new moodle_url('/');
    redirect($url);
}
$PAGE->set_context(context_system::instance()); // -- моя отсебятина
$PAGE->set_url(new moodle_url('/blocks/reports/view.php', array('courseid' => $courseid, 'quizid' => $quizid, 'groupid' => $groupid)));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/blocks/reports/css/journal.css');

$previewnode = $PAGE->navigation->add('Журнал ответов', new moodle_url('/my/index.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Непроверенные задания', new moodle_url('/blocks/reports/view.php', array('courseid' => $courseid)));
$thingnode->make_active();


$html = '<div id="page-wrapper">';

$html .= '<script type="text/javascript" src="./js/jquery-ui.min.js"> </script>';
$html .= '<link rel="stylesheet" type="text/css" href="./js/jquery-ui.min.css">';
$html .= '<link rel="stylesheet" type="text/css" href="./js/jquery-ui.structure.min.css">';
$html .= '<link rel="stylesheet" type="text/css" href="./js/jquery-ui.theme.min.css">';

$html .= '<script type="text/javascript" src="./js/lib.js"> </script>';

$teacherCoursesIDList = getTeachersCoursesIDList($chboxcourse);
$userIDList = getUserIDList($search_query);

$t_24h = time() - 86400;
$totHW = getTotalHW(0, 0, $teacherCoursesIDList, $userIDList);
$totHW24h = getTotalHW(0, $t_24h, $teacherCoursesIDList, $userIDList);

//$totHW = 25;
//$totHW24h = 12;

$html .= '<h2> Список непроверенных заданий в виде тестов.</h2>';
$html .= '<h4> Всего непроверенных работ: <span class="totalHW">' . $totHW . '</span>, ';
$html .= 'из них требуют внимания: <span class="totalHW24h">' . $totHW24h . '</span></h4>';
$html .= '<div class="blank-space"></div>';
$html .= '<div class="blank-space"></div>';

$html .= '<form method="get" action="/blocks/reports/view.php">';
$html .= '<p><input type="text" name="search_query" class="search_query" value="' . $search_query . '" placeholder="Поиск по Имени / Фамилии / E-Mail"></p>';
$html .= "<input type='hidden' value='$courseid' name='courseid'>";
$html .= "<input type='hidden' value='$quizid' name='quizid'>";

$html .= '<select class="groups-select" name="groupid" required> ';
$groups = getGroups($courseid);
$html .= (($groupid != 0) ? '<option value="0" selected> Все </option>' : '<option value="0"> Все </option>');
foreach ($groups as $group) {
    $html .= (($groupid == $group->id) ? '<option value="' . $group->id . '" selected>' . $group->name . '</option>' :
        '<option value="' . $group->id . '">' . $group->name . '</option>');
}
$html .= '</select>';


$html .= '<input type="submit" value="Обновить">';
$html .= '<input type="checkbox" value="1" name="chboxcourse"' . (empty($_GET['chboxcourse']) ? '' : 'checked') . '> Только курсы, в которых я являюсь учителем';
$html .= '<input type="checkbox" value="1" name="chbox_show_all_hw"' . (empty($_GET['chbox_show_all_hw']) ? '' : 'checked') . '> Показывать проверенные ДЗ';
$html .= '</form>';

//if ($USER->id == 2 ) {
//    $html .= '<form method="get" action="/blocks/reports/plagiat_lib_load.php">';
//    $html .= '<input type="submit" value="Загрузить в SQL данные о ДЗ (по 1000 ДЗ)">';
//    $html .= '</form>';
//}

//$html .= '<hr class="table-wrapper">';
//if ($USER->id == 2 ) {
//    $html .= '<form method="get" action="/blocks/hw_tests/plagiat_lib_calculate_all.php">';
//    $html .= '<input type="submit" class="white-btn" value="Сравнить ДЗ">'; //(по последним 2500 ДЗ) Рассчитать плагиат по всем непроверенным ДЗ
//    $html .= '</form>';
//}

$html .= '<div id="page-mod-quiz-report" class="main-table-row">';
list ($request, $usersGroups, $usersSubCourses, $usersPosts, $userQuizFirstAttempt, $userQuizAllAttempt, $userOnReview, $plagiatByQuizAttemptsID, $quizAttemptSections) = getHWforTable($courseid, $groupid, $quizid, $teacherCoursesIDList, $userIDList, $chbox_show_all_hw, $chboxcourse);

//$forumSections = getNotAnsweredForumMessage($courseid, $groupid, $forumid, $teacherCoursesIDList, $userIDList, $chbox_show_all_hw, $chboxcourse);

$html .= '<div class="table-wrapper">';

$html .= '<table id="attempts" class="hw-rightside">';
$html .= '<tr class="header">';
$html .= '<td>Задание</td>';
$html .= '<td>Имя Фамилия</td>';
$html .= '<td>Компания</td>';
$html .= '<td>Попыток</td>';
$html .= ($chbox_show_all_hw == 1) ? '<td>Прошло часов / Оценка</td>' : '<td>Прошло часов</td>';
$html .= ($chbox_show_all_hw == 1) ? '<td>Куратор / Действие</td>' : '<td>Статус</td>';
$html .= '<td>Сообщения</td>';
$html .= '<td>Плагиат'
    . '<form method="get" action="/blocks/hw_tests/plagiat_lib_calculate_all.php">'
    . '<input type="submit" class="white-btn" value="Сравнить">'
    . '</form>'
    .'</td>';
$html .= ($chbox_show_all_hw == 1) ? '<td>Дата проверки</td>' : '';
$html .= '</tr>';


$arrayForReturn = array(
    'courseid' => $courseid,
    'groupid' => $groupid,
    'quizid' => $quizid,
    'chboxcourse' => $chboxcourse,
    'chbox_show_all_hw' => $chbox_show_all_hw,
    'search_query' => $search_query
);

logVarDump($arrayForReturn, ' $arrayForReturn');

$ii = 0;
foreach ($request as $item) {
    $ii++;
    $timeSpent = round((time() - $item->timefinish) / 3600, 0);
    $keyForumSection = $item->course_section . '_' .$item->userid;

    $ar = array('course_name' => $item->shortname,
        'course_id' => $item->course,
        'quiz_attempt_id' => $item->quiz_a_id,
        'quiz_id' => $item->quiz_id,
        'quiz_name' => $item->quiz_name,
        'user_id' => $item->userid,
        'user_name' => $item->lastname . ' ' . $item->firstname,
        'user_company' => $usersGroups[$item->userid . '_' . $item->course],
        'attempt' => $item->attempt,
        'time_start' => $item->timestart,
        'time_end' => $item->timefinish,
        'time_spent' => $timeSpent,
        'teacher' => $userQuizAllAttempt[$item->userid . '_' . $item->quiz_id . '_' . $item->attempt],
        'teacher1' => $userQuizFirstAttempt[$item->userid . '_' . $item->quiz_id . '_' . 1],
//        'onreview' => $userOnReview[$item->course . '_' . $item->quiz_a_id . '_' . $item->attempt],
        'onreview' => 0,
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
    logVarDump($ar, $ii . ' ar');
    $html .= '<tr class="table-row">' . hw_form($ar, $arrayForReturn) . '</tr>';
}
$html .= '</table>';
$html .= '</div>';

$request = getHWforSideMenuByCourses($t_24h, $teacherCoursesIDList, $userIDList, $groupid, $chbox_show_all_hw, $chboxcourse);
logVarDump($request, ' getHWforSideMenuByCourses');
$html .= '<ul class="side-menu">';
if ($chbox_show_all_hw == 1 ){
    foreach ($request as $item) {
        $totHW1 += $item->count_hw;
    }
    $html .= side_menu('Все', $totHW1, $totHW24h, 0, $courseid, $groupid, $chboxcourse, $chbox_show_all_hw, $search_query);
}
else {
    $html .= side_menu('Все', $totHW, $totHW24h, 0, $courseid, $groupid, $chboxcourse, $chbox_show_all_hw, $search_query);
}

$ii = 0;
foreach ($request as $item) {
    $ii++;
    $html .= side_menu($item->shortname, $item->count_hw, $item->count_hw24h, $item->c_id, $courseid, $groupid, $chboxcourse, $chbox_show_all_hw, $search_query);
    if ($item->c_id == $courseid and $courseid !== 0) {
        $subRequest = getHWforSideMenuByQuiz($t_24h, $courseid, $userIDList, $groupid, $chbox_show_all_hw);
        logVarDump($subRequest, $ii . ' $subRequest');
        $html .= '<ul class="side-menu-quiz">';
        foreach ($subRequest as $subItem) {
            $html .= side_menu_quiz($subItem->name, $subItem->count_hw, $subItem->count_hw24h, $subItem->c_m_id, $courseid, $quizid, $groupid, $chboxcourse, $chbox_show_all_hw, $search_query);
        }
        $html .= '</ul>';
    }
}
$html .= '</ul>';
$html .= '</div>';
$html .= '</div>';

echo $OUTPUT->header();
echo $html;

?>