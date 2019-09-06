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

namespace tool_asynccourseimport;

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

use core\message\message;
use core\task\adhoc_task;
use core\task\manager;
use lang_string;
use tool_uploadcourse_tracker;

class task extends adhoc_task {

    /**
     * @param $content
     * @param array $options Form options
     * @param array $defaults Default course setting
     * @param $owner
     * @return task
     */
    public static function create($content, array $options, array $defaults, $owner) {
        $task = new self();
        $task->set_custom_data([
                "content" => $content,
                "options" => $options,
                "defaults" => $defaults,
                "fullreport" => [
                    "total" => count($content),
                    "created" => 0,
                    "updated" => 0,
                    "deleted" => 0,
                ]
        ]);
        $task->set_blocking(true);
        $task->set_userid($owner);

        return $task;
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     *
     * @throws \coding_exception
     * @throws \Exception
     */
    public function execute() {
        global $DB;
        $data = $this->get_custom_data();
        $content = (array) $data->content;
        $options = (array) $data->options;
        $defaults = (array) $data->defaults;
        $importid = $data->importid ?? csv_import_reader_for_task::get_new_iid('uploadcourse');

        echo "\nExecuting task [" . get_class($this) . " " . $this->get_id() . "]:\n\n";

        // This is a fake CSV reader, it just use "content" as if it came from the csv.
        $cir = new csv_import_reader_for_task($importid, 'uploadcourse', $content);

        $processor = new tool_asyncuploadcourse_processor($cir, $options, $defaults);
        $task_tracker = new tool_uploadcourse_tracker(tool_uploadcourse_tracker::OUTPUT_PLAIN);
        $processor->execute($task_tracker, $this->get_userid(), $this->get_id());

        $errors = $processor->get_errors();

        // TODO Limit to 3 attempts instead of < delay
        if (!empty($errors) and $this->get_fail_delay() < 240) { // 60 / 120 / 240

            // Update Content to not retry succeeded lines.
            $contentforretry = [];
            foreach ($errors as $lineNb => $lineErrors) {
                $contentforretry[] = $content[$lineNb - 1];
            }
            $data->content = $contentforretry;
            $data->linenb = count($errors);

            // Update full report
            $currentreport = $processor->get_report();
            $data->fullreport->created += $currentreport['created'];
            $data->fullreport->updated += $currentreport['updated'];
            $data->fullreport->deleted += $currentreport['deleted'];

            $this->set_custom_data($data);
            $record = manager::record_from_adhoc_task($this);
            $DB->update_record('task_adhoc', $record);

            // Throw an error, so that the task fails and is rescheduled.
            throw new \RuntimeException(get_string("task_incomplete", "tool_asynccourseimport"));
        }

        // No RuntimeException => OVER
        $this->send_report($data->fullreport, $errors);
    }

    /**
     * @param $report
     * @param array $errors
     * @throws \coding_exception
     */
    private function send_report($report, $errors = []) {
        // Notifying
        $msg = get_string('report_header', 'tool_asynccourseimport');

        if (count($errors) > 0) {
            $msg .= get_string('report_errors_header', 'tool_asynccourseimport');
            $msg .= "<ul>";
            foreach ($errors as $lineNb => $lineErrors) {

                $msg .= "<li>IdNumber: " . $lineErrors['data']['idnumber'] .
                    " Shortname: " . $lineErrors['data']['shortname'] . ". Reasons:";


                // Keys (except 'data') are string identifiers,
                // their values are lang_string objects.
                unset($lineErrors['data']);

                $msg .= "<ul>";

                /** @var lang_string $errorLangString */
                foreach ($lineErrors as $identifier => $errorLangString){
                    $msg .= "<li>" . $errorLangString->out() . "</li>";
                }
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
        }

        $msg .= "<br><strong>Summary:</strong><br>
            Courses total: ".$report->total."<br>
            Courses created: ".$report->created."<br>
            Courses updated: ".$report->updated."<br>
            Courses deleted: ".$report->deleted."<br>
            Courses errors: " . count($errors) . "<br>
        ";

        $message = new message();
        $message->component         = 'tool_asynccourseimport';
        $message->userfrom          = \core_user::get_noreply_user();
        $message->userto            = $this->get_userid();
        $message->notification      = 1; // This is only set to 0 for personal messages between users.
        $message->smallmessage      = '';
        $message->fullmessageformat = FORMAT_HTML;
        $message->name              = 'tasks_status';
        $message->subject           = 'The asynchronous courses import finished'; // TRANSLATE.
        $message->fullmessagehtml   = $msg;
        message_send($message);

        return;
    }
}