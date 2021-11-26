<?php

function getCourses()
{
    global $DB;

    $sql = "SELECT id, fullname as name
            FROM mdl_course
            ORDER BY fullname";
    $records = $DB->get_records_sql($sql);

    return $records;
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

function getTeachersForCourse($courseID)
{
    global $DB;

    $sql = "SELECT u.lastname
            FROM mdl_user u
            LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
            LEFT JOIN mdl_context cntx ON cntx.id = ra.contextid
            LEFT JOIN mdl_course c ON c.id = cntx.instanceid
            WHERE cntx.contextlevel=50 AND c.id = :course_id AND ra.roleid IN (1, 2, 3, 4, 23)";
    $records = $DB->get_records_sql($sql, array('course_id'=>$courseID));

    return $records;
}

function getRoleID($role){
    if ($role == 'onlyStudents') {
        return '(5, 22, 28)';
    }
    if ($role == 'onlyDirector') {
        return '(9, 22)';
    }
    return '(5, 9, 22, 28)';
}

function getClearedCompanyName($company, $teachers)
{
    $clearedName = str_replace('Глобальная группа ', '', $company);
    $clearedName = str_replace('_', '', $clearedName);
    foreach ($teachers as $teacher) {
        $clearedName = str_replace($teacher->lastname, '', $clearedName);
    }

    return $clearedName;
}

function getDataForTable($courseID, $companyID, $role)
{
    global $DB;

    $teachers = getTeachersForCourse($courseID);

    $sql = "SELECT sub.*
            FROM (
                SELECT u.id as user_id, u.firstname, u.lastname, u.middlename, u.email,
                r.id as r_id, ra.id as ra_id,
                 u.phone1, cm.course, DATE_FORMAT(FROM_UNIXTIME(ul.timeaccess), '%d.%m.%Y %H:%i') as date_f, cs.name as module_name, 
                g.id as group_id, g.name as company, cm.section as section_id, cs.section,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(ue.timestart), 'UTC', '+03:00'), '%d.%m.%Y') as time_start, 
                ue.timestart as time_start_timestamp, 
                ue.timeend as time_end_timestamp, 
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(ue.timeend), 'UTC', '+03:00'), '%d.%m.%Y') as time_end  
                FROM mdl_course_modules_completion cmc
                LEFT JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
                LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
                LEFT JOIN mdl_user u ON u.id = cmc.userid
                LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
                LEFT JOIN mdl_role r ON r.id = ra.roleid
                LEFT JOIN mdl_context ctx ON ctx.id = ra.contextid
                LEFT JOIN mdl_user_lastaccess ul ON ul.userid = u.id
                LEFT JOIN (
                    SELECT *
                    FROM mdl_groups sub_g
                    WHERE sub_g.id NOT IN (66,67,68,69,70,71,72,77,135,136,137,138,139,140,141)
                ) g ON g.courseid = cm.course
                LEFT JOIN mdl_groups_members gm ON gm.groupid = g.id
                LEFT JOIN mdl_user_enrolments ue ON ue.userid = u.id
                LEFT JOIN mdl_enrol e ON e.id = ue.enrolid
                WHERE gm.userid = u.id
                and cm.course = :course_id_1
                and cm.module IN (1, 13, 16, 18)
                and cmc.completionstate > 0  
                and ra.roleid IN (5, 22, 28)
                and ctx.contextlevel = 50 and ctx.instanceid = :course_id_2
                and ul.courseid = :course_id_3 
                and e.courseid = :course_id_4"
                . (empty($companyID) ? ' ' : ' and g.id = :company_id') .
                "
                GROUP BY cmc.userid, gm.id, cm.section
                ORDER BY g.id, u.id, cs.section DESC
            ) sub 
            GROUP BY sub.user_id, sub.group_id
            ORDER BY sub.group_id, sub.user_id";

    $params = array('course_id_1'=>$courseID, 'course_id_2'=>$courseID, 'course_id_3'=>$courseID, 'course_id_4'=>$courseID, 'company_id' => $companyID);

    $rs = $DB->get_recordset_sql($sql, $params);

    foreach ($rs as $record) {
        switch($record->r_id) {
            case 9:     $keyRole = 1; break;
            case 22:    $keyRole = 2; break;
            default :   $keyRole = 3;
        }

        $key = $record->company . '_' . $keyRole . '_' . $record->user_id;
        $keys[] = $key;
        $value = new stdClass();
        $value->company = getClearedCompanyName($record->company, $teachers);
        $value->companyID = $record->group_id;
        $value->r_id = $record->r_id;
        $value->user = $record->firstname . ' ' . $record->lastname;
        $value->userDirector = empty($record->middlename) ? $record->firstname . ' ' . $record->lastname : $record->firstname . ' ' . $record->middlename;
        $value->email = $record->email;
        $value->phone = $record->phone1;
        $value->course = $record->course;
        $value->enrollPeriod = $record->time_start . ' - ' . $record->time_end;
        $value->enrollStart = intval($record->time_start_timestamp);
        $value->enrollEnd = intval($record->time_end_timestamp);
        $value->dateModule = $record->date_f;
        $value->module = $record->module_name;
        $value->sectionID = $record->section_id;
        $value->sectionNumber = $record->section;
        $records[$key] = $value;
    }
    $rs->close();

    $sql = "SELECT g.id as group_id, g.name as company, g.courseid as course, u.id as user_id, u.firstname, u.lastname, u.middlename, u.email, u.phone1, r.name as role_name, r.id as r_id
            FROM mdl_groups g 
            LEFT JOIN mdl_groups_members gm ON gm.groupid = g.id
            LEFT JOIN mdl_user u ON u.id = gm.userid
            LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
            LEFT JOIN mdl_role r ON r.id = ra.roleid
            LEFT JOIN mdl_context ctx ON ctx.id = ra.contextid
            WHERE g.courseid = :course_id_1 and ctx.contextlevel = 50 and ctx.instanceid = :course_id_2
            and ra.roleid IN " . getRoleID($role)  ."
            and g.id NOT IN (66,67,68,69,70,71,72,77,135,136,137,138,139,140,141) "
            . (empty($companyID) ? ' ' : ' and g.id = :company_id') .
            "
            ORDER BY g.id, u.id";

    $params = Array('course_id_1' => $courseID, 'course_id_2' => $courseID, 'company_id' => $companyID);

    $rs = $DB->get_recordset_sql($sql, $params);

    foreach ($rs as $record) {
        switch($record->r_id) {
            case 9:     $keyRole = 1; break;
            case 22:    $keyRole = 2; break;
            default :   $keyRole = 3;
        }
        $key = $record->company . '_' . $keyRole . '_' . $record->user_id;

        if (array_key_exists($key, $records)) {
            $value = $records[$key];
        }
        else {
            $value = new stdClass();
//            $value->company = str_replace('Глобальная группа ', '', $record->company);
            $value->company = $value->company = getClearedCompanyName($record->company, $teachers);
            $value->companyID = $record->group_id;
            $value->r_id = $record->r_id;
            $value->user = $record->firstname . ' ' . $record->lastname;
            $value->userDirector = empty($record->middlename) ? $record->firstname . ' ' . $record->lastname : $record->firstname . ' ' . $record->middlename;
            $value->email = $record->email;
            $value->phone = $record->phone1;
            $value->course = $record->course;
            $value->enrollPeriod = ' ---- ';
            $value->enrollStart = ' ---- ';
            $value->enrollEnd = ' ---- ';
            $value->dateModule = ' ---- ';
            $value->module = ' ---- ';
            $value->sectionID = -1;
            $value->sectionNumber = 0;
        }
        if (!empty($record->role_name)){
            $value->role = $record->role_name;
        }
        $records[$key] = $value;
    }
    $rs->close();

    ksort($records);

    return $records;
}

function getDataForTable2($courseID)
{
    global $DB;

    $teachers = getTeachersForCourse($courseID);

    $sql = "SELECT sub.*
            FROM (
                SELECT u.id as user_id, u.firstname, u.lastname, u.middlename, u.email,
                r.id as r_id, ra.id as ra_id,
                 u.phone1, cm.course, DATE_FORMAT(FROM_UNIXTIME(ul.timeaccess), '%d.%m.%Y %H:%i') as date_f, cs.name as module_name, 
                g.id as group_id, g.name as company, cm.section as section_id, cs.section,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(ue.timestart), 'UTC', '+03:00'), '%d.%m.%Y') as time_start, 
                ue.timestart as time_start_timestamp, 
                ue.timeend as time_end_timestamp, 
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(ue.timeend), 'UTC', '+03:00'), '%d.%m.%Y') as time_end  
                FROM mdl_course_modules_completion cmc
                LEFT JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
                LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
                LEFT JOIN mdl_user u ON u.id = cmc.userid
                LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
                LEFT JOIN mdl_role r ON r.id = ra.roleid
                LEFT JOIN mdl_context ctx ON ctx.id = ra.contextid
                LEFT JOIN mdl_user_lastaccess ul ON ul.userid = u.id
                LEFT JOIN (
                    SELECT *
                    FROM mdl_groups sub_g
                    WHERE sub_g.id NOT IN (66,67,68,69,70,71,72,77,135,136,137,138,139,140,141)
                ) g ON g.courseid = cm.course
                LEFT JOIN mdl_groups_members gm ON gm.groupid = g.id
                LEFT JOIN mdl_user_enrolments ue ON ue.userid = u.id
                LEFT JOIN mdl_enrol e ON e.id = ue.enrolid
                WHERE gm.userid = u.id
                and cm.course = :course_id_1
                and cm.module IN (1, 13, 16, 18)
                and cmc.completionstate > 0  
                and ra.roleid IN (5, 22, 28)
                and ctx.contextlevel = 50 and ctx.instanceid = :course_id_2
                and ul.courseid = :course_id_3 
                and e.courseid = :course_id_4
                GROUP BY cmc.userid, gm.id, cm.section
                ORDER BY g.id, u.id, cs.section DESC
            ) sub 
            GROUP BY sub.user_id, sub.group_id
            ORDER BY sub.group_id, sub.user_id";

    $params = array('course_id_1'=>$courseID, 'course_id_2'=>$courseID, 'course_id_3'=>$courseID, 'course_id_4'=>$courseID);

    $rs = $DB->get_recordset_sql($sql, $params);


    foreach ($rs as $record) {
        switch($record->r_id) {
            case 9:     $keyRole = 1; break;
            case 22:    $keyRole = 2; break;
            default :   $keyRole = 3;
        }

        $key = $record->company . '_' . $keyRole . '_' . $record->user_id;
        $keys[] = $key;
        $value = new stdClass();
        $value->company = getClearedCompanyName($record->company, $teachers);
        $value->companyID = $record->group_id;
        $value->r_id = $record->r_id;
        $value->user = $record->firstname . ' ' . $record->lastname;
        $value->userDirector = empty($record->middlename) ? $record->firstname . ' ' . $record->lastname : $record->firstname . ' ' . $record->middlename;
        $value->email = $record->email;
        $value->phone = $record->phone1;
        $value->course = $record->course;
//        $value->enrollPeriod = $record->time_start . ' - ' . $record->time_end;
//        $value->enrollPeriod = $record->time_start . ' - ' . (intval($record->time_end_timestamp) == 0 ? ' ' . chr(236) : $record->time_end);
        $value->enrollPeriod = $record->time_start . ' - ' . (intval($record->time_end_timestamp) == 0 ? '' : $record->time_end);
        $value->enrollStart = intval($record->time_start_timestamp);
        $value->enrollEnd = intval($record->time_end_timestamp);
        $value->dateModule = $record->date_f;
        $value->module = $record->module_name;
        $value->sectionID = $record->section_id;
        $value->sectionNumber = $record->section;
        $records[$key] = $value;
    }
    $rs->close();

    $sql = "SELECT g.id as group_id, g.name as company, g.courseid as course, u.id as user_id, u.firstname, u.lastname, u.middlename, u.email, u.phone1, r.name as role_name, r.id as r_id
            FROM mdl_groups g 
            LEFT JOIN mdl_groups_members gm ON gm.groupid = g.id
            LEFT JOIN mdl_user u ON u.id = gm.userid
            LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
            LEFT JOIN mdl_role r ON r.id = ra.roleid
            LEFT JOIN mdl_context ctx ON ctx.id = ra.contextid
            WHERE g.courseid = :course_id_1 and ctx.contextlevel = 50 and ctx.instanceid = :course_id_2
            and g.id NOT IN (66,67,68,69,70,71,72,77,135,136,137,138,139,140,141) 
            ORDER BY g.id, u.id";

    $params = Array('course_id_1' => $courseID, 'course_id_2' => $courseID );

    $rs = $DB->get_recordset_sql($sql, $params);

    foreach ($rs as $record) {
        switch($record->r_id) {
            case 9:     $keyRole = 1; break;
            case 22:    $keyRole = 2; break;
            default :   $keyRole = 3;
        }
        $key = $record->company . '_' . $keyRole . '_' . $record->user_id;

        if (array_key_exists($key, $records)) {
            $value = $records[$key];
        }
        else {
            $value = new stdClass();
//            $value->company = str_replace('Глобальная группа ', '', $record->company);
            $value->company = $value->company = getClearedCompanyName($record->company, $teachers);
            $value->companyID = $record->group_id;
            $value->r_id = $record->r_id;
            $value->user = $record->firstname . ' ' . $record->lastname;
            $value->userDirector = empty($record->middlename) ? $record->firstname . ' ' . $record->lastname : $record->firstname . ' ' . $record->middlename;
            $value->email = $record->email;
            $value->phone = $record->phone1;
            $value->course = $record->course;
            $value->enrollPeriod = ' ---- ';
            $value->enrollStart = ' ---- ';
            $value->enrollEnd = ' ---- ';
            $value->dateModule = ' ---- ';
            $value->module = ' ---- ';
            $value->sectionID = -1;
            $value->sectionNumber = 0;
        }
        if (!empty($record->role_name)){
            $value->role = $record->role_name;
        }
        $records[$key] = $value;
    }
    $rs->close();

    ksort($records);

    return $records;
}

function getSectionList($courseID, $lesson = 10)
{
    global $DB;

    $sql = "SELECT cm.section
            FROM mdl_course_modules cm
            LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
            WHERE cm.course = :course_id AND cm.module IN (1, 2, 3, 8, 13, 15, 16, 18)
            GROUP BY cs.id
            ORDER BY cs.section";
    $rs = $DB->get_recordset_sql($sql, array('course_id'=>$courseID));

    $count = 0;

    foreach ($rs as $record) {
        if ($count == $lesson) {
            return $record->section;
        }
        $count++;
    }
    $rs->close();

    return '';
}

function getSections($courseID)
{
    global $DB;

    $sql = "SELECT cm.section
            FROM mdl_course_modules cm
            LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
            WHERE cm.course = :course_id AND cm.module IN (1, 2, 3, 8, 13, 15, 16, 18)
            GROUP BY cs.id
            ORDER BY cs.section";
    $rs = $DB->get_recordset_sql($sql, array('course_id'=>$courseID));

    foreach ($rs as $record) {
        $sections[]=$record->section;
    }
    $rs->close();

    return $sections;
}

function fillStatistics($records, $sections, $studentsTotal)
{
    if (empty($studentsTotal)){
        return '';
    }

    $statistics = [];

    foreach($records as $record) {
        if ($record->sectionID == -1) {
            continue;
        }
        $key = array_search($record->sectionID, $sections);
        $key = empty($key) ? 0 : $key;
        $statistics[$key] = (array_key_exists($key, $statistics)? $statistics[$key]:0) + 1;
    }

    $total =0;

    for ($i = count($sections)-1; $i>0; $i--) {
        $total += $statistics[$i];
        $value = new stdClass();
        $value->amount = $total;
        $value->percent = round($total/$studentsTotal*100,0);

        $finalStatistics[$i] = $value;
    }

    ksort($finalStatistics);

    return $finalStatistics;
}

function getStudentsCount($courseID, $section)
{
    global $DB;

    if (empty($section)){
        return 0;
    }

    $sql = "SELECT Count(DISTINCT u.id) as users
            FROM mdl_course_modules_completion cmc
            LEFT JOIN mdl_course_modules cm ON cm.id = cmc.coursemoduleid
            LEFT JOIN mdl_course_sections cs ON cs.id = cm.section
            LEFT JOIN mdl_user u ON u.id = cmc.userid
            LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
            LEFT JOIN mdl_context ctx ON ctx.id = ra.contextid
            LEFT JOIN mdl_user_lastaccess ul ON ul.userid = u.id
            LEFT JOIN (
              SELECT *
              FROM mdl_groups sub_g
              WHERE sub_g.id NOT IN (66,67,68,69,70,71,72,77,135,136,137,138,139,140,141)
            ) g ON g.courseid = cm.course
            LEFT JOIN mdl_groups_members gm ON gm.groupid = g.id
            WHERE gm.userid = u.id
            and cm.course = :course_id_1
            and cm.module IN (1, 13, 16, 18)
            and cmc.completionstate > 0 
            and ra.roleid IN (5, 22, 28)
            and ctx.contextlevel = 50 and ctx.instanceid = :course_id_2
            and ul.courseid = :course_id_3 "
            . ($section == "ALL" ? "" : " and cm.section IN (" . $section . ")");

    $record = $DB->get_record_sql($sql,array('course_id_1'=>$courseID, 'course_id_2'=>$courseID, 'course_id_3'=>$courseID));

    return $record->users;

}

function getStudentsCountAll($courseID)
{
    global $DB;

    $sql = "SELECT count(DISTINCT u.id) as userscount
            FROM mdl_user u
            LEFT JOIN mdl_role_assignments ra ON ra.userid = u.id
            LEFT JOIN mdl_context cntx ON cntx.id = ra.contextid
            LEFT JOIN mdl_course c ON c.id = cntx.instanceid
            WHERE cntx.contextlevel = 50 and
            ra.roleid IN (5, 22, 28) and
            c.id = :course_id";

    $record = $DB->get_record_sql($sql,array('course_id'=>$courseID));

    return $record->userscount;
}
