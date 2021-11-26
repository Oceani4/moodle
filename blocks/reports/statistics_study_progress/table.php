<?php

function addTable($records, $courseID, $moduleCount) {
    $html = '<div id="page-mod-quiz-report" class="main-table-row">';
    $html .= '<div class="table-wrapper">';
    $html .= '<table id="graded_hw" class="graded_hw">';

    $nn = 0;
    $html .= tableHeader();

    $today = time();

    $r = [];
    $currentCompany = -1;
    $currentStudy = false;

    foreach ($records as $record) {
        if ($currentCompany !== $record->company && $currentCompany !== -1) {
            foreach ($r as $key => $value) {
                $company = $value->company;
                $companyID = $value->companyID;
                $user = $value->user;
                $email = $value->email;
                $phone = $value->phone;
                $enrollPeriod = $value->enrollPeriod;
                $dateModule = $value->dateModule;
                $module = $value->module;
                $role_name = $value->role;
                $html .= tableRow($key, $company, $companyID, $user, $email, $phone, $role_name, $dateModule, $module, $courseID, $moduleCount, $value->r_id, $enrollPeriod, $currentStudy);
            }
            $r = [];
            $currentStudy = false;
        }

        $nn++;
        $currentCompany = $record->company;
        if ($record->enrollStart !== ' ---- ' && $record->enrollStart < $today && $today < $record->enrollEnd ) {
            $currentStudy = true;
        }
        $r[$nn] = $record;

    }
    foreach ($r as $key => $value) {
        $company = $value->company;
        $companyID = $value->companyID;
        $user = $value->user;
        $email = $value->email;
        $phone = $value->phone;
        $enrollPeriod = $value->enrollPeriod;
        $dateModule = $value->dateModule;
        $module = $value->module;
        $role_name = $value->role;
        $html .= tableRow($key, $company, $companyID, $user, $email, $phone, $role_name, $dateModule, $module, $courseID, $moduleCount, $value->r_id, $enrollPeriod, $currentStudy);
    }
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

function tableHeader() {
    $html = '<tr class="header">';
    $html .= '<td>№</td>';
    $html .= '<td>Компания</td>';
    $html .= '<td>Ученик</td>';
    $html .= '<td>E-Mail</td>';
    $html .= '<td>Телефон</td>';
    $html .= '<td>Роль</td>';
    $html .= '<td>Период обучения</td>';
    $html .= '<td>Дата последнего входа на Курс</td>';
    $html .= '<td>Занятие</td>';
    $html .= '</tr>';

    return $html;
}

function tableRow($nn, $company, $companyID, $user, $email, $phone, $roleName, $dateModule, $module, $courseID, $moduleCount, $r_id, $enrollPeriod, $currentStudy) {
    $rowColor = $currentStudy ?  ($r_id==9 || $r_id==22 ? ' role-red' : '') : ($r_id==9 || $r_id==22 ? ' role-red-gray' : ' role-gray');
    $html = '<tr class="table-row'. $rowColor .'">';
    $html .= '<td>' . $nn . '</td>';
    $url = new moodle_url('/blocks/reports/statistics_study_progress/table_by_company.php', array('courseid' => $courseID , 'companyid' => $companyID, 'modulecount' => $moduleCount ));
    $link = html_writer::link($url, $company, array('target'=>'_blank'));
    $html .= '<td>' . $link . '</td>';
    $html .= '<td>' . $user . '</td>';
    $html .= '<td class="align-center">' . $email . '</td>';
    $html .= '<td class="align-center">' . $phone . '</td>';
    $html .= '<td class="align-center' . $rowColor . '">' . $roleName . '</td>';
    $html .= '<td class="align-center">' . $enrollPeriod . '</td>';
    $html .= '<td class="align-center">' . $dateModule . '</td>';
    $html .= '<td class="module-name align-center">' . $module . '</td>';
    $html .= '</tr>';

    return $html;
}

function addStatistics($statistics)
{
    $html = '<div id="page-mod-quiz-report" class="main-table-row">';
    $html .= '<div class="table-wrapper">';
    $html .= '<table id="graded_hw" class="graded_hw">';

    $nn = 0;
    $html .= statHeader();
    foreach ($statistics as $record) {
        $nn++;
        $html .= '<tr class="table-row">';
        $html .= '<td>' . $nn . '</td>';
        $html .= '<td class="align-center">' . $record->amount . '</td>';
        $html .= '<td class="align-center">' . $record->percent . '%</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

function statHeader() {
    $html = '<tr class="header">';
    $html .= '<td>№</td>';
    $html .= '<td>Кол-во учеников, которые выполнили ДЗ</td>';
    $html .= '<td>% учеников, кто выполняет ДЗ</td>';
    $html .= '</tr>';

    return $html;
}
