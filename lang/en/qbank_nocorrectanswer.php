<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     qbank_nocorrectanswer
 * @category    string
 * @copyright   2024 Georg Maißer <info@wunderbyte.at>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'No correct answer for active user';
$string['shortcodes::correctanswers'] = "Display the correctly answered questions of the current user";
$string['repeat_wrong'] = "Repeat incorrectly answered questions and improve!";
$string['questions_statistic'] = "Question Statistics";
$string['repeat_wrong_btn'] = "Repeat incorrect";
$string['edited_questions'] = '{$a->edit} of {$a->absolute} edited';
$string['correct_questions'] = '{$a->correct} of {$a->edit} correct';
$string['wrong_questions'] = '{$a->wrong} of {$a->edit} incorrect';

// Shortcodes.
$string['correctanswers'] = "Display the correctly answered questions of the current user";
$string['resultoverview'] = "Display an overview of all results";
$string['performanceoverview'] = "Display an overview of users performance";

// Result overview.
$string['resultoverview_results'] = 'Results';
$string['resultoverview_last_result'] = 'Your last exercise';
$string['resultoverview_first_result'] = 'Original exercise';
$string['resultoverview_current_values'] = 'Your most recent values';
$string['resultoverview_points'] = 'Points:';
$string['resultoverview_from'] = 'of';
$string['resultoverview_percentage'] = 'Percentage: ';
$string['resultoverview_percentagerank'] = 'Percentage rank: ';
$string['resultoverview_test_value'] = 'Test value (standard value): ';
$string['resultoverview_average'] = 'Average score: ';
$string['resultoverview_max'] = 'Maximum score: ';
$string['resultoverview_compare'] = 'Comparison values';
$string['resultoverview_total'] = 'Total participants: ';
$string['resultoverview_average_total'] = 'Average score: ';
$string['resultoverview_max_total'] = 'Maximum score:';
$string['resultoverview_information'] = '* Values may change over time.';
$string['resultoverview_performance'] = 'Performance';

// Performance overview.
$string['performanceoverview_performance'] = 'Performance of the current exercise';
$string['performanceoverview_from'] = 'of';
$string['performanceoverview_points'] = 'points';
$string['performanceoverview_average'] = 'Average performance of the last 4 exercises';
$string['performanceoverview_current'] = 'Your current exercise compared to {$a} participants:';
$string['performanceoverview_average_total'] = 'Average value of the comparison group';
$string['performanceoverview_max_total'] = 'Maximum score of the comparison group';
$string['performanceoverview_testvalue'] = 'Test value';
$string['performanceoverview_percentage'] = 'Percentage rank';

$string['performanceoverview_average_total_i'] = 'Average total of comparison group';
$string['performanceoverview_max_total_i'] = 'This is the max points of the comparison group';
$string['performanceoverview_testvalue_i'] = 'This is the mean average value of the comparison group';
$string['performanceoverview_percentage_i'] = 'This is average percentage rank of the comparison group';

// Settings.
$string['numberofquestions'] = 'Number of Questions';
$string['pleaseenternumber'] = 'Please enter a number';
$string['correct'] = '{$a} correct';
$string['pleaseenterpositiveintegerbetween0and100'] = 'Please enter a positive integer between 0 and 100';
$string['percentagerankmapping'] = 'Percentagerank mapping';

$string['percentagerank'] = 'Percentagerank';
$string['meanvaluemapping'] = 'Meanvalue';
$string['meanvalue'] = 'Meanvalue';
$string['allquestionsanswered'] = 'All question answered. Feel free to restart.';

$string['questionsinfo_percentage'] = '<p>Your percentage score is based on 20 out of 24 tasks, as four tasks in the test are not counted.</p>';
$string['questionsinfo_test_value'] = '<p>Your test score is based on 20 out of 24 tasks, as four tasks in the test are not counted.</p>';
$string['questionsinfo_percentagerank'] = '<p>Your percentage rank is based on 20 out of 24 tasks, as four tasks in the test are not counted.</p>';
