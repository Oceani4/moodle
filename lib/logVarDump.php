<?php
/**
 *  для подключения данного файла, в начале файла поставьте следующую строчку
 *  require_once($CFG->dirroot.'/lib/logVarDump.php');
 *
 * @param $s - имя переменной для дампа
 * @param $name - имя файла
 */
function logVarDump($s, $name)
{
    global $CFG;

    file_put_contents($CFG->dirroot.'/lib/1/_' . $name . '.txt', "--------\r\n" . $name . ": \r\n");
    ob_start();
    var_export($s);
    echo "\r\n\r\n--------\r\n\r\n";
    var_dump($s);

    $msg = ob_get_clean();
    file_put_contents($CFG->dirroot.'/lib/1/_' . $name . '.txt', $msg . "\r\n", FILE_APPEND);
}

function logVarDumpAdd($s, $name)
{
    global $CFG;

    file_put_contents($CFG->dirroot.'/lib/1/_' . $name . '.txt', "--------\r\n" . $name . ": \r\n",FILE_APPEND);
    ob_start();
    var_export($s);
    echo "\r\n\r\n--------\r\n\r\n";
    var_dump($s);

    $msg = ob_get_clean();
    file_put_contents($CFG->dirroot.'/lib/1/_' . $name . '.txt', $msg . "\r\n", FILE_APPEND);
}

function monthsEn2Ru($date) {
    $months = ['January' => 'Января',
        'February'  => 'Февраля',
        'March'     => 'Марта',
        'April'     => 'Апреля',
        'May'       => 'Мая',
        'June'      => 'Июня',
        'July'      => 'Июля',
        'August'    => 'Августа',
        'September' => 'Сентября',
        'October'   => 'Октября',
        'November'   => 'Ноября',
        'December'   => 'Декабря'
    ];

    foreach ($months as $key => $value) {
        $date = str_replace($key, $value, $date);
    }

    return $date;
}