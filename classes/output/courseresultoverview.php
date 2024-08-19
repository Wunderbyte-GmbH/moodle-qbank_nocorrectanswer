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
 * This file contains the definition for the renderable classes for the booking instance
 *
 * @package   qbank_nocorrectanswer
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author Georg Maißer {@link http://www.wunderbyte.at}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_nocorrectanswer\output;

use renderer_base;
use renderable;
use templatable;

/**
 * This class prepares data for displaying a booking instance
 *
 * @package qbank_nocorrectanswer
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author Georg Maißer {@link http://www.wunderbyte.at}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class resultoverview implements renderable, templatable {

    /** @var array $strings Localised strigns */
    public $strings = [];

    /** @var array $lastquiz Localised strigns */
    public $lastquiz = [];

    /** @var array $averagequiz Localised strigns */
    public $averagequiz = [];


    /**
     * Constructor
     *

     *
     */
    public function __construct($lastquiz, $averagequiz) {
        $this->averagequiz = $averagequiz;
        $this->lastquiz = $lastquiz;
        $this->strings = [
          'resultoverview_results' => get_string('resultoverview_results', 'qbank_nocorrectanswer'),
          'resultoverview_last_result' => get_string('resultoverview_last_result', 'qbank_nocorrectanswer'),
          'resultoverview_first_result' => get_string('resultoverview_first_result', 'qbank_nocorrectanswer'),
          'resultoverview_current_values' => get_string('resultoverview_current_values', 'qbank_nocorrectanswer'),
          'resultoverview_points' => get_string('resultoverview_points', 'qbank_nocorrectanswer'),
          'resultoverview_from' => get_string('resultoverview_from', 'qbank_nocorrectanswer'),
          'resultoverview_percentage' => get_string('resultoverview_percentage', 'qbank_nocorrectanswer'),
          'resultoverview_percentagerank' => get_string('resultoverview_percentagerank', 'qbank_nocorrectanswer'),
          'resultoverview_test_value' => get_string('resultoverview_test_value', 'qbank_nocorrectanswer'),
          'resultoverview_average' => get_string('resultoverview_average', 'qbank_nocorrectanswer'),
          'resultoverview_max' => get_string('resultoverview_max', 'qbank_nocorrectanswer'),
          'resultoverview_compare' => get_string('resultoverview_compare', 'qbank_nocorrectanswer'),
          'resultoverview_total' => get_string('resultoverview_total', 'qbank_nocorrectanswer'),
          'resultoverview_average_total' => get_string('resultoverview_average_total', 'qbank_nocorrectanswer'),
          'resultoverview_max_total' => get_string('resultoverview_max_total', 'qbank_nocorrectanswer'),
          'resultoverview_information' => get_string('resultoverview_information', 'qbank_nocorrectanswer'),
          'resultoverview_performance' => get_string('resultoverview_performance', 'qbank_nocorrectanswer'),
        ];
    }

    /**
     * Export for template
     *
     * @param renderer_base $output
     *
     * @return array
     *
     */
    public function export_for_template(renderer_base $output) {
        return [
                'averagequiz' => $this->averagequiz,
                'lastquiz' => $this->lastquiz,
                'strings' => $this->strings,
        ];
    }
}
