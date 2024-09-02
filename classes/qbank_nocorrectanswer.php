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
        $sql = "SELECT q.*
        FROM {question} q
        JOIN {question_versions} qv ON q.id = qv.questionid
        JOIN {question_bank_entries} qbe ON qv.questionbankentryid = qbe.id
        WHERE qbe.questioncategoryid = :qcatid
          AND qv.status = 'ready'
          AND qv.version = (
              SELECT MAX(qv2.version)
              FROM {question_versions} qv2
              WHERE qv2.questionbankentryid = qv.questionbankentryid
          )";
        $params['qcatid'] = $args['qcatid'];

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
            JOIN {question_bank_entries} qbe ON qv.questionbankentryid = qbe.id
            ";
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
                    qa.sumgrades / q.sumgrades * q.grade AS calculated_usergrade,  -- Calculate grade based on the specific attempt
                    qg.grade AS usergrade,  -- This assumes qg.grade is an overall grade, which might not match an individual attempt
                    q.sumgrades AS sumgrades,
                    q.grade AS grade,
                    qa.timemodified,
                    qa.userid,
                    qa.timefinish,
                    MAX(qa.sumgrades) OVER (PARTITION BY qa.userid, qa.quiz) AS max_user_sumgrades,
                    AVG(qa.sumgrades) OVER (PARTITION BY qa.userid, qa.quiz) AS avg_user_sumgrades,
                    MAX(qa.sumgrades) OVER (PARTITION BY qa.quiz) AS max_total_sumgrades,
                    AVG(qa.sumgrades) OVER (PARTITION BY qa.quiz) AS avg_total_sumgrades ";
            $sql = self::build_quiz_sql($select, 0);
            $data = $DB->get_records_sql($sql, $params);
            if ($data) {
                $data = reset($data);
                $data->usersumgrade = round($data->usersumgrade ?? 0, 2);
                $data->usergrade = round($data->calculated_usergrade ?? 0, 2);
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

    // public static function get_all_questions_of_cmid($args) {
    //     global $DB;
    //     $sql = "



    //     "
    //     $params = ['cmid' => $args['cmid']];

    // }

    /**
     * Get average quiz results.
     *
     * @param string $select
     * @param string $limit
     * @return string
     */
    public static function build_quiz_sql($select, $limit) {
        $from = "FROM {quiz_attempts} qa
              LEFT JOIN {quiz_grades} qg ON qg.quiz = qa.quiz
              JOIN {quiz} q ON q.id = qa.quiz
              JOIN {course_modules} cm ON cm.instance = q.id AND qg.userid = qa.userid
              WHERE qa.userid =:userid AND cm.id =:cmid
              ORDER BY qa.timefinish asc
             ";
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
     * Get average course uiz results.
     *
     * @param array $args
     * @return array
     */
    public static function get_average_cquiz($args) {
        $data = [];
        if (isset($args['courseid'])) {
            global $DB;
            $params = [
                'courseid' => $args['courseid'],
            ];
            $sql = "SELECT
                AVG(num_participants) AS avg_participants
            FROM (
                SELECT
                    COUNT(DISTINCT qa.userid) AS num_participants
                FROM {quiz_grades} qg
                JOIN {quiz_attempts} qa ON qg.quiz = qa.quiz
                JOIN {course_modules} cm ON cm.instance = qa.quiz
                JOIN {modules} m ON cm.module = m.id AND m.name = 'quiz'
                WHERE cm.course = :courseid
                GROUP BY qa.quiz
            ) AS participant_counts";

            $data = $DB->get_records_sql($sql, $params);
            if ($data) {
                $data = reset($data);
            }
        }
        return $data;
    }

    /**
     * Get average course quiz results.
     *
     * @param array $args - cmid
     * @return stdClass
     */
    public static function get_average_quiz_scores($args) {
        global $DB, $USER;
        $sql = "SELECT
    COUNT(DISTINCT qa.userid) AS num_participants,
    MAX(qg.grade) AS highest_grade,
    AVG(qg.grade) AS average_grade
FROM
    {quiz_grades} qg
JOIN
    {quiz_attempts} qa ON qg.quiz = qa.quiz
JOIN
    {course_modules} cm ON cm.instance = qa.quiz
JOIN
    (
        SELECT
            userid,
            quiz,
            MAX(attempt) AS latest_attempt
        FROM
            {quiz_attempts}
        GROUP BY
            userid, quiz
    ) latest_qa ON latest_qa.userid = qa.userid AND latest_qa.quiz = qa.quiz AND latest_qa.latest_attempt = qa.attempt
WHERE
    cm.id = :cmid
        ";
        $result = $DB->get_records_sql($sql, ['cmid' => $args['cmid'], 'excludeuserid' => $USER->id]);
        if ($result) {
            $res = reset($result);
            $data = new stdClass();
            $data->maxgrade = round($res->highest_grade ??  0, 2);
            $data->num_participants = $res->num_participants;
            $data->average_grade = round($res->average_grade ??  0, 2);
            $refcmid = isset($args['refcmid']) ? $args['refcmid'] : $args['cmid'];
            if ($config = get_config('qbank_nocorrectanswer', 'pc_' . $refcmid)) {
                $arrayvalues = json_decode($config);
                $data->avg_percentagevalue = $arrayvalues[(int) $res->average_grade];
            }
            if ($config = get_config('qbank_nocorrectanswer', 'mv_' . $refcmid)) {
                $arrayvalues = json_decode($config);
                $data->avg_testvalue = $arrayvalues[(int) $res->average_grade];
            }
        }
        return $data;
    }

    /**
     * Get average course quiz results.
     *
     * @param array $args
     * @return stdClass
     */
    public static function get_average_course_scores($args) {
        global $DB, $USER;
        $sql = "SELECT
        COUNT(DISTINCT latest.userid) AS num_participants,
            MAX(latest.grade) AS highest_grade,
            AVG(latest.grade) AS average_grade
        FROM
            {quiz_grades} latest
        JOIN
            (
                SELECT
                    userid,
                    MAX(timemodified) AS latest_timemodified
                FROM
                    {quiz_grades}
                GROUP BY
                    userid
            ) latest_times ON latest.userid = latest_times.userid
                        AND latest.timemodified = latest_times.latest_timemodified
        JOIN
            {quiz} qz ON latest.quiz = qz.id
        JOIN
            {course_modules} cm ON cm.instance = qz.id
        JOIN
            {modules} m ON cm.module = m.id AND m.name = 'quiz'
        WHERE
            cm.course = :courseid";
        $result = $DB->get_records_sql($sql, ['courseid' => $args['courseid']]);
        if ($result) {
            $res = reset($result);
            $data = new stdClass();
            $data->maxgrade = round($res->highest_grade ??  0, 2);
            $data->average_grade = round($res->average_grade ??  0, 2);
            $data->num_participants = $res->num_participants;
            if (isset($args['refcmid'])) {
                if ($config = get_config('qbank_nocorrectanswer', 'pc_' . $args['refcmid'])) {
                    $arrayvalues = json_decode($config);
                    $data->avg_percentagevalue = $arrayvalues[(int) $res->average_grade];
                }
                if ($config = get_config('qbank_nocorrectanswer', 'mv_' . $args['refcmid'])) {
                    $arrayvalues = json_decode($config);
                    $data->avg_testvalue = $arrayvalues[(int) $res->average_grade];
                }
            }
        }
        return $data;
    }

    /**
     * Get all edited, correct and wrong questions of user.
     *
     * @param array $args
     * @return stdClass()
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
                qg.id,
                qg.timemodified,
                qg.grade AS usergrade
             FROM
                {quiz_grades} qg
              JOIN {quiz} q ON q.id = qg.quiz
              JOIN {course_modules} cm ON cm.instance = q.id
              WHERE qg.userid = :userid AND cm.id = :cmid
              ORDER BY
               COALESCE(qg.timemodified, 0) DESC
              ";
            //$sql = self::build_quiz_sql($select, 5);
            $results = $DB->get_records_sql($select, $params);
            $count = 0;
            $lastfivequiz = new stdClass();
            $lastfivequiz->dates = [];
            $lastfivequiz->points = [];
            foreach ($results as $result) {
                if ($count < 5) {
                    $lastfivequiz->dates[] = date('d.m.y H:i', $result->timemodified);
                    $lastfivequiz->points[] = round($result->usergrade ?? 0, 0, 2);
                    $count ++;
                }
            }
        }
        return $lastfivequiz;
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
            // $avgofquizzes = self::get_average_scores($args);
            // Get max points from quizzes and divide trough points.
            $select = "SELECT
                q.id AS quizid,
                q.name AS quizname,
                q.grade AS maxpoints,
                COALESCE(qg.grade, 0) AS usergrade,
                c.id AS courseid,
                c.fullname AS coursename,
                COALESCE(qg.grade / q.grade, 0) AS gradefraction,
                qa.timefinish
            FROM
                {quiz} q
            JOIN
                {course} c ON c.id = q.course
            LEFT JOIN
                {quiz_attempts} qa ON qa.quiz = q.id AND qa.userid = :userid
            LEFT JOIN
                {quiz_grades} qg ON qg.quiz = q.id AND qg.userid = :useridq
            WHERE
                c.id = :courseid and qa.timefinish > 0
            ORDER BY
                COALESCE(qa.timefinish, 0) ASC,
                q.id

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
            $fourquizzes->dates = [];
            $fourquizzes->points = [];

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
                if ($count < 5) {
                    $fourquizzes->dates[] = date('d.m.y', $result->timefinish);
                    $fourquizzes->points[] = round($result->usergrade ?? 0, 0 ,2);
                    $count ++;
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
