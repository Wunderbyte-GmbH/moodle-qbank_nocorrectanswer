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


use qbank_nocorrectanswer\form\calculationst_form;

// @codingStandardsIgnoreStart
// @codingStandardsIgnoreEnd
require('../../../config.php');

require_once( $CFG->libdir.'/formslib.php' );
require_once( $CFG->libdir.'/datalib.php' );

$cmid = required_param('cmid', PARAM_INT);

$context = \context_system::instance();
$PAGE->set_context($context);
require_login();
$PAGE->set_url('/question/bank/nocorrectanswer/settingsform.php', ['cmid' => $cmid]);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('meanvaluemapping', 'qbank_nocorrectanswer'));
$PAGE->set_heading(get_string('meanvaluemapping', 'qbank_nocorrectanswer'));

$form = new calculationst_form(null, ['cmid' => $cmid]);

$plugin = 'qbank_nocorrectanswer';
$setting = 'qbank_questionmeanvalue_' . $cmid;
$numberofquestions  = get_config($plugin, 'qbank_numberofquestions');
if ($form->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present and pressed
} else if ($data = $form->get_data()) {

    $values = [];
    for ($i = 0; $i < $numberofquestions; $i++) {
        $fieldname = 'value' . $i;
        if ($data->$fieldname) {
            $values[$i] = $data->$fieldname;
        } else {
            $values[$i] = ''; // or some default value
        }
    }
    $jsonvalues = json_encode($values);
    set_config($setting, $jsonvalues, $plugin);
    $url = new moodle_url('/question/bank/nocorrectanswer/settingsformst.php', ['cmid' => $cmid]);
    redirect($url);

    // Provide feedback to the user.
} else {
    $toform = new stdClass();
    $values = get_config($plugin, $setting);
    if ($values) {
        $arrayvalues = json_decode($values);
        for ($i = 0; $i < $numberofquestions; $i++) {
            $fieldname = 'value' . $i;
            if ($arrayvalues[$i]) {
                $toform->$fieldname = $arrayvalues[$i];
            } else {
                $toform->$fieldname = ''; // or some default value
            }
        }
    }
    $toform->cmid = $cmid;
    // Set the existing data
    $form->set_data($toform);
    // Form didn't validate or this is the first display
    // Display the form
    echo $OUTPUT->header();
    $form->display();
    echo $OUTPUT->footer();
}
