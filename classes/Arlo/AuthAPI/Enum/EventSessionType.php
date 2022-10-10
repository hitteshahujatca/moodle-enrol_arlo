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
 * An EventSessionType value representing the type of this session, such as venue or online.
 *
 * Class EventSessionType
 * @package Arlo\AuthAPI\Enum
 * @copyright 2022 Palmeira Group Ltd.{@link https://palmeiragroup.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_arlo\Arlo\AuthAPI\Enum;

class EventSessionType {
    /**
     * @var string Describes a session that runs at a venue.
     */
    const VENUE = 'Venue';
    /**
     * @var string Describes a session that runs online, such as a webinar.
     */
    const ONLINE = 'Online';
}
