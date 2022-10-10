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
 * An EventSessionStatus value representing the current state of this session, such as draft, active or cancelled.
 * Class EventSessionStatus
 * @package Arlo\AuthAPI\Enum
 * @copyright 2022 Palmeira Group Ltd.{@link https://palmeiragroup.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_arlo\Arlo\AuthAPI\Enum;

class EventSessionStatus {
    /**
     * @var string Describes an Event Session that is scheduled, or in progress, with a FinishDateTime in the future.
     */
    const ACTIVE = 'Active';
    /**
     * @var string Describes an Event Session that has been cancelled.
     */
    const CANCELLED = 'Cancelled';
    /**
     * @var string Describes an Event Session with a FinishDateTime that has now elapsed.
     */
    const COMPLETED = 'Completed';
    /**
     * @var string Describes an Event Session that has been created, but with unconfirmed details.
     * Events in this state are not published and cannot accept registrations.
     */
    const DRAFT = 'Draft';
    /**
     * @var string Describes an Event Session with a state not supported by the API representation.
     */
    const UNKNOWN = 'Unknown';
}
