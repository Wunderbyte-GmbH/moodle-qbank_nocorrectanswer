<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Shortcodes for qbank_nocorrectanswer
 *
 * @package qbank_nocorrectanswer
 * @subpackage db
 * @since Moodle 4.1
 * @copyright 2023 Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_nocorrectanswer;
use stdClass;

/**
 * Deals with local_shortcodes regarding booking.
 */
class qbank_nocorrectanswer {

    /** @var string sql to be used. */
    protected static $sql = "SELECT q.*
          FROM {question} q
          JOIN {question_versions} qv ON q.id = qv.questionid
          JOIN {question_bank_entries} qbe ON qv.questionbankentryid = qbe.id";

    /**
     * Get all the questions.
     *
     * @param array $args
     * @return array
     */
    public static function get_all_questions($args) {
        global $DB;
        $params = [];
        $select = "SELECT q.* ";
        $where = '';

        if (isset($args['qcatid'])) {
             $where = " WHERE qbe.questioncategoryid = :qcatid";
             $params['qcatid'] = $args['qcatid'];
        }
        $sql = self::build_question_sql($select, $where);

        $records = $DB->get_records_sql($sql, $params);
        return $records;
    }

    /**
     * Get average quiz results.
     *
     * @param string $select
     * @param string $join
     * @return string
     */
    public static function build_question_sql($select, $join) {
        $from = "FROM {question} q
            JOIN {question_versions} qv ON q.id = qv.questionid
            JOIN {question_bank_entries} qbe ON qv.questionbankentryid = qbe.id";
        $sql = $select . $from . $join;
        return $sql;
    }

    /**
     * Get all edited questions.
     *
     * @param array $args
     * @return array
     */
    public static function get_all_edited_questions($args) {
        global $DB, $USER;
        $sql = self::$sql;
        $subwhere = " WHERE qas.userid=:nocorrectuseruserid AND qas.state ";
        $params = ['nocorrectuseruserid' => $USER->id];

        [$insql, $inparams] = $DB->get_in_or_equal(['gradedright', 'gradedwrong'], SQL_PARAMS_NAMED, 'param', true);
        $params = array_merge($params, $inparams);
        $subwhere .= $insql;

        $args = null;
        if (isset($args['cmid'])) {
            $subwhere .= " AND c.instanceid = :cinstanceid";
            $params['cinstanceid'] = $args['cmid'];
        }

        if (isset($args['qcatid'])) {
            $subwhere .= " AND qbe.questioncategoryid = :qcatid";
            $params['qcatid'] = $args['qcatid'];
        }
        $select = "SELECT q.*, subquery.state ";
        $join = " JOIN (
              SELECT qa.questionid, qas.state
              FROM {question_attempt_steps} qas
              JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
              JOIN {question_usages} qu ON qu.id = qa.questionusageid
              JOIN {context} c ON c.id = qu.contextid
              JOIN {question_bank_entries} qbe ON qa.questionid = qbe.id
              " . $subwhere . ") subquery ON q.id = subquery.questionid";
        $sql = self::build_question_sql($select, $join);
        $records = $DB->get_records_sql($sql, $params);
        $userquestions = self::get_user_questiond_data($records);

        return $userquestions;
    }
    /**
     * Get all edited, correct and wrong questions of user.
     *
     * @param array $records
     * @return array
     */
    public static function get_user_questiond_data($records) {
        $data = [
            'edit' => count($records),
            'correct' => 0,
            'wrong' => 0,
        ];
        foreach ($records as $record) {
            if ($record->state == 'gradedright') {
                $data['correct']++;
            } else {
                $data['wrong']++;
            }
        }
        return $data;
    }

    /**
     * Get all edited, correct and wrong questions of user.
     *
     * @param array $args
     * @return array
     */
    public static function get_last_quiz($args) {
        $data = new stdClass();
        if (isset($args['cmid'])) {
            global $USER, $DB;
            $params = [
                'userid' => $USER->id,
                'cmid' => $args['cmid'],
            ];

            $select = "SELECT
                q.name,
                qa.sumgrades AS usersumgrade,
                qg.grade AS usergrade,
                q.sumgrades AS sumgrades,
                q.grade AS grade,
                MAX(qa.sumgrades ) OVER (PARTITION BY qa.userid, qa.quiz) AS max_user_sumgrades,
                AVG(qa.sumgrades) OVER (PARTITION BY qa.userid, qa.quiz) AS avg_user_sumgrades,
                MAX(qa.sumgrades) OVER (PARTITION BY qa.quiz) AS max_total_sumgrades,
                AVG(qa.sumgrades) OVER (PARTITION BY qa.quiz) AS avg_total_sumgrades ";
            $sql = self::build_quiz_sql($select, 1);
            $data = $DB->get_records_sql($sql, $params);
            if ($data) {
                $data = reset($data);
                $data->usersumgrade = round($data->usersumgrade ?? 0, 2);
                $data->usergrade = round($data->usergrade ?? 0, 2);
                $data->sumgrades = round($data->sumgrades ?? 0, 2);
                $data->grade = round($data->grade ?? 0, 2);
                $data->max_user_sumgrades = round($data->max_user_sumgrades ?? 0, 2);
                $data->avg_user_sumgrades = round($data->avg_user_sumgrades ?? 0, 2);
                $data->max_total_sumgrades = round($data->max_total_sumgrades ?? 0, 2);
                $data->avg_total_sumgrades = round($data->avg_total_sumgrades ?? 0, 2);
                $data->percentage = round(100 * $data->usergrade / $data->grade, 0);
                if ($config = get_config('qbank_nocorrectanswer', 'pc_' . $args['cmid'])) {
                    $arrayvalues = json_decode($config);
                    $data->percentagerank = $arrayvalues[(int)$data->usergrade];
                    $data->avg_percentagevalue = $arrayvalues[(int) $data->avg_user_sumgrades];
                }
                if ($config = get_config('qbank_nocorrectanswer', 'mv_' . $args['cmid'])) {
                    $arrayvalues = json_decode($config);
                    $data->meanvalue = $arrayvalues[(int)$data->usergrade];
                    $data->avg_testvalue = $arrayvalues[(int) $data->avg_user_sumgrades];

                }
                if (isset($args['showinfo'])) {
                    $data->showinfo = true;
                }
            }
        }

        return $data;
    }

    /**
     * Get average quiz results.
     *
     * @param string $select
     * @param string $limit
     * @return string
     */
    public static function build_quiz_sql($select, $limit) {
        $from = "FROM {quiz_attempts} qa
              JOIN {quiz_grades} qg ON qg.quiz = qa.quiz
              JOIN {quiz} q ON q.id = qa.quiz
              JOIN {course_modules} cm ON cm.instance = q.id
              WHERE qa.userid =:userid AND cm.id =:cmid
              ORDER BY qa.id DESC ";
        $sql = $select . $from;
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }
        return $sql;
    }

    /**
     * Get average quiz results.
     *
     * @param array $args
     * @return array
     */
    public static function get_average_quiz($args) {
        $data = [];
        if (isset($args['cmid'])) {
            global $DB;
            $params = [
                'cmid' => $args['cmid'],
            ];
            $sql = "SELECT
                COUNT(DISTINCT qa.userid) AS num_participants
                FROM {quiz_grades} qg
                JOIN {quiz_attempts} qa ON qg.quiz = qa.quiz
                JOIN {course_modules} cm ON cm.instance = qa.quiz
                WHERE cm.id =:cmid";

            $data = $DB->get_records_sql($sql, $params);
            if ($data) {
                $data = reset($data);
            }
        }
        return $data;
    }

    /**
     * Get all edited, correct and wrong questions of user.
     *
     * @param array $args
     * @return float
     */
    public static function get_last_five_quiz($args) {
        $average = 0;
        if (isset($args['cmid'])) {
            global $USER, $DB;
            $params = [
                'userid' => $USER->id,
                'cmid' => $args['cmid'],
            ];
            $select = "SELECT
                qa.id,
                qa.sumgrades AS usersumgrade,
                qg.grade AS usergrade,
                q.sumgrades  AS sumgrades,
                q.grade AS grade ";
            $sql = self::build_quiz_sql($select, 4);
            $results = $DB->get_records_sql($sql, $params);
            $sumgrade = 0;
            foreach ($results as $result) {
                $average += $result->usersumgrade;
                $sumgrade = $result->sumgrades;
            }
            if ($results) {
                $average = $average / count($results);
            }
        }
        return (int) $average;
    }

    /**
     * Summary of get_all_quizzes_from_course
     * @param mixed $args
     * @return stdClass
     */
    public static function get_all_quizzes_from_course($args) {
        if (isset($args['courseid'])) {
            global $USER, $DB;
            $params = [
                'useridq' => $USER->id,
                'userid' => $USER->id,
                'courseid' => $args['courseid'],
            ];
            $avgofquizzes = self::get_average_scores($args);
            // Get max points from quizzes and divide trough points.
            $select = "    SELECT
                q.id AS quizid,
                q.name AS quizname,
                q.grade AS maxpoints,
                gg.finalgrade AS usergrade,
                c.id AS courseid,
                c.fullname AS coursename,
                gi.id AS gradeitemid,
                gg.finalgrade / q.grade AS gradefraction,
                qa.timefinish
            FROM
                {quiz} q
            JOIN
                {course} c ON c.id = q.course
            LEFT JOIN
                {quiz_attempts} qa ON qa.quiz = q.id AND qa.userid = :useridq
            LEFT JOIN
                {grade_items} gi ON gi.iteminstance = q.id AND gi.itemmodule = 'quiz'
            LEFT JOIN
                {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = :userid
            WHERE
                c.id = :courseid
                AND gg.finalgrade > 0
            ORDER BY
                COALESCE(qa.timefinish, 0) asc,
                gi.id
            ";
            $results = $DB->get_records_sql($select, $params);
            $data = new stdClass();
            $data->quizstatistic = new stdClass();
            $data->quizstatistic->totalpossiblepoints = self::get_maxscores_from_quizzes($args);
            $sumgrade = 0;
            $lastattempt = 0;
            $lastquiz = new stdClass();
            $fourquizzes = new stdClass();
            $count = 0;
            if ($results) {
                $numberofquizzestaken = count($results);

            }

            foreach ($results as $result) {
                // firstquiz
                if (!$lastattempt && $result->timefinish) {
                    $lastquiz->maxpoints = $result->maxpoints;
                    $lastquiz->grade = round( $result->maxpoints ?? 0, 2);
                    $lastquiz->usergrade = round( $result->usergrade ?? 0, 2);
                    $lastquiz->percentage = round( $result->usergrade / $result->maxpoints * 100 ?? 0, 2);

                    if (isset($args['refcmid'])) {
                        if ($config = get_config('qbank_nocorrectanswer', 'pc_' . $args['refcmid'])) {
                            $arrayvalues = json_decode($config);
                            $lastquiz->percentagerank = $arrayvalues[(int)$lastquiz->usergrade];
                        }
                        if ($config = get_config('qbank_nocorrectanswer', 'mv_' . $args['refcmid'])) {
                            $arrayvalues = json_decode($config);
                            $lastquiz->meanvalue = $arrayvalues[(int)$lastquiz->usergrade];
                        }
                    }
                }
                if ($numberofquizzestaken >= 4) {
                    if ($count < 4) {
                        $fourquizzes->avg += $result->usergrade;
                        $fourquizzes->maxpoints += $result->maxpoints;
                        $count ++;
                    }
                    if ($count == 4) {
                        $fourquizzes->avg = round(($fourquizzes->avg / 4) ?? 0, 2);
                        $fourquizzes->maxpoints =round(($fourquizzes->maxpoints / 4) ?? 0, 2);
                    }
                }
                $average += $result->usergrade;
                $sumgrade = $result->sumgrades;
                $maxpoints += $result->maxpoints;
                $userpoints += $result->usergrade;
                if ($result->usergrade) {
                    $data->quizstatistic->grade += $result->maxpoints;
                    $data->quizstatistic->usergrade  += $result->usergrade;
                }
            }
            if ($results) {
                $average = $average * $sumgrade / count($results);
            }

            // Coursestatistic.
            $data->average = $average;
            $data->sumgrades = $sumgrade;
            $data->maxpoints = $maxpoints;
            $data->userpoints = $userpoints;

            // Last Quiz statistic.
            $data->lastquiz = $lastquiz;

            $data->fourquizzes = $fourquizzes;
            // Last 4 Quizstatistic
            $data->quizzes = $results;

        }
        $quizzes = $results;
        return $data;
    }

    public static function get_average_scores($args) {
        global $DB;
        $params = [
            'courseid' => $args['courseid'],
        ];
        $select = "
        SELECT
            AVG(gg.finalgrade / q.grade) AS overallaveragegradefraction
        FROM
            {quiz} q
        JOIN
            {course} c ON c.id = q.course
        JOIN
            {quiz_attempts} qa ON qa.quiz = q.id
        JOIN
            {grade_items} gi ON gi.iteminstance = q.id AND gi.itemmodule = 'quiz'
        JOIN
            {grade_grades} gg ON gg.itemid = gi.id
        WHERE
            c.id = :courseid
            AND qa.timefinish IS NOT NULL
        GROUP BY
            q.id
        HAVING
            COUNT(qa.userid) > 0";
        $results = $DB->get_records_sql($select, $params);
        return $results;
    }

    public static function get_maxscores_from_quizzes($args) {
        global $DB;
        $params = [
            'courseid' => $args['courseid'],
        ];
        $select = "
        SELECT
            SUM(q.grade) AS totalpossiblepoints
        FROM
            {quiz} q
        JOIN
            {course} c ON c.id = q.course
        WHERE
            c.id = :courseid
        ";
        $result = $DB->get_record_sql($select, $params);
        return $result->totalpossiblepoints;
    }
}
