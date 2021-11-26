<?php
require_once('../../../config.php');
require_once($CFG->dirroot.'/lib/logVarDump.php');
require_once('lib.php');
require_once('table.php');

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid',PARAM_INT);
$lesson = optional_param('lesson',                                                                                      1,PARAM_INT);

require_login();
if (!has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))){
    $url = new moodle_url('/');
    redirect($url);
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/reports/statistics_study_progress/view.php', array()));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/blocks/reports/statistics_study_progress/view.css');

$previewnode = $PAGE->navigation->add('Журнал ответов', new moodle_url('/my/index.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Отчет по прогрессу обучения', new moodle_url('/blocks/reports/statistics_study_progress/view.php', array()));
$thingnode->make_active();

$html = '';

$courseName = getCourseName($courseid);
$section = getSectionList($courseid, $lesson);
$sections = getSections($courseid);
$studentsCount = getStudentsCount($courseid, $section);
$studentsTotal = getStudentsCountAll($courseid);
//$records = getDataForTable($courseid);
$records = getDataForTable2($courseid);
$statistics = fillStatistics($records, $sections, $studentsTotal);
$studentsPercent = (empty($studentsTotal) or empty($lesson))? "-" : round($studentsCount/$studentsTotal*100,2) . "%";

$html .= '<h1> Отчет по прогрессу обучения на ' . date('d.m.Yг.') . '</h1>';
$html .= '<h3> По курсу: ' . $courseName . '</h3>';

$html .= '<form method="get" action="/blocks/reports/statistics_study_progress/view.php">';
$html .= '<select class="course-select" name="courseid" required> ';
$courses = getCourses();
$html .= (($courseid != 0) ? '<option value="0" selected> Все </option>' : '<option value="0"> Все </option>');
foreach ($courses as $course) {
    $html .= (($courseid == $course->id) ? '<option value="' . $course->id . '" selected>' . $course->name . '</option>' :
        '<option value="' . $course->id . '">' . $course->name . '</option>');
}
$html .= '</select>';
$html .= '<input type="submit" value="Обновить" name>';
$html .= '<p class="input-text">Дошло до <input class="input-number" type="number" min="1" max="40" name="lesson" value="' . $lesson . '"> урока: ' . $studentsCount
            . ' из ' . $studentsTotal . ' студентов, что составляет: ' . $studentsPercent . '</p>';
$html .= '</form>';

$html .= addTable($records, $courseid, count($statistics));
//$html .= addTable1($records, $courseid, count($statistics));
$html .= addStatistics($statistics);

echo $OUTPUT->header();
echo $html;
?>
