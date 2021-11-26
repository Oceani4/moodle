<?php
require_once('../../../config.php');
global $DB, $OUTPUT, $PAGE, $USER;

$courseid = optional_param('courseid', 0, PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_INT);
$quizid = optional_param('quizid', 0, PARAM_INT);
$chboxcourse = optional_param('chboxcourse', 0, PARAM_INT);
$chbox_show_all_hw = optional_param('chbox_show_all_hw', 0, PARAM_INT);
$search_query = optional_param('search_query', '', PARAM_TEXT);

require_login();

if (!has_capability('block/reports:viewreport_homework', context_user::instance($USER->id))){
    $url = new moodle_url('/');
    redirect($url);
}
$PAGE->set_context(context_system::instance()); // -- моя отсебятина
$PAGE->set_url(new moodle_url('/blocks/reports/view.php', array('courseid' => $courseid, 'quizid' => $quizid, 'groupid' => $groupid)));
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/blocks/reports/css/journal.css');

$previewnode = $PAGE->navigation->add('Журнал ответов', new moodle_url('/my/index.php'), navigation_node::TYPE_CONTAINER);
$thingnode = $previewnode->add('Непроверенные задания', new moodle_url('/blocks/reports/view.php', array('courseid' => $courseid)));
$thingnode->make_active();

////-------- create table ----------
//$sql = "CREATE TABLE `mdl_onreview` (
//                    `id` BIGINT(10) NOT NULL AUTO_INCREMENT,
//                    `course` BIGINT(10) NOT NULL DEFAULT 0,
//                    `module` BIGINT(10) NOT NULL DEFAULT 0,
//                    `instance` BIGINT(10) NOT NULL DEFAULT 0,
//                    `attempt` MEDIUMINT(9) NOT NULL DEFAULT 0,
//                    `user` BIGINT(10) NOT NULL DEFAULT 0,
//                    PRIMARY KEY (`id`),
//                    UNIQUE INDEX `CMIA` (`course`, `module`, `instance`, `attempt`)
//                )
//                COMMENT='ДЗ на проверке.\r\nModules:\r\n1 - Assign\r\n9 - Forum\r\n16 - Quiz'
//                COLLATE='utf8mb4_unicode_ci'
//                ENGINE=InnoDB
//                ROW_FORMAT=COMPRESSED
//                AUTO_INCREMENT=12";
//$DB->execute($sql);
//
//echo 'onreview - created!! 22'; exit;

echo $OUTPUT->header();
?>

<div id="root"></div>
<script>!function (f) {
        function e(e) {
            for (var r, t, n = e[0], o = e[1], u = e[2], l = 0, a = []; l < n.length; l++) t = n[l], Object.prototype.hasOwnProperty.call(p, t) && p[t] && a.push(p[t][0]), p[t] = 0;
            for (r in o) Object.prototype.hasOwnProperty.call(o, r) && (f[r] = o[r]);
            for (s && s(e); a.length;) a.shift()();
            return c.push.apply(c, u || []), i()
        }

        function i() {
            for (var e, r = 0; r < c.length; r++) {
                for (var t = c[r], n = !0, o = 1; o < t.length; o++) {
                    var u = t[o];
                    0 !== p[u] && (n = !1)
                }
                n && (c.splice(r--, 1), e = l(l.s = t[0]))
            }
            return e
        }

        var t = {}, p = {1: 0}, c = [];

        function l(e) {
            if (t[e]) return t[e].exports;
            var r = t[e] = {i: e, l: !1, exports: {}};
            return f[e].call(r.exports, r, r.exports, l), r.l = !0, r.exports
        }

        l.m = f, l.c = t, l.d = function (e, r, t) {
            l.o(e, r) || Object.defineProperty(e, r, {enumerable: !0, get: t})
        }, l.r = function (e) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {value: "Module"}), Object.defineProperty(e, "__esModule", {value: !0})
        }, l.t = function (r, e) {
            if (1 & e && (r = l(r)), 8 & e) return r;
            if (4 & e && "object" == typeof r && r && r.__esModule) return r;
            var t = Object.create(null);
            if (l.r(t), Object.defineProperty(t, "default", {
                enumerable: !0,
                value: r
            }), 2 & e && "string" != typeof r) for (var n in r) l.d(t, n, function (e) {
                return r[e]
            }.bind(null, n));
            return t
        }, l.n = function (e) {
            var r = e && e.__esModule ? function () {
                return e.default
            } : function () {
                return e
            };
            return l.d(r, "a", r), r
        }, l.o = function (e, r) {
            return Object.prototype.hasOwnProperty.call(e, r)
        }, l.p = "/";
        var r = this["webpackJsonpmoodle-hw-grade"] = this["webpackJsonpmoodle-hw-grade"] || [], n = r.push.bind(r);
        r.push = e, r = r.slice();
        for (var o = 0; o < r.length; o++) e(r[o]);
        var s = n;
        i()
    }([])</script>

<script src="http://moodle-test.bankdabrabyt.by/blocks/reports/hw_grade/static/js/2.2d87eb6e.chunk.js"></script>
<!--<script src="http://192.168.99.101:80/blocks/reports/hw_grade/static/js/2.2d87eb6e.chunk.js"></script>-->
<script src="http://moodle-test.bankdabrabyt.by/blocks/reports/hw_grade/static/js/main.fa5b9dc9.chunk.js"></script>
<!--<script src="http://192.168.99.101:80/blocks/reports/hw_grade/static/js/main.fa5b9dc9.chunk.js"></script>-->

<?php
echo $OUTPUT->footer();
?>