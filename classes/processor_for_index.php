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

use core\task\manager;
use csv_import_reader;
use tool_uploadcourse_processor;
use tool_uploadcourse_tracker;

/**
 * Class processor_for_index
 * This processor will create an AdHock task to process course import asynchronously instead of processing them.
 *
 * @package tool_asynccourseimport
 */
class processor_for_index extends tool_uploadcourse_processor {
    /**
     * A task will be created to handle MAX_BATCH_SIZE courses.
     */
    const MAX_BATCH_SIZE = 10;

    /**
     * @var array Import options
     */
    private $options;
    /**
     * @var array Buffer of CSV lines to be processed.
     */
    private $buffer;
    /**
     * @var int The buffer size (to avoid a count)
     */
    private $buffersize;

    /**
     * @var int batch number.
     */
    private $batchid = 1;

    /**
     * @var string id of the import, used to get the backup folder.
     */
    private $importid;

    /**
     * processor constructor.
     *
     * @param csv_import_reader $cir
     * @param array $options
     * @param array $defaults
     * @param string $importid The import id.
     * @throws \coding_exception
     */
    public function __construct(csv_import_reader $cir, array $options, array $defaults, $importid) {

        $this->options = $options;
        $this->defaults = $defaults;
        $this->importid = $importid;
        $this->buffer = [];
        $this->buffersize = 0;
        $this->batchid = 1;
        parent::__construct($cir, $options, $defaults);
    }

    public function execute($tracker = null) {
        if (empty($tracker)) {
            $tracker = new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT);
        }
        $tracker->start();

        $total = 0;
        $this->buffer = [];
        $this->buffersize = 0;
        $this->linenb = 0;

        // Loop over the CSV lines.
        while ($line = $this->cir->next()) {
            if ($this->buffersize >= self::MAX_BATCH_SIZE) {
                $this->createTasksFromBuffer();
            }

            $line = $this->parse_line($line);
            $course = $this->get_course($line);
            // Note that you can also avoid the "prepare" call to be faster, but the output will have less sense.
            $result = $course->prepare();
            if (!$result) {
                // Display errors.
                $tracker->output($this->linenb, $result, $course->get_errors(), $line);
            } else {
                // On success, we add the line to the buffer so it will be processed in a task.
                $status = array_merge(["Scheduled in batch " . $this->batchid . "."], $course->get_statuses());

                $this->linenb++;
                $tracker->output($this->linenb, $result, $status, $line + ["id" => "-"]);
                $total++;
                $this->buffer[] = $line;
                $this->buffersize++;
            }

        }

        // Be sure that the remaining buffer is handled.
        if (!empty($this->buffer)) {
            $this->createTasksFromBuffer();
        }

        // Display the result.
        $tracker->finish();
        $tracker->results($total, $total, 0, 0, null);
        $this->cir->close();
    }

    /**
     * Creates an Ad-Hock task to process x courses, based on the parsed CSV lines that are on the buffer.
     */
    private function createTasksFromBuffer() {
        if (empty($this->buffer)) {
            return;
        }
        $task = task::create($this->buffer, $this->options, $this->defaults, $this->linenb, $this->importid);
        manager::queue_adhoc_task($task);
        $this->buffer = [];
        $this->buffersize = 0;
        $this->batchid++;
    }
}
