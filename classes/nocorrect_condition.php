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

namespace qbank_nocorrectanswer;

use core\output\datafilter;
use core_question\local\bank\condition;

/**
 * Question bank search class to allow searching/filtering by tags on a question.
 *
 * @package   qbank_tagquestion
 * @copyright 2018 Ryan Wyllie <ryan@moodle.com>
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class nocorrect_condition extends condition {

    /** @var int The default filter type */
    const JOINTYPE_DEFAULT = datafilter::JOINTYPE_ALL;

    /** @var array Contexts to be used. */
    protected $contexts = [];

    /** @var array Contexts to be used. */
    protected static $options = [
        0 => [
            "name" => "never played",
            "sql" => "todo",
        ],
        1 => [
            "name" => "correct",
            "sql" => "gradedright",
        ],
        2 => [
            "name" => "not correct",
            "sql" => "gradedwrong",
        ],
    ];

    /** @var array List of IDs for tags that have been selected in the form. */
    protected $selectedtagids = [];

    /**
     * Noccorectanswers condition constructor. It uses the qbank object and initialises all the its required information
     * to be passed as a part of condition to get the questions.
     *
     * @param null $qbank qbank view
     */
    public function __construct($qbank = null) {
        if (is_null($qbank)) {
            return;
        }
        parent::__construct($qbank);
        $cat = $qbank->get_pagevars('cat');
        if (is_array($cat)) {
            foreach ($cat as $value) {
                [, $contextid] = explode(',', $value);
                $catcontext = \context::instance_by_id($contextid);
                $this->contexts[] = $catcontext;
            }
        } else {
            [, $contextid] = explode(',', $qbank->get_pagevars('cat'));
            $catcontext = \context::instance_by_id($contextid);
            $this->contexts[] = $catcontext;
        }
        $thiscontext = $qbank->get_most_specific_context();
        $this->contexts[] = $thiscontext;
    }

    public static function get_condition_key() {
        return 'qnocorrectanswerids';
    }

    /**
     * Print HTML to display the list of tags to filter by.
     */
    public function display_options() {
        global $PAGE;

        return 'display_options_functions_call';
    }

    /**
     * Build query from filter value
     *
     * @param array $filter filter properties
     * @return array where sql and params
     */
    public static function build_query_from_filter(array $filter): array {
        global $DB, $USER;

        $selectedoptions = self::get_query_value($filter['values']);
        $params = ['nocorrectuseruserid' => $USER->id];
        $where = '';

        if (!empty($selectedoptions)) {
            $jointype = $filter['jointype'] ?? self::JOINTYPE_DEFAULT;
            $equal = !($jointype === datafilter::JOINTYPE_NONE);
            [$insql, $inparams] = $DB->get_in_or_equal($selectedoptions, SQL_PARAMS_NAMED, 'param', $equal);
            $sql = "q.id NOT IN (
                SELECT qa.questionid
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id=qas.questionattemptid
                WHERE qas.nocorrectuseruserid= AND qas.state $insql
            )";

            $params = array_merge($params, $inparams);
        } else {
            // If there are no selected options, use the default query.
            $where = "q.id NOT IN (
                SELECT qa.questionid
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id=qas.questionattemptid
                WHERE qas.state = 'gradedright' AND qas.userid=:nocorrectuseruserid
            )";
        }
        return [$where, $params];
    }

    public static function get_query_value($filters) {
        $selectedoptions = [];
        foreach ($filters as $filter) {
            $testing = self::$options[$filter];
            $selectedoptions[] = self::$options[$filter]['sql'];
        }
        return $selectedoptions;
    }

    public function get_title() {
        return get_string('pluginname', 'qbank_nocorrectanswer');
    }

    public function get_filter_class() {
        return null;
    }

    public function allow_custom() {
        return false;
    }

    public function get_initial_values() {
        $values = [];
        foreach (self::$options as $key => $option) {
            $values[] = [
                'value' => $key,
                'title' => $option['name'],
                'selected' => false,
            ];
        }

        return $values;
    }
}
