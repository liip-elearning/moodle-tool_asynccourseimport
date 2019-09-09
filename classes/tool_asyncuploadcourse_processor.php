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
 * File containing processor class.
 *
 * @package    tool_asynccourseimport
 * @copyright  2019 Liip
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_asynccourseimport;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core_php_time_limit;
use tool_uploadcourse_processor;
use tool_uploadcourse_tracker;

require_once($CFG->libdir . '/csvlib.class.php');

/**
 * Processor class.
 *
 * @package    tool_asynccourseimport
 * @copyright  2019 Liip
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_asyncuploadcourse_processor extends tool_uploadcourse_processor {

    protected $report = null;

    /**
     * Execute the process.
     *
     * @param object $tracker the output tracker to use.
     * @param null $userid
     * @param null $taskid
     * @return void
     * @throws \moodle_exception
     * @throws coding_exception
     */
    public function execute($tracker = null, $userid = null, $taskid = null) {
        if ($this->processstarted) {
            throw new coding_exception('Process has already been started');
        }

        if (empty($tracker)) {
            $tracker = new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT);
        }

        $this->processstarted = true;

        $tracker->start();

        $total = 0;
        $created = 0;
        $updated = 0;
        $deleted = 0;
        $errors = 0;

        // We will most certainly need extra time and memory to process big files.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        // Loop over the CSV lines.
        while ($line = $this->cir->next()) {
            $this->linenb++;
            $total++;

            $data = $this->parse_line($line);
            $course = $this->get_course($data);
            if ($course->prepare()) {
                $course->proceed();

                $status = $course->get_statuses();
                if (array_key_exists('coursecreated', $status)) {
                    $created++;
                } else if (array_key_exists('courseupdated', $status)) {
                    $updated++;
                } else if (array_key_exists('coursedeleted', $status)) {
                    $deleted++;
                }

                $data = array_merge($data, $course->get_data(), array('id' => $course->get_id()));
                $tracker->output($this->linenb, true, $status, $data);
            } else {
                $errors++;
                $tracker->output($this->linenb, false, $course->get_errors(), $data);

                // CUSTOM / EXTENSION.
                $this->log_error($course->get_errors(), $data, $userid, $taskid);
            }
        }

        // CUSTOM / EXTENSION.
        $this->prepare_report($total, $created, $updated, $deleted, $errors);

        $tracker->finish();
        $tracker->results($total, $created, $updated, $deleted, $errors);
    }

    /**
     * Log errors on the current line.
     *
     * @param array $errors array of errors
     * @param null $data
     * @param null $userid
     * @param null $taskid
     * @return void
     * @throws coding_exception
     */
    protected function log_error($errors, $data = null, $userid = null, $taskid = null) {
        global $PAGE;

        if (empty($errors)) {
            return;
        }

        // Set data for the report (sent by the caller task).
        /* @var $errors \lang_string[] */
        foreach ($errors as $code => $langstring) {
            if (!isset($this->errors[$this->linenb])) {
                $this->errors[$this->linenb] = array();
            }
            $this->errors[$this->linenb][$code] = $langstring;
            $this->errors[$this->linenb]['data'] = $data;
        }

        // Send an event to log the error on the server.
        $event = \tool_asynccourseimport\event\importcourse_error::create(array(
            'context' => $PAGE->context,
            'other' => [
                "shortname" => $data['shortname'],
                "reason" => $langstring->out(),
                "userid" => $userid,
                "task_id" => $taskid,
            ]
        ));
        $event->trigger();
    }

    protected function prepare_report($total, $created, $updated, $deleted, $errors) {
        $this->report = [
            'total' => $total,
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'errors' => $errors,
        ];
    }

    public function get_report() {
        return $this->report;
    }
}
