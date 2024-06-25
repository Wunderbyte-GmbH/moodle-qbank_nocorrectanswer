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
use core\chart_series;
use qbank_nocorrectanswer\output\overview;

/**
 * Deals with local_shortcodes regarding booking.
 */
class shortcodes {

    /**
     * This shortcode shows a list of booking options, which have a booking customfield...
     * ... with the shortname "recommendedin" and the value set to the shortname of the course...
     * ... in which they should appear.
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return string
     */
    public static function correctanswers($shortcode, $args, $content, $env, $next) {

        global $PAGE, $USER, $DB, $OUTPUT;

        $allquestions = self::get_all_questions($args);

        $editedquestions = self::get_all_edited_questions($args);

        // Get the renderer.
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new overview(
            [$editedquestions['correct'], $editedquestions['wrong']],
            ['Correct', 'Wrong'],
            count($allquestions),
            $editedquestions
        );
        return $output->render_overview($data);
    }

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
            $params['cinstanceid'] = $args['cmid'];
        }

        if (isset($args['qcatid'])) {
            $sqlwhere .= " AND qbe.questioncategoryid = :qcatid";
            $params['qcatid'] = $args['qcatid'];
        }

        if ($sqlwhere !== '') {
            $sql .= " WHERE " . ltrim($sqlwhere, ' AND');
        }
        $sql .= ")ORDER BY q.id;";
        $records = $DB->get_records_sql($sql, $params);

        return $records;
    }

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
}
