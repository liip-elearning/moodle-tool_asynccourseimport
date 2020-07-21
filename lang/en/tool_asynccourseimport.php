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
 * Strings for component 'tool_asynccourseimport', language 'en'
 *
 * @package   tool_asynccourseimport
 * @copyright 2019 Liip AG {@link http://liip.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Asynchronous course import';

$string['import_footer_message'] =
        'Your courses have been scheduled to be imported. You need to wait as the task is processed by a cron.';
$string['import_footer_count'] = '{$a} course(s) is/are scheduled to be imported';

$string['task_incomplete'] = 'Unable to complete the course import (Task id: {$a}). ';
$string['task_complete'] = 'The asynchronous courses import finished. ';

$string['report_header'] = 'A course import task you scheduled has finished.';
$string['report_errors_header'] = '<p>The following courses could not be imported:</p>';
$string['report_errors_line'] = '<li>Idnumber: {$a->idnumber} Shortname: {$a->shortname}. Reasons: {$a->reasons}</li>';
$string['report_summary'] = 'Summary';
$string['report_results'] = 'Results';

$string['event_importcourse_error'] = 'A course could not be imported during the Async course import process.';

$string['preview_create_all'] = 'No preview will be shown, the courses will be directly scheduled for import without any pre-check since the Upload mode is <b>"Create all, increment shortname if needed"</b>. Any error will be shown in the Notifications report.';