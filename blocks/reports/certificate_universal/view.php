<?php
require_once('../../../config.php');
require_once('lib.php');
require_once('table.php');

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid',PARAM_INT);
$groupid = optional_param('groupid',-1,PARAM_INT);

require_login();
if (!has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))){
    $url = new moodle_url('/');
    redirect($url);
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/reports/certificate_universal/view.php', array()));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/blocks/reports/certificate_universal/view.css');

$previewnode = $PAGE->navigation->add('Все отчеты', new moodle_url('/my/index.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Отчет по сертификатам Универсальный', new moodle_url('/blocks/reports/certificate_universal/view.php', array()));
$thingnode->make_active();

$html = '';

$courses = getCourses();
$groups = getGroups($courseid);

$html .= '<form method="get" action="/blocks/reports/certificate_universal/view.php">';

$html .= '<select class="course-select" name="courseid" required> ';
foreach ($courses as $course) {
//    $html .= (($courseid == $course['id']) ? '<option value="' . $course['id'] . '" selected>' . $course['fullname'] . '</option>' :
//        '<option value="' . $course['id'] . '">' . $course['fullname'] . '</option>');
    $html .= (($courseid == $course->id) ? '<option value="' . $course->id . '" selected>' . $course->fullname . '</option>' :
        '<option value="' . $course->id . '">' . $course->fullname . '</option>');
}
$html .= '</select>';

if ($groupid === -1 && count($groups) > 0) {
    $groupid = $groups[0]->id;
}

$html .= '<select class="course-select" name="groupid" required> ';
//$html .= (($groupid != 0) ? '<option value="0" selected> Все </option>' : '<option value="0"> Все </option>');
foreach ($groups as $group) {
    $html .= (($groupid == $group->id) ? '<option value="' . $group->id . '" selected>' . $group->name . ' (' . $group->tot_users . ')</option>' :
        '<option value="' . $group->id . '">' . $group->name . ' (' . $group->tot_users . ')</option>');
}
$html .= '</select>';

$html .= '<input type="submit" value="Обновить">';
$html .= '</form>';

$courseName = getCourseName($courseid);

$html .= '<h1> Отчет по сертификатам (универсальный) на ' . date('d.m.Yг.') . '</h1>';
$html .= '<h3> По курсу: ' . $courseName . '</h3>';

$tableHeaderData = getTableHeaderData($courseid, $groupid);
$tableData = getTableData($courseid, $groupid);


$html .= addTable($tableHeaderData, $tableData);
//$records = getDataForTable($courseid, $companyid);
//$companies = getCompanies($courseid, $companyid);
//$finalTests = getFinalTests($courseid);
//$roles = getUsersRoles($courseid, $companyid);
//$maxArrow = getTotalHW($courseid);
//$html .= addTable($records, $finalTests, $companies, $maxArrow, $roles);

echo $OUTPUT->header();
echo $html;

