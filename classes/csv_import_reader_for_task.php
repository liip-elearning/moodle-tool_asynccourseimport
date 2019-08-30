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

/**
 * Class csv_import_reader
 *
 * @package tool_asynccourseimport
 * This simulate a fake CSV reader as we are already using parsed content.
 */
class csv_import_reader_for_task extends \csv_import_reader {
    /**
     * @var array
     */
    private $data;

    /**
     * csv_import_reader_for_task constructor.
     *
     * @param $iid
     * @param $type
     * @param array $data
     */
    public function __construct($iid, $type, array $data) {
        parent::__construct($iid, $type);
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function get_columns() {
        if (empty($this->data)) {
            return false;
        }
        return array_keys((array) $this->data[0]);
    }

    /**
     * @inheritDoc
     */
    public function init() {
        reset($this->data);
    }

    /**
     * @inheritDoc
     */
    public function next() {
        $data = current($this->data);
        if ($data === false) {
            return false;
        }
        next($this->data);
        return array_values((array) $data);
    }

    /**
     * @inheritDoc
     */
    public function close() {
        $this->data = [];
    }

    /**
     * @inheritDoc
     */
    public function load_csv_content($content, $encoding, $delimiter_name, $column_validation = null, $enclosure = '"') {
        // We don't load the file as we use "data" instead.
    }

}
