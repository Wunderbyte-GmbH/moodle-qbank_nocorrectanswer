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
 * Shortcodes for qbank_nocorrectanswers
 *
 * @package qbank_nocorrectanswers
 * @subpackage db
 * @since Moodle 4.1
 * @copyright 2023 Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_nocorrectanswer;

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

        global $PAGE, $USER, $DB;

        $sql = "SELECT COUNT(qa.questionid)
                FROM {question_attempt_steps} qas
                JOIN {question_attempts} qa ON qa.id=qas.questionattemptid
                WHERE qas.state LIKE 'gradedright' AND qas.userid=:userid";
        $params = ['userid' => $USER->id];

        $correctlyanswered = $DB->count_records_sql($sql, $params);

        return "Correctly answered: " . $correctlyanswered ?: 0;
    }
}
