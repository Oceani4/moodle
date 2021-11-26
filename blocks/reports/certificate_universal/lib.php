<?php

//function getCourses()
//{
//    $records = [
//        ['id'=>61, 'fullname'=>'Логистика и ТЭД'],
//        ['id'=>60, 'fullname'=>'Кадровая политика']
//    ];
//
//    return $records;
//}

function getCourses()
{
    global $DB;

    $sql = "SELECT id, fullname
            FROM mdl_course
            ORDER BY fullname";
    $records = $DB->get_records_sql($sql);

    return $records;
}

function getGroups($courseId)
{
    global $DB;

    $sql = "SELECT g.id, g.name
            FROM mdl_groups AS g
            WHERE g.courseid = :course_id";
    $rs = $DB->get_recordset_sql($sql, array('course_id'=>$courseId));

    $records=[];

    foreach ($rs as $r) {
        $group = new stdClass();
        $group->id = $r->id;
        $group->name = $r->name;
        $group->tot_users = getGroupUsersTotal($r->id);
        $records[] = $group;
    }
    $rs->close();

    return $records;
}

function getGroupUsersTotal($groupId)
{
    global $DB;

    $sql = "SELECT COUNT(gm.userid) as total
            FROM mdl_groups_members gm
            WHERE gm.groupid = :group_id";
    $record = $DB->get_record_sql($sql, array('group_id'=>$groupId));

    return $record->total;
}


function getCourseName($courseID)
{
    global $DB;

    $sql = "SELECT c.fullname
            FROM mdl_course c
            WHERE c.id = :course_id ";
    $record = $DB->get_record_sql($sql, array('course_id'=>$courseID));

    return $record->fullname;
}

function getCourseModules($courseID, $groupId)
{
    global $DB;

    $sql = "SELECT m.name  AS module_name, cm.*
            FROM mdl_course_modules cm
            LEFT JOIN mdl_course c ON c.id = cm.course
            LEFT JOIN mdl_modules m ON m.id = cm.module
            WHERE c.id = :course_id";
    $rs = $DB->get_recordset_sql($sql, array('course_id'=>$courseID));

    $resp = new stdClass();
    $resp->modules = [];
    $resp->totalModules = 0;
    $resp->finalTest = [];

    $ww=0;
    foreach ($rs as $record) {
        $ww++;
        $availability = json_decode($record->availability);
        $module = getModule($record->module_name, $record->instance);
        $module->module_name = $record->module_name;
        $module->module_type = $record->module;
        $module->availability = $availability;
        $isAvail = checkAvailability($module, $groupId);

        $parsedModule = strpos($module->intro, '<p>#module');
        $parsedFinalTest = strpos($module->intro, '<p>#final_test');

        if ($parsedModule !== false && $isAvail) {
            $resp->totalModules++;
            $resp->modules[] = $module;
        }
        if ($parsedFinalTest !== false && $isAvail) {
            $resp->finalTest[] = $module;
        }
    }
    $rs->close();

    return $resp;
}

function checkAvailability($module, $groupId)
{
    if ($groupId === 0 || !$module->availability){
        return true;
    }

    if ($module->availability && $module->availability->c) {
        foreach($module->availability->c as $r) {
            if($r->type && $r->id && $r->type === 'group' && $r->id === $groupId) {
                return true;
            }
        }
    }
    return false;
}

function getTableHeaderData($courseId, $groupId)
{
    $resp = getCourseModules($courseId, $groupId);
    return $resp;
}

function getTableData($courseId, $groupId)
{
    $courseModules = getCourseModules($courseId, $groupId);
    $enrolledUsers = getEnrolledUsers($courseId, $groupId);

    $data = [];
    foreach($enrolledUsers as $user) {
        $progress = getUserProgress($courseId, $user->id, $courseModules);
        $user->progress = $progress;

//        $tmpUser = new stdClass();
//        $tmpUser->id = $user->id;
//        $tmpUser->firstname = $user->firstname;
//        $tmpUser->lastname = $user->lastname;
//        $tmpUser->group_name = $user->group_name;
//        $tmpUser->progress = $progress;
        $data[] = $user;
    }

    return $data;
}

function getUserProgress($courseID, $uid, $courseModules)
{
    $progress = new stdClass();
    $progress->totalModulesCompleted = 0;
    $progress->finalTest = [];
    foreach($courseModules->modules as $module) {
        $isModuleCompete = checkCompletion($courseID, $uid, $module);
        $progress->totalModulesCompleted += $isModuleCompete ? 1 : 0;
    }

    $allTestComplete = true;
    foreach($courseModules->finalTest as $test) {
        $grade = getFinalTestGrade($uid, $test);
        $isModuleCompete = checkCompletion($courseID, $uid, $test);
        $status = new stdClass();
        $status->grade = $grade;
        $status->isComplete = $isModuleCompete;
        $allTestComplete = !$isModuleCompete ? false : $allTestComplete;
        $progress->finalTest[$test->id] = $status;
    }
    //--------------------------
    // 1. 80% ззаданий должно быть выполнено --> percent > 80
    // 2. Финальный тест (тесты) должны быть пройдены все!
    $userTotalModules = $progress->totalModulesCompleted;
    $totalModules = $courseModules->totalModules;
    $percent = $totalModules && $totalModules > 0 ? $userTotalModules / $totalModules * 100 : 100;

    $progress->сertificate = $percent > 80 && $allTestComplete ? true : false;

    return $progress;
}

function getFinalTestGrade($uid, $test)
{
    global $DB;

    $sql = "SELECT qg.grade
            FROM mdl_quiz_grades qg 
            WHERE qg.userid = :uid AND qg.quiz = :test_id";
    $record = $DB->get_record_sql($sql, array('uid'=>$uid, 'test_id'=>$test->id));

    return $record && $record->grade ? $record->grade : 0;
}

function checkCompletion($courseID, $uid, $module)
{
    global $DB;

    $sql = "SELECT cmc.completionstate
            FROM mdl_course_modules_completion cmc
            LEFT JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
            WHERE cm.course = :course_id AND cmc.userid = :uid AND cm.module = :module_type AND cm.instance = :module_id ";
    $record = $DB->get_record_sql($sql, array('course_id'=>$courseID, 'uid'=>$uid, 'module_type'=>$module->module_type, 'module_id'=>$module->id));

    return $record && $record->completionstate ? true : false;
}


function getEnrolledUsers($courseId, $groupId)
{
    global $DB;

    $sql = "SELECT u.id, u.firstname, u.lastname, g.name AS group_name, g.id AS group_id
            FROM mdl_user_enrolments ue
            LEFT JOIN mdl_enrol e ON e.id = ue.enrolid
            LEFT JOIN mdl_user u ON u.id = ue.userid
            LEFT JOIN mdl_groups_members gm ON gm.userid = ue.userid
            LEFT JOIN mdl_groups g ON g.id = gm.groupid AND g.courseid = :course_id_1 
            LEFT JOIN mdl_role_assignments ra ON ra.userid = ue.userid
            LEFT JOIN mdl_context c ON c.id = ra.contextid
            WHERE e.courseid = :course_id_2 AND c.instanceid = :course_id_3 AND c.contextlevel = 50 AND ra.roleid = 5 " . ($groupId === 0 ? " " : " AND g.id = :group_id");
    $rs = $DB->get_recordset_sql($sql, array('course_id_1'=>$courseId, 'course_id_2'=>$courseId, 'course_id_3'=>$courseId, 'group_id'=>$groupId));

    $records = [];
    foreach($rs as $record) {
        $records[] = $record;
    }

    $rs->close();

    return $records;
}

function getModule($type, $moduleID)
{
    global $DB;

    $sql = "SELECT m.*
            FROM mdl_" . $type . " m 
            WHERE m.id = :module_id";
    $record = $DB->get_record_sql($sql, array('module_id'=>$moduleID));

    return $record;
}