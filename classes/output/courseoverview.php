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

use core\chart_series;
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
class courseoverview implements renderable, templatable {

    /** @var string $piechart the note as it is saved in db */
    public $piechart = null;

    /** @var string $absquestions Absolute number of questions */
    public $absquestions = null;

    /** @var array $editedquestions Absolute number of user played questions */
    public $editedquestions = null;

    /** @var array $strings Localised strigns */
    public $strings = [];

    /** @var string $wrongquiz Localised strigns */
    public $wrongquiz = null;

    /**
     * Constructor
     *
     * @param array $series
     * @param array $labels
     * @param int $absquestions
     * @param array $editedquestions
     *
     */
    public function __construct($series, $labels, $absquestions, $editedquestions, $wrongquiz) {
        global $OUTPUT, $PAGE, $CFG;

        if (
            $series[0] == 0 &&
            $series[1] == 0
        ) {
            $this->piechart = null;
        } else {
            $chart = new \core\chart_pie();
            $series = new chart_series('Results', $series);
            $chart->add_series($series);
            $chart->set_labels($labels);
            $chart->set_doughnut(true);
            $CFG->chart_colorset = ['#32b400', '#dc3c28'];
            $this->piechart = $OUTPUT->render($chart);
        }
        $this->absquestions = $absquestions;
        $this->editedquestions = $editedquestions;
        $this->strings = [
          'questions_statistic' => get_string('questions_statistic', 'qbank_nocorrectanswer'),
          'edited_questions' => get_string('edited_questions', 'qbank_nocorrectanswer',
            ['edit' => $this->editedquestions['edit'], 'absolute' => $this->absquestions]
            ),
          'correct_questions' => get_string('correct_questions', 'qbank_nocorrectanswer',
            ['correct' => $this->editedquestions['correct'], 'edit' => $this->editedquestions['edit']]
            ),
          'wrong_questions' => get_string('wrong_questions', 'qbank_nocorrectanswer',
            ['wrong' => $this->editedquestions['wrong'], 'edit' => $this->editedquestions['edit']]
            ),
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
                'piechart' => $this->piechart,
                'absquestions' => $this->absquestions,
                'editedquestions' => $this->editedquestions,
                'strings' => $this->strings,
                'wrongquiz' => $this->wrongquiz,
        ];
    }
}
