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

/**
 * Deals with local_shortcodes regarding booking.
 */
class qbank_nocorrectanswer {

    /**
     * Get all the questions.
     *
     * @param array $args
     * @return array
     */
    public static function get_all_questions($args) {
        global $DB;
        $sql = "SELECT DISTINCT ON (q.id) q.*
            FROM {question} q
            WHERE q.id IN (
                SELECT qa.questionid
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
                JOIN {question_usages} qu ON qu.id = qa.questionusageid
                JOIN {context} c ON c.id = qu.contextid
                JOIN {question_bank_entries} qbe ON qbe.id = qa.questionid";

        $params = [];
        $sqlwhere = '';
        if (isset($args['cmid'])) {
            $sqlwhere .= " AND c.instanceid = :cinstanceid";
            //$params['cinstanceid'] = $args['cmid'];
        }

        if (isset($args['qcatid'])) {
            $sqlwhere .= " AND qbe.questioncategoryid = :qcatid";
            $params['qcatid'] = $args['qcatid'];
            $paramscatid['qcatid'] = $args['qcatid'];
            $sqlqcatid = "SELECT qbe.*
              FROM {question_bank_entries} qbe
              WHERE qbe.questioncategoryid =:qcatid";
            return $DB->get_records_sql($sqlqcatid, $paramscatid);
        }
        if ($sqlwhere !== '') {
            //$sql .= " WHERE " . ltrim($sqlwhere, ' AND');
        }
        $sql .= ")ORDER BY q.id;";
        $records = $DB->get_records_sql($sql, $params);

        return $records;
    }

    /**
     * Get all edited questions.
     *
     * @param array $args
     * @return array
     */
    public static function get_all_edited_questions($args) {
        global $DB, $USER;
        $sql = "SELECT DISTINCT ON (q.id) q.*, qas.*
            FROM {question} q
            JOIN {question_attempts} qa ON q.id = qa.questionid
            JOIN {question_attempt_steps} qas ON qas.questionattemptid = qa.id
            WHERE q.id IN (
                SELECT qa.questionid
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
                JOIN {question_usages} qu ON qu.id = qa.questionusageid
                JOIN {context} c ON c.id = qu.contextid
                JOIN {question_bank_entries} qbe ON qbe.id = qa.questionid
                WHERE qas.userid=:nocorrectuseruserid AND qas.state ";
        $params = ['nocorrectuseruserid' => $USER->id];

        [$insql, $inparams] = $DB->get_in_or_equal(['gradedright', 'gradedwrong'], SQL_PARAMS_NAMED, 'param', true);
        $params = array_merge($params, $inparams);
        $sql .= $insql;

        if (isset($args['cmid'])) {
            $sql .= " AND c.instanceid = :cinstanceid";
            $params['cinstanceid'] = $args['cmid'];
        }

        if (isset($args['qcatid'])) {
            $sql .= " AND qbe.questioncategoryid = :qcatid";
            $params['qcatid'] = $args['qcatid'];
        }

        $sql .= ")ORDER BY q.id, qas.timecreated DESC;";
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
        $data = [];
        if ($args['quizid']) {
            global $USER, $DB;
            $params = [
                'userid' => $USER->id,
                'quizid' => $args['quizid'],
            ];

            $sql = "SELECT
                CAST(qa.sumgrades AS INT) AS usersumgrade,
                CAST(qg.grade AS INT) AS usergrade,
                CAST(q.sumgrades AS INT) AS sumgrades,
                CAST(q.grade AS INT) AS grade
              FROM {quiz_attempts} qa
              JOIN {quiz_grades} qg ON qg.quiz = qa.quiz
              JOIN {quiz} q ON q.id = qa.quiz
              WHERE qa.userid =:userid AND qa.quiz =:quizid
              ORDER BY qa.id DESC LIMIT 1;";
            $data = $DB->get_records_sql($sql, $params);
            if ($data) {
                $data = reset($data);
                $data->percentage = (int) (($data->usergrade / $data->grade) * 100);
            }
        }
        return $data;
    }

    /**
     * Get average quiz results.
     *
     * @param array $args
     * @return array
     */
    public static function get_average_quiz($args) {
        $data = [];
        if ($args['quizid']) {
            global $DB;
            $params = [
                'quizid' => $args['quizid'],
            ];

            $sql = "SELECT
                CAST(AVG(qg.grade) AS INT) AS average_score,
                COUNT(DISTINCT qa.userid) AS num_participants,
                CAST(MAX(qg.grade) AS INT) AS maximum_grade
                FROM {quiz_grades} qg
                JOIN {quiz_attempts} qa ON qg.quiz = qa.quiz
                WHERE qa.quiz =:quizid";

            $data = $DB->get_records_sql($sql, $params);
            if ($data) {
                $data = reset($data);
            }
        }
        return $data;
    }
}
