<?php

class block_reports extends block_base
{
    public function init()
    {
        $this->title = get_string('blockTitle', 'block_reports');
    }

    public function get_content()
    {
        global $USER;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        require_login();

        if (has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))) {
            $url = new moodle_url('/blocks/reports/hw_grade/view.php', array('courseid' => 0, 'chboxcourse' => 1));
            $link = html_writer::link($url, 'Журнал ответов (тесты)');
            $this->content->footer = '<p>' . $link . '</p>';
        }
        if (has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))) {
            $url = new moodle_url('/blocks/reports/statistics_study_progress/view.php', array('courseid' => 3));
            $link = html_writer::link($url, 'Отчет по прогрессу обучения');
            $this->content->footer .= '<p>' . $link . '</p>';
        }
        if (has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))) {
            $url = new moodle_url('/blocks/reports/certificate_universal/view.php', array('courseid' => 3));
            $link = html_writer::link($url, 'Отчет по сертификатам');
            $this->content->footer .= '<p>' . $link . '</p>';
        }

        $horisontalLine = false;
        if (has_capability('block/reports:viewreport_hwgraded', context_user::instance($USER->id))){
            $this->content->footer .= '<hr>';
            $horisontalLine = true;
            $url = new moodle_url('/blocks/reports/graded_hw_report_view.php', array());
            $link = html_writer::link($url, 'Отчет по проверенным ДЗ');
            $this->content->footer .= '<p>' . $link . '</p>';
        }

//        if (has_capability('block/hw_tests:viewreport_forum', context_user::instance($USER->id))){
//            $url = new moodle_url('/blocks/hw_tests/assign_and_forum/view.php', array('courseid' => 0, 'chboxcourse' => 1));
//            $link = html_writer::link($url, 'Журнал ответов (форум)');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        $horisontalLine = false;
//        if (has_capability('block/hw_tests:viewreport_hwgraded', context_user::instance($USER->id))){
//            $this->content->footer .= '<hr>';
//            $horisontalLine = true;
//            $url = new moodle_url('/blocks/hw_tests/graded_hw_report_view.php', array());
//            $link = html_writer::link($url, 'Отчет по проверенным ДЗ');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        if (has_capability('block/hw_tests:viewreport_hwgraded', context_user::instance($USER->id))){
//            if ($horisontalLine == false) {
//                $this->content->footer .= '<hr>';
//                $horisontalLine = true;
//            }
//            $url = new moodle_url('/blocks/hw_tests/graded_hw_by_exercise/view.php', array('courseid' =>95));
//            $link = html_writer::link($url, 'Отчет по проверенным ДЗ в разрезе занятий');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        if (has_capability('block/hw_tests:viewreport_hwgraded', context_user::instance($USER->id))){
//            if ($horisontalLine == false) {
//                $this->content->footer .= '<hr>';
//                $horisontalLine = true;
//            }
//            $url = new moodle_url('/blocks/hw_tests/statistics_study_progress/view.php', array('courseid'=>106));
//            $link = html_writer::link($url, 'Отчет по прогрессу обучения');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        if (has_capability('block/hw_tests:viewreport_hwgraded', context_user::instance($USER->id))){
//            if ($horisontalLine == false) {
//                $this->content->footer .= '<hr>';
//                $horisontalLine = true;
//            }
//            $url = new moodle_url('/blocks/hw_tests/course_print/view.php', array('courseid' => 26));
//            $link = html_writer::link($url, 'Печатная форма курса');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        if (has_capability('block/hw_tests:viewreport_hwgraded', context_user::instance($USER->id))){
//            $this->content->footer .= '<hr>';
//            $horisontalLine = true;
//
//            $url = new moodle_url('/blocks/hw_tests/certificate/view.php', array('courseid' => 106, 'companyid' => 0));
//            $link = html_writer::link($url, 'Отчет по сертификатам');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        if (has_capability('block/hw_tests:viewreport_hwgraded', context_user::instance($USER->id))){
//            if ($horisontalLine == false) {
//                $this->content->footer .= '<hr>';
//                $horisontalLine = true;
//            }
//            $url = new moodle_url('/blocks/hw_tests/hw_results/view.php', array('courseid' => 95));
//            $link = html_writer::link($url, 'Отчет по итогам ДЗ');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }
//
//        if (has_capability('moodle/site:config', context_user::instance($USER->id))){
//            $this->content->footer .= '<hr>';
//            $url = new moodle_url('/blocks/hw_tests/admin_tools/view.php', array());
//            $link = html_writer::link($url, 'Admin Tools');
//            $this->content->footer .= '<p>' . $link . '</p>';
//        }

        return $this->content;
    }
}