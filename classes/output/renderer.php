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
 * A custom renderer class that extends the plugin_renderer_base and is used by the booking module.
 *
 * @package qbank_nocorrectanswer
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author David Bogner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_nocorrectanswer\output;

use qbank_nocorrectanswer\output\overview;
use qbank_nocorrectanswer\output\resultoverview;
use qbank_nocorrectanswer\output\performanceoverview;
use plugin_renderer_base;

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the booking module.
 *
 * @package qbank_nocorrectanswer
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @author David Bogner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Function to print html qoverview
     * @param overview $data
     * @return string
     */
    public function render_overview(overview $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/overview', $data);
        return $o;
    }

    /**
     * Function to print html qoverview
     * @param resultoverview $data
     * @return string
     */
    public function render_resultoverview(resultoverview $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/resultoverview', $data);
        return $o;
    }

    /**
     * Function to print html qoverview
     * @param performanceoverview $data
     * @return string
     */
    public function render_performanceoverview(performanceoverview $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/performanceoverview', $data);
        return $o;
    }


    /**
     * Function to print html qoverview
     * @param performanceoverview $data
     * @return string
     */
    public function render_courseresultoverview(courseresultoverview $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/courseresultoverview', $data);
        return $o;
    }
    /**
     * Function to print html qoverview
     * @param performanceoverview $data
     * @return string
     */
    public function render_courseoverview(courseoverview $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/courseoverview', $data);
        return $o;
    }
    /**
     * Function to print html qoverview
     * @param performanceoverview $data
     * @return string
     */
    public function render_courseperformanceoverview(courseperformanceoverview $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/courseperformanceoverview', $data);
        return $o;
    }

    /**
     * Function to print html qoverview
     * @param performanceoverview $data
     * @return string
     */
    public function render_congratulations(congratulations $data) {
        $o = '';
        $data = $data->export_for_template($this);
        $o .= $this->render_from_template('qbank_nocorrectanswer/congratulations', $data);
        return $o;
    }
}
