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
 * Session persistent model.
 *
 * @package   enrol_arlo {@link https://docs.moodle.org/dev/Frankenstyle}
 * @copyright 2022 Palmeira Group Ltd.{@link https://palmeiragroup.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_arlo\local\persistent;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core_date;
use core_text;
use context_system;
use DateTime;
use enrol_arlo\api;
use enrol_arlo\Arlo\AuthAPI\Enum\EventSessionStatus;
use enrol_arlo\event\session_created;
use enrol_arlo\event\session_updated;
use enrol_arlo\local\enum\arlo_type;
use enrol_arlo\persistent;

class event_session_persistent extends persistent {

    /** Table name. */
    const TABLE = 'enrol_arlo_event_session';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     * @throws coding_exception
     */
    protected static function define_properties() {
        $pluginconfig = api::get_enrolment_plugin()->get_plugin_config();
        return [
            'platform' => [
                'type' => PARAM_TEXT,
                'default' => $pluginconfig->get('platform')
            ],
            'sourceid' => [
                'type' => PARAM_INT,
                'default' => 0
            ],
            'startdatetime' => [
                'type' => PARAM_TEXT
            ],
            'finishdatetime' => [
                'type' => PARAM_TEXT
            ],
            'starttimezoneabbr' => [
                'type' => PARAM_TEXT
            ],
            'finishtimezoneabbr' => [
                'type' => PARAM_TEXT
            ],
            'sessiontype' => [
                'type' => PARAM_TEXT
            ],
            'name' => [
                'type' => PARAM_TEXT
            ],
            'description' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => ''
            ],
            'sourcestatus' => [
                'type' => PARAM_TEXT
            ],
            'sourcecreated' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null
            ],
            'sourcemodified' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null
            ],
            'sourceeventid' => [
                'type' => PARAM_INT,
                'default' => 0
            ],
            'sourceeventguid' => [
                'type' => PARAM_TEXT
            ]
        ];
    }

    /**
     * Method that works out timenorequestafter.
     *
     * @return int
     * @throws coding_exception
     */
    public function get_time_norequests_after() {
        $status = $this->get('sourcestatus');
        $nowepoch = time();
        $finishdatetime = $this->get('finishdatetime');
        if (!empty($status) && !empty($finishdatetime)) {
            if ($status == EventSessionStatus::ACTIVE) {
                $finishdate = new DateTime(
                    $finishdatetime,
                    core_date::get_user_timezone_object()
                );
                $finishepoch = $finishdate->getTimestamp();
                if ($finishepoch > $nowepoch) {
                    return $finishepoch;
                }
            }
        }
        return $nowepoch;
    }
    protected function set_name($value) {
        return $this->raw_set('name', $value);
    }

    /**
     * Fire event session created event.
     *
     * @throws \dml_exception
     * @throws coding_exception
     */
    protected function after_create() {
        $data = [
            'objectid' => 1,
            'context' => context_system::instance(),
            'other' => [
                'id' => $this->raw_get('id'),
                'sourceid' => $this->raw_get('sourceid'),
                'sourcestatus' => $this->raw_get('sourcestatus'),
                'sourceeventid' => $this->raw_get('sourceeventid'),
                'sourceeventguid' => $this->raw_get('sourceeventguid'),
                'startdatetime' => $this->raw_get('startdatetime'),
                'finishdatetime' => $this->raw_get('finishdatetime'),
                'platform' => $this->raw_get('platform'),
                'name' => $this->raw_get('name'),
                'description' => $this->raw_get('description')
            ]
        ];
        session_created::create($data)->trigger();
    }

    /**
     * Fire event session updated event.
     *
     * @param bool $result
     * @throws \dml_exception
     * @throws coding_exception
     */
    protected function after_update($result) {
        if ($result) {
            $data = [
                'objectid' => 1,
                'context' => context_system::instance(),
                'other' => [
                    'id' => $this->raw_get('id'),
                    'sourceid' => $this->raw_get('sourceid'),
                    'sourcestatus' => $this->raw_get('sourcestatus'),
                    'sourceeventid' => $this->raw_get('sourceeventid'),
                    'sourceeventguid' => $this->raw_get('sourceeventguid'),
                    'startdatetime' => $this->raw_get('startdatetime'),
                    'finishdatetime' => $this->raw_get('finishdatetime'),
                    'platform' => $this->raw_get('platform'),
                    'name' => $this->raw_get('name'),
                    'description' => $this->raw_get('description')
                ]
            ];
            session_updated::create($data)->trigger();
        }
    }
}
