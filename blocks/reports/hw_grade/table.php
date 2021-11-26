<?php

function hw_form($item, $arrayForReturn)
{
    global $CFG;

    $param = [
        'quiz_attempt_id' => $item['quiz_attempt_id'],
        'courseid' => $arrayForReturn['courseid'],
        'groupid' => $arrayForReturn['groupid'],
        'quizid' => $arrayForReturn['quizid'],
        'chboxcourse' => $arrayForReturn['chboxcourse'],
        'chbox_show_all_hw' => $arrayForReturn['chbox_show_all_hw'],
        'search_query' => $arrayForReturn['search_query'],
        'o_courseid' => $item['course_id'],
        'o_quizid' => $item['quiz_id'],
        'o_attempt' => $item['attempt']
    ];

//    if ($item['grade'] != NULL) {
//        $url = '/blocks/reports/hw_grade/view_action_open.php';
//        $inputValue = 'Открыть';
//    }
//    else {
//        $url = '/blocks/reports/hw_grade/view_action_grade.php';
//        $inputValue = 'Проверить';
//    }

    $quizAttemptID = $item['quiz_attempt_id'];
    $courseid = $arrayForReturn['courseid'];
    $groupid = $arrayForReturn['groupid'];
    $quizid = $arrayForReturn['quizid'];
    $chboxcourse = $arrayForReturn['chboxcourse'];
    $chbox_show_all_hw = $arrayForReturn['chbox_show_all_hw'];
    $search_query = $arrayForReturn['search_query'];
//    $o_courseid = $item['course_id'];
//    $o_quizid = $item['quiz_id'];
//    $o_attempt = $item['attempt'];
    $url = $CFG->wwwroot . '/mod/quiz/review.php?attempt=' . $quizAttemptID . '&showall=0';
    $url .= (empty($courseid) ? '' : '&courseid=' . $courseid);
    $url .= (empty($groupid) ? '' : '&groupid=' . $groupid);
    $url .= (empty($quizid) ? '' : '&quizid=' . $quizid);
    $url .= (empty($chboxcourse) ? '' : '&chboxcourse=' . $chboxcourse);
    $url .= (empty($chbox_show_all_hw) ? '' : '&chbox_show_all_hw=' . $chbox_show_all_hw);
    $url .= (empty($search_query) ? '' : '&search_query=' . $search_query);

    $moodleUrl = new moodle_url($url, $param);
    $link = html_writer::link($moodleUrl, $item['quiz_name']);

    if (empty($item['grade'])) {
        $html = '<td class="quiz-name"> ' . $item['quiz_name'] . '</td>';
    }
    else {
        $html = '<td class="quiz-name"> ' . $link . '</td>';
    }

    $url = new moodle_url('/user/view.php', array('id' => $item['user_id'], 'course' => $item['course_id']));
    $link = html_writer::link($url, $item['user_name']);
    $html .= '<td class="user-name" data-groups="' . $item['subcourses'] .'" data-description="'. $item['posts'] .'">' . $link . '</td>';

    $html .= '<td class="user-name">' . $item['user_company'] . '</td>';
    $prevGrader = (empty($item['teacher1']) ? '' : ' (' . $item['teacher1'] . ')');
    $html .= '<td class="quiz-attempt">' . $item['attempt'] . $prevGrader . '</td>';

    if ($item['grade'] != NULL && $item['grade'] != 5) {
        $html .= ($item['grade'] > 51 ? '<td class="time-spent"><strong>' . intval($item['grade']) . ' баллов</strong></td>'
            : '<td class="time-spent"><strong style="color : red">' . intval($item['grade']).'</strong><strong>' . ' баллов</strong></td>' );
    }
    else {
        if ($item['time_spent'] > 24) {
            $html .= '<td class="time-spent"><time class="timeMore24">' . $item['time_spent'] . 'ч.</time></td>';
        } else {
            $html .= '<td class="time-spent"><time>' . $item['time_spent'] . 'ч.</time></td>';
        }
    }

    if (empty($item['grade'])) {
        $lastGrader = ' (' . $item['onreview'] . ')';
        $link1 = html_writer::link($moodleUrl,'<input type="submit" value=' . $inputValue . '>' );
        $link2 = html_writer::link($moodleUrl, 'на проверке <br>' . $lastGrader);
        $html .= (empty($item['onreview']) ? '<td class="quiz-link">'.$link1.'</td>'
            : '<td class="quiz-status-onreview">' .$link2. '</td>');
    }
    else {
        $link = html_writer::link($moodleUrl, (empty($item['teacher']) ? 'автотест' : $item['teacher']));
        $html .= '<td class="quiz-status-needgrade">' . $link . '</td>';
    }

    if (empty($item['disc_id']) && empty($item['post_id'])) {
        $html .= '<td class="quiz-status-needgrade">—</td>';
    }
    else {
        $link = '/mod/forum/discuss.php?d=' . $item['disc_id'] . '#p' . $item['post_id'];
        $html .= '<form target="_blank" method="post" action="' . $link .'">';
        $inputValue = 'Ответить';

        $html .= '<td class="forum-link"><input type="submit" value=' . $inputValue . '></td>';
        $html .= '</form>';
    }

    if (empty($item['plagiat_percent']) && $item['str_len'] > 500) {
        $url = '/blocks/hw_tests/view_action_plagiat_calculate.php';
        $inputValue = 'Сравнить';
        $moodleUrl = new moodle_url($url, $param);
        $link = html_writer::link($moodleUrl,'<input type="submit" value=' . $inputValue . '>' );
        $html .= '<td class="quiz-link">'.$link.'</td>';
    }
    else {
        $url = '/mod/quiz/review.php';
        $param = [
            'attempt' => $item['plagiat_quiz_attempt_id'],
        ];
        $inputValue = ($item['plagiat_percent'] > 50 ? '<span class="timeMore24">' . number_format($item['plagiat_percent'], 0, '.', '') . '%' . '</span>' :
            ($item['plagiat_percent'] < '1' ? '—' : number_format($item['plagiat_percent'], 0, '.', ''). '%') );
        $moodleUrl = new moodle_url($url, $param) . '#q'.$item['plagiat_slot'];
        $link = $item['plagiat_percent'] < '1' ? $inputValue : html_writer::link($moodleUrl, $inputValue  );
        $html .= '<td class="quiz-link course-name" data-coursename="' . $item['course_name'] .'">'.$link.'</td>';
    }

    if ($arrayForReturn['chbox_show_all_hw'] == 1) {
        $html .= '<td class="time-spent"><time>' . date('Y-m-d H:i:s', $item['time_graded']) . '</time></td>';
    }

    return $html;
}