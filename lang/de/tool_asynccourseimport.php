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
 * Strings for component 'tool_asynccourseimport', language 'de'
 *
 * @package   tool_asynccourseimport
 * @copyright 2019 Liip AG {@link http://liip.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Asynchroner Kursimport';

$string['import_footer_message'] =
        'Ihre Kurse wurden für den Import eingeplant. Sie müssen warten, da die Aufgabe von einem Cron bearbeitet wird.';
$string['import_footer_count'] = '{$a} Kurs(e) ist/sind für den Import vorgesehen';

$string['task_incomplete'] = 'Der Kursimport kann nicht abgeschlossen werden (Task id: {$a}). ';
$string['task_complete'] = 'Der Import der asynchronen Kurse ist abgeschlossen. ';

$string['report_header'] = 'Eine von Ihnen geplante Aufgabe zum Import von Kursen ist abgeschlossen.\n';
$string['report_errors_header'] = '<p>Die folgenden Kurse konnten nicht importiert werden:</p>';

$string['event_importcourse_error'] = 'Ein Kurs konnte während des Async-Kursimportprozesses nicht importiert werden.';

$string['preview_create_all'] = 'Es wird keine Vorschau angezeigt, die Kurse werden ohne Vorabprüfung direkt für den 
Import eingeplant, da der Upload-Modus <b>"Alle Kurse anlegen, Kurznamen bei Bedarf inkrementieren"</b> ist. 
Jeder Fehler wird im Benachrichtigungsbericht angezeigt.';