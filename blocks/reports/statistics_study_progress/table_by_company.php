<?php
require_once('../../../config.php');
require_once('lib.php');

global $CFG, $OUTPUT;

$companyID = required_param('companyid',PARAM_INT);
$courseID = required_param('courseid',PARAM_INT);
$moduleCount = optional_param('modulecount',40, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/reports/statistics_study_progress/view.php', array()));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/blocks/reports/statistics_study_progress/view.css');
$director = '';
$records = getDataForTable($courseID, $companyID, 'onlyDirector');
foreach ($records as $dir) {
    if ($dir->role == 'Наблюдающий Директор' || $dir->role == 'Директор + студент') {
        $director = $dir->userDirector;
        break;
    }
}

$html =
    '<div class="letter-main">
        <p>
            Добрый день, '. $director .'! <br><br>
            Меня зовут Ольга, я руководитель отдела кураторов программы “АС мебельных продаж”. <br>
            Я внимательно слежу за тем, как обучается каждый участник программы. <br>
            Еженедельно я рассылаю отчет по прогрессу обучения. <br>
            Я обеспокоена тем, что ваши сотрудники отстают от обучения: <br>
        </p>
    </div>';
$html .='';

$records = getDataForTable($courseID, $companyID, 'onlyStudents', 'emailForCompany');

$newRec = [];
foreach($records as $record) {
    $rest = $moduleCount - $record->sectionNumber;
    $key = ($rest < 10 ? '0' . $rest : $rest) . '_' . $record->user;
    $newRec[$key] = $record;
}

ksort($newRec);

$html .= addTable($newRec);

$today = time();
$minStartTime = 0;
foreach($records as $record) {
    if ($record->enrollStart > 0 && ( $record->enrollStart < $minStartTime || $minStartTime === 0)) {
        $minStartTime = $record->enrollStart;
    }
}

$studyDay = ceil(($today - $minStartTime) / (60 * 60 * 24));

$html .=
    '<div class="letter-main">
        <br><br>
        <p class="letter-attention"> Напоминаю, сегодня уже ' . $studyDay . '-й день обучения!</p>
     
        <p>
            Такими темпами сотрудники могут не успеть изучить весь объём необходимой информации и вовремя закончить обучение. <br> 
            Ваша компания уже инвестировала деньги в обучение своих сотрудников. <br>
            Каков будет возврат на инвестиции? <br>
            И будет ли он вообще при таком отношении к обучению? <br> 
        </p>
        <p> 
            <strong>Начните получать результаты уже сейчас! </strong><br> 
            Приведите своих продавцов на обучение, остальное мы сделаем за вас. <br>   
            Нужно стартовать уже сейчас, чтобы уже сейчас во время обучения ваши продажи пошли вверх. <br>   
            Внизу указаны мои контактные данные. <br> 
            Если у Вас будут вопросы по входу в программу и по обучению, звоните, мы Вам поможем. 
        </p>    
        <div class="letter-orange">
            <p>
                Больших Вам мебельных продаж!  <br>
             </p>  
        </div>
    </div>';


echo $OUTPUT->header();
echo $html;

function addTable($records) {
    $html = '<div id="page-mod-quiz-report" class="main-table-row">';
    $html .= '<div class="table-wrapper">';
    $html .= '<table id="graded_hw" class="graded_hw">';

    $nn = 0;
    $html .= tableHeader();
    foreach ($records as $record) {
        $nn++;
        $company = $record->company;
        $companyID = $record->companyID;
        $user = $record->user;
        $phone = $record->phone;
        $dateModule = $record->dateModule;
        $module = $record->sectionNumber;
        $role_name = $record->role;

        $html .= tableRow($nn, $company, $companyID, $user, $phone, $role_name, $dateModule, $module);
    }
    $html .= '</table>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

function tableHeader() {
    $html = '<tr class="header">';
    $html .= '<td>Компания</td>';
    $html .= '<td>Ученик</td>';
    $html .= '<td>Последний раз заходил на платформу</td>';
    $html .= '<td>Выполнено ДЗ</td>';
    $html .= '<td>Осталось выполнить ДЗ</td>';
    $html .= '</tr>';

    return $html;
}

function tableRow($nn, $company, $companyID, $user, $phone, $roleName, $dateModule, $module) {
    global $moduleCount;

    $html = '<tr class="table-row">';
    $html .= '<td>' . $company . '</td>';
    $html .= '<td>' . $user . '</td>';
    $html .= '<td class="align-center">' . $dateModule . '</td>';
    $html .= '<td class="module-name align-center">' . $module . '</td>';
    $html .= '<td class="module-name align-center">' . ($moduleCount - $module) . '</td>';
    $html .= '</tr>';

    return $html;
}