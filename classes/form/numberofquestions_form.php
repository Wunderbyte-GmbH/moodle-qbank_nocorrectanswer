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
 * Form to set numbere of questions for a calculation.
 *
 * @copyright  2024 Thomas Winkler
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class numberofquestions_form extends moodleform {

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the manage categories feature needs.
     * @throws \coding_exception
     */
    protected function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        // Choice of import format, with help icons.
        $mform->addElement('header', 'numberofquestionsheader', get_string('numberofquestions', 'qbank_nocorrectanswer'));

        $mform->addElement('text', 'numberofquestions', get_string('numberofquestions', 'qbank_nocorrectanswer'));
        $mform->addRule('numberofquestions', get_string('pleaseenternumber', 'qbank_nocorrectanswer'), 'numeric', null, 'client');
        $mform->setType('numberofquestions', PARAM_INT);

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

        // Ensure the number of questions is a positive integer
        // if (isset($data['numberofquestions']) && (!is_numeric($data['numberofquestions']) || intval($data['numberofquestions']) <= 0)) {
        //     $errors['numberofquestions'] = get_string('pleaseenterpositiveinteger', 'qbank_nocorrectanswere');
        // }

        return $errors;
    }
}
