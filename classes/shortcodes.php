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
use qbank_nocorrectanswer\output\courseoverview;
use qbank_nocorrectanswer\output\courseperformanceoverview;
use qbank_nocorrectanswer\output\courseresultoverview;
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
            preg_match('/href=(https?:\/\/[^ ]+)/', $args['quizlink'], $matches);
            // The URL will be in $matches[1] if found
            if (isset($matches[1])) {
                $wrongquiz = $matches[1];
            }
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

        $averagequiz = qbank_nocorrectanswer::get_average_quiz_scores($args);

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
        $averagequiz = qbank_nocorrectanswer::get_average_quiz_scores($args);
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new performanceoverview(
            $lastquiz,
            $lastfivequiz,
            $averagequiz
        );
        return $output->render_performanceoverview($data);
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
    public static function coursecorrectanswers($shortcode, $args, $content, $env, $next) {
        global $PAGE;
        // Get the renderer.
        $data = qbank_nocorrectanswer::get_all_quizzes_from_course($args);

        $wrongquiz = null;
        if (isset($args['quizlink'])) {
            preg_match('/href=(https?:\/\/[^ ]+)/', $args['quizlink'], $matches);
            // The URL will be in $matches[1] if found
            if (isset($matches[1])) {
                $wrongquiz = $matches[1];
            }
        }

        $editedquestions['correct'] = round($data->quizstatistic->usergrade, 0, 2);
        $editedquestions['wrong'] = round($data->quizstatistic->grade - $data->quizstatistic->usergrade, 0, 2);
        $totalpoints = round($data->quizstatistic->totalpossiblepoints, 0, 2);

        $editedquestions['edit'] = $data->quizstatistic->grade;
        // Get the renderer.
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new courseoverview(
            [$editedquestions['correct'], $editedquestions['wrong']],
            ['Correct', 'Wrong'],
            $totalpoints,
            $editedquestions,
            $wrongquiz
        );
        return $output->render_courseoverview($data);
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
    public static function courseresultoverview($shortcode, $args, $content, $env, $next) {
        global $PAGE;
        // Get the renderer.

        $data = qbank_nocorrectanswer::get_all_quizzes_from_course($args);

        // $averagequiz = qbank_nocorrectanswer::get_average_cquiz($args);

        $wrongquiz = null;
        if (isset($args['quizlink'])) {
            preg_match('/href=(https?:\/\/[^ ]+)/', $args['quizlink'], $matches);
            // The URL will be in $matches[1] if found
            if (isset($matches[1])) {
                $wrongquiz = $matches[1];
            }
        }
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new courseresultoverview(
            $data->lastquiz,
            $data->lastquiz,
        );
        return $output->render_courseresultoverview($data);
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
    public static function courseperformanceoverview($shortcode, $args, $content, $env, $next) {
        global $PAGE;
        // Get the renderer.
        $quizzes = qbank_nocorrectanswer::get_all_quizzes_from_course($args);
        //$averagequiz = qbank_nocorrectanswer::get_average_cquiz($args);
        $averagequiz = qbank_nocorrectanswer::get_average_course_scores($args);


        $lastquiz = $quizzes->lastquiz;
        $fourquiz = $quizzes->fourquizzes;
        // $lastfivequiz = qbank_nocorrectanswer::get_last_five_cquiz($args);
        // $averagequiz = qbank_nocorrectanswer::get_average_cquiz($args);
        $output = $PAGE->get_renderer('qbank_nocorrectanswer');
        $data = new courseperformanceoverview(
            $lastquiz,
            $fourquiz,
            $averagequiz
        );
        return $output->render_courseperformanceoverview($data);
    }
}
