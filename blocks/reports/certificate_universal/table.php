<?php

function addTable($tableHeaderData, $tableData)
{
    $htmTable = '<div id="page-mod-quiz-report" class="main-table-row">';
    $htmTable .= '<div class="table-wrapper">';
    $htmTable .= '<table id="graded_hw" class="graded_hw">';

    $htmTable .= tableHeader($tableHeaderData);
    $htmTable .= tableRows($tableHeaderData, $tableData);

    $htmTable .= '</table>';
    $htmTable .= '</div>';
    $htmTable .= '</div>';
    return $htmTable;
}

function tableHeader($tableHeaderData) {
    $html = '<tr class="header">';
    $html .= '<td>№</td>';
    $html .= '<td>Отдел</td>';
    $html .= '<td>Ученик</td>';
    $html .= '<td>Кол-во выполненных модулей/Всего модулей</td>';
    $html .= '<td>% выполненных модулей</td>';
    foreach ($tableHeaderData->finalTest as $test){
        $html .= '<td>' . $test->name . '</td>';
    }
    $html .= '<td>Сертификат</td>';
    $html .= '</tr>';

    return $html;
}

function tableRows($tableHeaderData, $tableData)
{
    $html = '';

    foreach($tableData as $user){
        $html .= tableRow($tableHeaderData, $user);
    }

    return $html;
}

function tableRow($tableHeaderData, $user)
{
    static $nn = 0;
    $nn++;
    $userTotalModules = $user->progress->totalModulesCompleted;
    $totalModules = $tableHeaderData->totalModules;
    $percent = $totalModules && $totalModules > 0 ? $userTotalModules / $totalModules * 100 : 100;

    $html = '<tr class="table-row">';
    $html .= '<td>' . $nn . '</td>';
    $html .= '<td>' . $user->group_name . '</td>';
    $html .= '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
    $html .= '<td class="graded-count">' . $userTotalModules . ' / ' . $totalModules . '</td>';
    $html .= '<td class="graded-count">' . number_format($percent, 0) . ' % </td>';

    foreach ($tableHeaderData->finalTest as $test) {
        $grade = $user->progress->finalTest[$test->id]->grade;

        $html .= '<td class="graded-count"><strong>' . ($grade ? number_format($grade, 2) : '--') . '</strong></td>';
    }

    $flagCertificate = $user->progress->сertificate;

    $html .= '<td class="graded-count"><strong>' . ($flagCertificate ? 'V' : '--') . '</strong></td>';

    $html .= '</tr>';

    return $html;
}

