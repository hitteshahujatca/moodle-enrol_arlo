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
 * Class EventSession
 * @package enrol_arlo\Arlo\AuthAPI\Resource
 * @copyright 2022 Palmeira Group Ltd.{@link https://palmeiragroup.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_arlo\Arlo\AuthAPI\Resource;

defined('MOODLE_INTERNAL') || die();

class EventSession extends AbstractResource {
    public $SessionID;
    public $StartDateTime;
    public $FinishDateTime;
    public $StartTimeZoneAbbr;
    public $FinishTimeZoneAbbr;
    public $SessionType;
    public $Name;
    public $Status;
    public $Description;

    /**
     * @var Event associated resource.
     */
    protected $event;

    /**
     * @return Event
     */
    public function getEvent() {
        return $this->event;
    }
    /**
     * @param EventTemplate $eventTemplate
     */
    public function setEvent(Event $event) {
        $this->event = $event;
    }
}
