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

defined('MOODLE_INTERNAL') || die();

use core\task\manager;
use csv_import_reader;
use tool_uploadcourse_processor;
use tool_uploadcourse_tracker;

/**
 * Class tool_task_processor
 * This processor will create an AdHock task to process course import asynchronously
 * instead of processing them.
 *
 * @package tool_asynccourseimport
 */
class tool_preview_processor extends tool_uploadcourse_processor {

    /**
     * @var array Import options
     */
    private $options;
    /**
     * @var array Buffer of CSV lines to be processed.
     */
    private $buffer;

    /**
     * @var string id of the import, used to get the backup folder.
     */
    private $importid;

    /**
     * @var int the ID of the user who ordered the import task
     */
    private $userid;

    /**
     * processor constructor.
     *
     * @param csv_import_reader $cir
     * @param array $options
     * @param array $defaults
     * @param string $importid The import id.
     * @throws \coding_exception
     */
    public function __construct(csv_import_reader $cir, array $options, array $defaults, $importid, $userid) {

        $this->options = $options;
        $this->defaults = $defaults;
        $this->importid = $importid;
        $this->userid = $userid;
        $this->buffer = [];
        parent::__construct($cir, $options, $defaults);
    }

    public function execute($tracker = null) {
        if (empty($tracker)) {
            $tracker = new tool_uploadcourse_tracker(tool_uploadcourse_tracker::NO_OUTPUT);
        }
        $tracker->start();

        $this->buffer = $this->get_content_from_cir($this->cir, $tracker);

        if (!empty($this->buffer)) {
            $task = task::create($this->buffer, $this->options, $this->defaults, $this->userid);
            manager::queue_adhoc_task($task);
            $this->buffer = [];
        }

        // Display the preview result (SCREEN 3).
        $tracker->finish();
        $tracker->results(count($this->buffer), count($this->buffer), 0, 0, null);
        $this->cir->close();
    }

    private function get_content_from_cir(csv_import_reader $cir, $tracker = null) {
        // Loop over the CSV lines.
        $buffer = [];
        $this->linenb = 0;
        while ($line = $cir->next()) {
            $line = $this->parse_line($line);
            $course = $this->get_course($line);
            // Note that you can also avoid the "prepare" call to be faster, but the output will have less sense.
            $result = $course->prepare();
            if (!$result && $tracker) {
                // Display errors.
                $tracker->output($this->linenb, $result, $course->get_errors(), $line);
            } else {
                // On success, we add the line to the buffer so it will be processed in a task.
                $this->linenb++;
                $buffer[] = $line;
            }
        }
        return $buffer;
    }
}
