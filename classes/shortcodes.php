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
use qbank_nocorrectanswer\output\overview;
use qbank_nocorrectanswer\output\resultoverview;
use qbank_nocorrectanswer\output\performanceoverview;


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

        $allquestions = qbank_nocorrectanswer::get_all_questions($args);

        $editedquestions = qbank_nocorrectanswer::get_all_edited_questions($args);

        $wrongquiz = null;
        if (isset($args['quizlink'])) {
            $wrongquiz = $args['quizlink'];
        }

        // Get the renderer.
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new overview(
            [$editedquestions['correct'], $editedquestions['wrong']],
            ['Correct', 'Wrong'],
            count($allquestions),
            $editedquestions,
            $wrongquiz
        );
        return $output->render_overview($data);
    }

    /**
     * This shortcode shows a list of results of questions
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return string
     */
    public static function resultoverview($shortcode, $args, $content, $env, $next) {
        global $PAGE;
        // Get the renderer.

        $lastquiz = qbank_nocorrectanswer::get_last_quiz($args);

        $averagequiz = qbank_nocorrectanswer::get_average_quiz($args);

        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new resultoverview(
            $lastquiz,
            $averagequiz
        );
        return $output->render_resultoverview($data);
    }

    /**
     * This shortcode shows a list of results of questions
     *
     * @param string $shortcode
     * @param array $args
     * @param string|null $content
     * @param object $env
     * @param Closure $next
     * @return string
     */
    public static function performanceoverview($shortcode, $args, $content, $env, $next) {
        global $PAGE;
        // Get the renderer.
        $lastquiz = qbank_nocorrectanswer::get_last_quiz($args);
        $lastfivequiz = qbank_nocorrectanswer::get_last_five_quiz($args);
        $averagequiz = qbank_nocorrectanswer::get_average_quiz($args);
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new performanceoverview(
            $lastquiz,
            $lastfivequiz,
            $averagequiz
        );
        return $output->render_performanceoverview($data);
    }
}
