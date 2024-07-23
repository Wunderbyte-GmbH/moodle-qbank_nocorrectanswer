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
 * Defines the import questions form.
 *
 * @package    qbank_importquestions
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_nocorrectanswer\form;

defined('MOODLE_INTERNAL') || die();

use moodle_exception;
use moodleform;
use stdClass;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to import questions into the question bank.
 *
 * @copyright  2024 Thomas Winkler
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calculationst_form extends moodleform {
    /**
     * $cmid of quiz
     * @int
     */
    public $cmid;

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the manage categories feature needs.
     * @throws \coding_exception
     */
    protected function definition() {
        global $CFG;
        $mform = $this->_form; // Don't forget the underscore!
        // Retrieve the configuration value (assumed to be stored in $CFG->numberofquestions)

        $plugin = 'qbank_nocorrectanwser';
        $setting = 'qbank_numberofquestions';
        $numberofquestions = get_config($plugin, $setting);

        if (!is_numeric($numberofquestions) || $numberofquestions < 0) {
            $numberofquestions = 10; // Default value if config value is invalid
        }

        // Generate text fields based on configuration value
        for ($i = 0; $i < $numberofquestions; $i++) {
            $mform->addElement('text', 'value' . $i, get_string('correct', 'qbank_nocorrectanswer', $i));
            $mform->setType('value' . $i, PARAM_INT); // Ensure the value is treated as an integer
            $mform->addRule('value' . $i, get_string('pleaseenternumber', 'qbank_nocorrectanswer'), 'numeric', null, 'client');
        }

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT); // Ensure the value is treated as an integer


        // Add submit and cancel buttons
        $this->add_action_buttons($cancel = true, $submitlabel = get_string('submit'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     * @throws \dml_exception|\coding_exception|moodle_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Add any custom validation rules here
        foreach ($data as $key => $value) {
            if (strpos($key, 'value') || intval($value) < 0 || intval($value) > 100) {
                $errors[$key] = get_string('pleaseenterpositiveintegerbetween0and100', 'qbank_nocorrctanswer');
            }
        }

        return $errors;
    }
}
