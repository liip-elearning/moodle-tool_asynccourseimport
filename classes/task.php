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
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

use core\task\adhoc_task;
use tool_uploadcourse_processor;
use tool_uploadcourse_tracker;

class task extends adhoc_task {

    /**
     * @param array $content CSV Content parsed.
     * @param array $options Form options
     * @param array $defaults Default course setting
     * @param int $linenb The CSV Line number
     * @param string $importid The importid used for the backup folder.
     * @return task
     */
    public static function create(array $content, array $options, array $defaults, $linenb, $importid) {
        $task = new self();
        $task->set_custom_data([
                "content" => $content,
                "options" => $options,
                "defaults" => $defaults,
                "linenb" => $linenb,
                "importid" => $importid
        ]);
        $task->set_blocking(true);

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
        $data = $this->get_custom_data();
        $content = (array) $data->content;
        $options = (array) $data->options;
        $defaults = (array) $data->defaults;
        $importid = $data->importid ?? csv_import_reader_for_task::get_new_iid('uploadcourse');

        echo "Importing course batch task " . $this->get_id() . "";
        // This is a fake CSV reader, it just use "content" as if it came from the csv.
        $cir = new csv_import_reader_for_task($importid, 'uploadcourse', $content);
        // We run the Moodle tool_uploadcourse_processor with a PLAIN output.
        $processor = new tool_uploadcourse_processor($cir, $options, $defaults);
        $processor->execute(new tool_uploadcourse_tracker(tool_uploadcourse_tracker::OUTPUT_PLAIN));

        $errors = $processor->get_errors();

        // Throws on error, so that the task is rescheduled.
        if (!empty($errors)) {
            print_r($errors);
            throw new \RuntimeException("Unable to complete the course import batch");
        }
    }
}
