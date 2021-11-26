<?php

function side_menu($courseName, $countHW, $countHW24, $courseID, $currentCourseID, $currendGroupID, $currentChboxCourse, $currentChboxShowAllHW, $currentSearchQuery)
{
    global $CFG;

    if ($courseID == $currentCourseID) {
        $cqgString = 'courseid=' . $courseID . '&quizid=0' . '&groupid=' . $currendGroupID . ($currentChboxCourse == 1 ? '&chboxcourse=1' : '') .
            ($currentChboxShowAllHW == 1 ? '&chbox_show_all_hw=1' : '') . (empty($currentSearchQuery) ? '' : '&search_query=' . $currentSearchQuery);
        $tmpLabel = '<span class="name">' . $courseName . '</span>:  <span class="count">' . $countHW . ' <span class="count24">(' . $countHW24 . ')</span></span>';
        $html = '<li class="active"><a class="name" href="' . $CFG->wwwroot . '/blocks/hw_tests/view.php?' . $cqgString . '">' . $tmpLabel . '</a></li>';
    } else {
        $cqgString = 'courseid=' . $courseID . '&quizid=0' . '&groupid=0' . ($currentChboxCourse == 1 ? '&chboxcourse=1' : '') .
            ($currentChboxShowAllHW == 1 ? '&chbox_show_all_hw=1' : '') . (empty($currentSearchQuery) ? '' : '&search_query=' . $currentSearchQuery);
        $tmpLabel = '<span class="name">' . $courseName . '</span>:  <span class="count">' . $countHW . ' <span class="count24">(' . $countHW24 . ')</span></span>';
        $html = '<li><a class="name" href="' . $CFG->wwwroot . '/blocks/hw_tests/view.php?' . $cqgString . '">' . $tmpLabel . '</a></li>';
    }

    return $html;
}

function side_menu_quiz($quizName, $quizCountHW, $quizCountHW24, $quizID, $currentCourseID, $currentQuizID, $currendGroupID, $currentChboxCourse, $currentChboxShowAllHW, $currentSearchQuery)
{
    global $CFG;

    if ($currentCourseID !== 0) {
        $cqgString = 'courseid=' . $currentCourseID . '&quizid=' . $quizID . '&groupid=' . $currendGroupID . ($currentChboxCourse == 1 ? '&chboxcourse=1' : '') .
            ($currentChboxShowAllHW == 1 ? '&chbox_show_all_hw=1' : '') . (empty($currentSearchQuery) ? '' : '&search_query=' . $currentSearchQuery);
        if ($quizID == $currentQuizID) {
            $tmpLabel = '<span class="quiz-name">' . $quizName . '</span>:  <span class="quiz-count">' . $quizCountHW . ' <span class="quiz-count24">(' . $quizCountHW24 . ')</span></span>';
            $html = '<li class="quiz-active"><a class="quiz-name" href="' . $CFG->wwwroot . '/blocks/hw_tests/view.php?' . $cqgString . '">' . $tmpLabel . '</a></li>';
        } else {
            $tmpLabel = '<span class="quiz-name">' . $quizName . '</span>:  <span class="quiz-count">' . $quizCountHW . ' <span class="quiz-count24">(' . $quizCountHW24 . ')</span></span>';
            $html = '<li><a class="quiz-name" href="' . $CFG->wwwroot . '/blocks/hw_tests/view.php?' . $cqgString . '">' . $tmpLabel . '</a></li>';
        }
    }

    return $html;
}