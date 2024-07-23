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
 * Plugin internal classes, functions and constants are defined here.
 * This code puts the new fields at the end of the form. They can be
 * inserted elsewhere with code like this, which puts the field
 * before the description field.
 * $examplefield = $mform->createElement('text', 'examplefield', get_string('examplefieldlabel', 'qbank_nocorrectanswer'));
 * $mform->insertElementBefore($examplefield, 'introeditor');
 * @package     qbank_nocorrectanswer
 * @copyright   2021 Marcus Green
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 * https://docs.moodle.org/dev/Callbacks
 * This function name depends on which plugin is implementing it. So if you were
 * implementing mod_wordsquare
 * This function would be called wordsquare_coursemodule_standard_elements
 * (the mod is assumed for course activities)
 */
function qbank_nocorrectanswer_coursemodule_standard_elements($formwrapper, $mform) {
    // Call code to get examplefield from database
    // For example $existing = get_existing($coursemodule);
    // You have to write get_existing.
    $modulename = $formwrapper->get_current()->modulename;
    $cmid = $formwrapper->get_current()->coursemodule;
    if ($modulename == 'quiz') {
        $mform->addElement('header', 'percentagerank', get_string('percentagerank', 'qbank_nocorrectanswer'));
        $url = new moodle_url('/question/bank/nocorrectanswer/settingsform.php', ['cmid' => $cmid]);
        $mform->addElement('html', '<div class="col-md-9 my-4 mx-4">
        <a href=' . $url->out() . ' class="btn btn-primary">' . get_string('percentagerank', 'qbank_nocorrectanswer'). '</a>
        </div>
        ', get_string('examplefieldlabel', 'qbank_nocorrectanswer'));
        $mform->setType('examplefield', PARAM_RAW);
        // Populate with $mform->setdefault('examplefield', $existing['examplefield']);.
    }
}

/**
 * Process data from submitted form
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return void
 * See plugin_extend_coursemodule_edit_post_actions in
 * https://github.com/moodle/moodle/blob/master/course/modlib.php
 */
function qbank_nocorrectanswer_coursemodule_edit_post_actions($data, $course) {
    // Pull apart $data and insert/update the database table.
}

/**
 * Validate the data in the new field when the form is submitted
 *
 * @param moodleform_mod $fromform
 * @param array $fields
 * @return void
 */
function qbank_nocorrectanswer_coursemodule_validation($fromform, $fields) {
    if (get_class($fromform) == 'mod_quiz_mod_form') {
        \core\notification::add($fields['examplefield'], \core\notification::INFO);
    }
}

function qbank_nocorrectanswer_error_handler($errno, $errstr, $errfile, $errline) {
    if (strpos($errstr, 'specific error message') !== false) {
        echo "Oops! A specific error occurred. Please contact support.";
        debugging("Custom error: [$errno] $errstr in $errfile on line $errline", DEBUG_DEVELOPER);
        exit();
    }
    return false; // Continue with default error handling
}

function qbank_nocorrectanswer_exception_handler($exception) {
    global $OUTPUT, $PAGE, $CFG, $USER;
    $msg = $exception->getMessage();
    $errorcode = $exception->errorcode;
    switch ($errorcode) {
        case 'notenoughrandomquestions':
            $categoryid = $exception->a->category;
            $userid = $USER->id;
            $preferencekey = 'qbank_nocorret_'  . $USER->id . '_' . $exception->a->filtercondition['cmid'];
            $preferencevalue = time();

            set_user_preference($preferencekey, $preferencevalue, $userid);
            $url = new moodle_url($exception->link);
            redirect($url, get_string('allquestionsanswered', 'qbank_nocorrectanswers'), null, \core\output\notification::NOTIFY_SUCCESS);
            break;

        // Add more cases as needed
        default:
            default_exception_handler($exception);
            break;
    }
}

function qbank_nocorrectanswer_init() {
    set_error_handler('qbank_nocorrectanswer_error_handler');
    set_exception_handler('qbank_nocorrectanswer_exception_handler');
}

function qbank_nocorrectanswer_after_config() {
    qbank_nocorrectanswer_init();
}
