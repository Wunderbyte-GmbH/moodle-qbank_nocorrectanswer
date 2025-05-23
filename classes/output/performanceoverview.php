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
class performanceoverview implements renderable, templatable {

    /** @var array $strings Localised strigns */
    public $strings = [];

    /** @var string $lastpiechart the note as it is saved in db */
    public $lastpiechart = null;

    /** @var string $fivepiechart the note as it is saved in db */
    public $fivepiechart = null;

    /** @var array $lastquiz the note as it is saved in db */
    public $lastquiz = null;

    /** @var \stdClass $lastfivequiz the note as it is saved in db */
    public $lastfivequiz = null;

    /** @var array $averagequiz the note as it is saved in db */
    public $averagequiz = null;

    /**
     * Constructor
     *

     *
     */
    public function __construct($lastquiz, $lastfivequiz, $averagequiz) {
        global $OUTPUT, $CFG;

        $this->lastquiz = $lastquiz;
        $this->lastfivequiz = $lastfivequiz;
        $this->averagequiz = $averagequiz;
        $numparticipants = 0;

        if (isset($this->lastquiz->sumgrades)) {
            $ration = $this->lastquiz->grade / $this->lastquiz->sumgrades;
            $lastseries = [
              $this->lastquiz->usergrade,
              $this->lastquiz->grade - ($this->lastquiz->usergrade),
            ];
            $lastchart = new \core\chart_pie();
            $series = new chart_series('Results', $lastseries);
            $lastchart->add_series($series);
            $lastchart->set_labels(['Correct', 'Wrong']);
            $lastchart->set_doughnut(true);
            $CFG->chart_colorset = ['#32b400', '#dc3c28'];
            $this->lastpiechart = $OUTPUT->render($lastchart);
            if (!empty((array)$lastfivequiz)) {
              $CFG->chart_colorset = ['#32b400'];
              $chart = new \core\chart_bar();
              $chart->set_horizontal(true);
              // Set the labels for the X-axis (dates)
              $chart->set_labels($lastfivequiz->dates);

              // Create a dataset for the points
              $series = new \core\chart_series('Punkte', $lastfivequiz->points);
              // Add the dataset to the chart
              $chart->add_series($series);

              $this->fivepiechart = $OUTPUT->render($chart);

            }
            if (isset($averagequiz->num_participants)) {
                $numparticipants = $averagequiz->num_participants;
            }

        }
        $this->strings = [
          'performanceoverview_performance' => get_string('performanceoverview_performance', 'qbank_nocorrectanswer'),
          'performanceoverview_from' => get_string('performanceoverview_from', 'qbank_nocorrectanswer'),
          'performanceoverview_points' => get_string('performanceoverview_points', 'qbank_nocorrectanswer'),
          'performanceoverview_average' => get_string('performanceoverview_average', 'qbank_nocorrectanswer'),
          'performanceoverview_current' => get_string('performanceoverview_current', 'qbank_nocorrectanswer', $numparticipants),
          'performanceoverview_average_total' => get_string('performanceoverview_average_total', 'qbank_nocorrectanswer'),
          'performanceoverview_max_total' => get_string('performanceoverview_max_total', 'qbank_nocorrectanswer'),
          'performanceoverview_testvalue' => get_string('performanceoverview_testvalue', 'qbank_nocorrectanswer'),
          'performanceoverview_percentage' => get_string('performanceoverview_percentage', 'qbank_nocorrectanswer'),
          'performanceoverview_average_total_i' => get_string('performanceoverview_average_total_i', 'qbank_nocorrectanswer'),
          'performanceoverview_max_total_i' => get_string('performanceoverview_max_total_i', 'qbank_nocorrectanswer'),
          'performanceoverview_testvalue_i' => get_string('performanceoverview_testvalue_i', 'qbank_nocorrectanswer'),
          'performanceoverview_percentage_i' => get_string('performanceoverview_percentage_i', 'qbank_nocorrectanswer'),
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
                'lastquiz' => $this->lastquiz,
                'lastfivequiz' => $this->lastfivequiz,
                'averagequiz' => $this->averagequiz,
                'lastpiechart' => $this->lastpiechart,
                'fivepiechart' => $this->fivepiechart,
                'strings' => $this->strings,
        ];
    }
}
