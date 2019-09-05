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

use html_writer;
use tool_uploadcourse_tracker;

/**
 * tracker. Just change the result message.
 *
 * @package tool_asynccourseimport
 */
class tool_preview_tracker extends tool_uploadcourse_tracker {
    /**
     * @inheritDoc
     */
    public function results($total, $created, $updated, $deleted, $errors) {
        echo "\n ----- tool_asyncuploadcourse_tracker ----- \n\n";

        if ($this->outputmode === self::NO_OUTPUT) {
            return;
        }

        $message1 = get_string("import_footer_message", "tool_asynccourseimport");
        $message2 = get_string("import_footer_count", "tool_asynccourseimport", $total);

        if ($this->outputmode === self::OUTPUT_PLAIN) {
            echo $message1 . "\n" . $message2;
            return;
        }

        echo html_writer::tag("p", $message1) . "<br>" . html_writer::tag("strong", $message2);
    }
}
