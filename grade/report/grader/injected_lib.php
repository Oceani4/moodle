<?php
require_once('../../../lib/logVarDump.php');

function getTheoriesForHeader($courseId)
{
    global $DB;

    $sql = "SELECT m.name, cm.*
            FROM mdl_course_modules cm
            LEFT JOIN mdl_modules m ON m.id = cm.module
            WHERE cm.module IN (3, 5, 8, 15) AND cm.course = :course_id";

    $rs = $DB->get_recordset_sql($sql, array('course_id'=>$courseId));

    $resp = [];

    foreach ($rs as $r) {
        $r->title = getModuleTitle($r->name, $r->instance);
        array_push($resp, $r);
    }
    return $resp;
}

function getUserTheories($courseId, $userId)
{
    global $DB;

    $sql = "SELECT u.id as uid, u.firstname, u.lastname, m.name, cm.instance, cm.id, cmc.completionstate, cmc.timemodified, cm.course
            FROM mdl_course_modules_completion cmc
            LEFT JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
            LEFT JOIN mdl_modules m ON m.id = cm.module
            LEFT JOIN mdl_user u ON u.id = cmc.userid
            WHERE u.id = :user_id AND cm.course = :course_id";

    $rs = $DB->get_recordset_sql($sql, array('user_id'=>$userId, 'course_id'=>$courseId));

    $resp = new stdClass();

    foreach ($rs as $r) {
        $r->title = getModuleTitle($r->name, $r->instance);
        $key = 'user_' . $r->uid;
        $moduleKey = $r->name . '_' . $r->id;
        $resp->{$moduleKey} = $r;
    }
    return $resp;
}

function getModuleTitle($moduleType, $moduleId)
{
    switch($moduleType) {
        case 'book':
        case 'choice':
        case 'folder':
        case 'scorm':
        case 'lesson':
        case 'resource':
        case 'page': return getTitle($moduleType, $moduleId);
        default: return '';
    }
}

function getTitle($moduleName, $moduleId)
{
    global $DB;

    $sql = "SELECT m.name
            FROM mdl_" . $moduleName ." m
            WHERE m.id = :module_id";

    $record = $DB->get_record_sql($sql, array('module_id'=>$moduleId));

    return $record ? $record->name : '';
}

function generateHeaderCell($courseId, $moduleType, $moduleTitle, $courseModuleId)
{
    $headerRow = new html_table_cell();
    $headerRow->id = Null;
    $headerRow->text = getHeaderLink($moduleType, $courseModuleId, $moduleTitle, $courseId);
    $headerRow->abbr = NULL;
    $headerRow->colspan = 1;
    $headerRow->rowspan = NULL;
    $headerRow->scope = 'col';
    $headerRow->header = true;
    $headerRow->style = NULL;

    return $headerRow;
}

function getHeaderLink($moduleType, $courseModuleId, $moduleTitle, $courseId)
{
    return html_writer::link(new moodle_url(getModuleUrl($moduleType, $courseId), array('id' => $courseModuleId)), $moduleTitle, array(
        'class' => 'action-icon',
    ));
}

function getModuleUrl($moduleType, $courseId)
{
    switch ($moduleType){
        case 'book':
        case 'choice':
        case 'folder':
        case 'scorm':
        case 'lesson':
        case 'resource':
        case 'page': return '/mod/'. $moduleType .'/view.php';
        default: return '/course/view.php?id='. $courseId;
    }
}

function generateCell($userId, $cellValue, $moduleType, $moduleId)
{
    $row = new html_table_cell();
    $row ->id = 'u'.$userId.'i';
    $_span = "<span class='gradevalue gradepass'>" . ($cellValue->completionstate
        ? 'V (' . date('d.m.Y H:i', $cellValue->timemodified) . ')' : '-') . "</span>";
    $_i = '<i class="icon fa fa-search-plus fa-fw "  title="Анализ оценок" aria-label="Анализ оценок">
	</i>';
    $_a = html_writer::link(
        new moodle_url(getModuleUrl($moduleType, $cellValue->course), array('id' => $moduleId)),
        $_i, array('class' => 'action-icon')
    );
    $row->text = $_span . $_a;
    $row->abbr = NULL;
    $row->colspan = NULL;
    $row->rowspan = NULL;
    $row->scope = NULL;
    $row->header = NULL;
    $row->style = NULL;
    $row->attributes =
      array (
          'class' => ' grade course grade_type_value',
          'data-itemid' => NULL,
      );

    return $row;
}


