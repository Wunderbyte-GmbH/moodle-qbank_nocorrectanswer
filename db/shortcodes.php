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
 * @copyright 2024 Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$shortcodes = [
    'correctanswers' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::correctanswers',
        'wraps' => false,
        'description' => 'correctanswers',
    ],
    'correctanswerschilds' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::correctanswerschilds',
        'wraps' => false,
        'description' => 'correctanswers',
    ],
    'resultoverview' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::resultoverview',
        'wraps' => false,
        'description' => 'resultoverview',
    ],
    'performanceoverview' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::performanceoverview',
        'wraps' => false,
        'description' => 'performanceoverview',
    ],
    'coursecorrectanswers' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::coursecorrectanswers',
        'wraps' => false,
        'description' => 'coursecorrectanswers',
    ],
    'courseresultoverview' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::courseresultoverview',
        'wraps' => false,
        'description' => 'courseresultoverview',
    ],
    'courseperformanceoverview' => [
        'callback' => 'qbank_nocorrectanswer\shortcodes::courseperformanceoverview',
        'wraps' => false,
        'description' => 'courseperformanceoverview',
    ],
];
