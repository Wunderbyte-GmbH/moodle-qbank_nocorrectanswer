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
 * Plugin strings are defined here.
 *
 * @package     qbank_nocorrectanswer
 * @category    string
 * @copyright   2024 Georg Maißer <info@wunderbyte.at>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Keine richtige Antwort für aktiven Benutzer';
$string['shortcodes::correctanswers'] = "Zeige die richtig beantworteten Fragen des aktuellen Benutzers an";
$string['repeat_wrong'] = "Wiederhole falsch beantwortete Fragen und verbessere Dich!";
$string['repeat_wrong_btn'] = "Falsche wiederholen";
$string['edited_questions'] = '{$a->edit} von {$a->absolute} bearbeitet';
$string['correct_questions'] = '{$a->correct} von {$a->edit} korrekt';
$string['wrong_questions'] = '{$a->wrong} von {$a->edit} falsch';
$string['questions_statistic'] = 'Statistik';

// Shortcodes.
$string['correctanswers'] = "Zeige die richtig beantworteten Fragen des aktuellen Benutzers an";


// Result overview.
$string['resultoverview_results'] = 'Ergebnisse';
$string['resultoverview_last_result'] = "Deine letzte Übung";
$string['resultoverview_first_result'] = "Originale Übung";
$string['resultoverview_current_values'] = "Deine aktuelle Übung";
$string['resultoverview_points'] = 'Punkte:';
$string['resultoverview_from'] = 'von';
$string['resultoverview_percentage'] = 'Prozentwert: ';
$string['resultoverview_percentagerank'] = 'Prozentrangwert: ';
$string['resultoverview_test_value'] = 'Testwert (Standardwert): ';
$string['resultoverview_average'] = 'Durchschnittliche Punktzahl: ';
$string['resultoverview_max'] = 'Maximale Punktzahl: ';
$string['resultoverview_compare'] = 'Vergleichswerte';
$string['resultoverview_total'] = 'Gesamtzahl Teilnehmer: ';
$string['resultoverview_average_total'] = 'Durchschnittliche Punktzahl: ';
$string['resultoverview_max_total'] = 'Maximale Punktzahl:';
$string['resultoverview_information'] = '* Werte können sich im weiteren Verlauf ändern.';
$string['resultoverview_performance'] = 'Performance';

// Performance overview.
$string['performanceoverview_performance'] = 'Dein Ergebnis';
$string['performanceoverview_from'] = "von";
$string['performanceoverview_points'] = "Punkten";
$string['performanceoverview_average'] = "Ergebnisse der letzen Übungen";
$string['performanceoverview_current'] = 'Deine aktuelle Übung im Vergleich zu {$a} Teilnehmer:innen:';
$string['performanceoverview_average_total'] = 'Mittelwert';
$string['performanceoverview_max_total'] = 'Maximale Punktzahl';
$string['performanceoverview_testvalue'] = 'Testwert<br>(Standardwert)';
$string['performanceoverview_percentage'] = 'Prozentrang';

$string['performanceoverview_average_total_i'] = 'Dies ist der Mittelwert der Vergleichsgruppe.';
$string['performanceoverview_max_total_i'] = 'Dies ist die maximale Punktzahl der Vergleichsgruppe.';
$string['performanceoverview_testvalue_i'] = 'Dies ist der Mittelwert der Vergleichsgruppe der letzten Übung.';
$string['performanceoverview_percentage_i'] = 'Dies ist der Mittelwert der Vergleichsgruppe der letzten Übung.';

// Settings.
$string['numberofquestions'] = 'Anzahl der Fragen';
$string['pleaseenternumber'] = 'Bitte eine Zahl eingeben';
$string['correct'] = '{$a} richtige Antwort';
$string['pleaseenterpositiveintegerbetween0and100'] = 'Bitte eine Zahl zwischen 0 und 100 eingeben';
$string['percentagerankmapping'] = 'Prozentrang Zuweisung';
$string['percentagerank'] = 'Prozentrang';

$string['meanvaluemapping'] = 'Standardwert Zuweisung';
$string['meanvalue'] = 'Standardwert';

$string['allquestionsanswered'] = 'Sie haben alle Fragen dieser Kategorie beantwortet und können die Simulation gerne noch einmal starten.';

$string['questionsinfo_percentagerank'] = '<p>Der Prozentrangwert bezieht sich, wie im Originaltest, nur auf 20 der insgesamt 24 Aufgaben, da vier Aufgaben als Einstreuaufgaben nicht gewertet werden.
Der Prozentrangwert basiert auf den Daten der letzten Jahre und dient als Orientierungshilfe. Im Originaltest kann dieser Wert jedoch abweichen./p>';

$string['questionsinfo_percentage'] = '<p>Dein Prozentwert bezieht sich auf 20 von 24 Aufgaben, weil vier Aufgaben im Test nicht gewertet werden.</p>';
$string['questionsinfo_test_value'] = '<p>Dein Testwert bezieht sich auf 20 von 24 Aufgaben, weil vier Aufgaben im Test nicht gewertet werden.</p>';
$string['questionsinfo_percentagerank'] = '<p>Dein Prozentwertrang bezieht sich auf 20 von 24 Aufgaben, weil vier Aufgaben im Test nicht gewertet werden.</p>';