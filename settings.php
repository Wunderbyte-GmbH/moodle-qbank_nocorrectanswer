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
 * Plugin administration pages are defined here.
 *
 * @package     qbank_nocorrectanswer
 * @category    admin
 * @copyright   2024 Georg Maißer <info@wunderbyte.at>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('qbank_nocorrectanswer_settings', new lang_string('pluginname', 'qbank_nocorrectanswer'));

    $url = new moodle_url('/question/bank/nocorrectanswer/nocorrectanswer_percentage.php', ['section' => 'columnsortorder']);

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        $page = $adminroot->locate('manageqbanks');
        if (isset($page)) {
            $page->add(new admin_setting_description(
                'manageqbanksgotocolumnsort',
                '',
                new lang_string(
                    'qbankgotocolumnsort',
                    'qbank_columnsortorder',
                    html_writer::link($url, get_string('qbankcolumnsortorder', 'qbank_columnsortorder')))
            ));
        }
    }
    // Column sort order link in admin page.
    $settings = new admin_externalpage('qbank_nocorrectanswer_settings', get_string('qbankcolumnsortorder', 'qbank_columnsortorder'), $url);
}
