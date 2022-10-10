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

use enrol_arlo\Arlo\AuthAPI\Enum\EventSessionStatus;

defined('MOODLE_INTERNAL') || die();
global $CFG;

class enrol_arlo_events_testcase extends \advanced_testcase {

    /**
     * Test set up.
     */
    protected function setUp(): void {
        global $USER;
        // The user we are going to test this on.
        $this->setAdminUser();
        $this->user = $USER;
        $this->course = self::getDataGenerator()->create_course();
    }
    /**
     * Test for session created event.
     */
    public function test_event_session_event_created() {

        $plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_arlo');
        $this->resetAfterTest();
        // Catch the events.
        $sink = $this->redirectEvents();
        $template1 = $plugingenerator->create_event_template();
        $event1 = $plugingenerator->create_event($template1);
        $eventsession1 = $plugingenerator->create_event_session($event1);
        // Capture the event.
        $events = $sink->get_events();
        $sink->clear();
        // Validate the event.
        $event = $events[1];
        $this->assertInstanceOf('enrol_arlo\event\session_created', $event);
        $other = array(
                'id' => $eventsession1->get('id'),
                'sourceid' => $eventsession1->get('sourceid'),
                'name' => $eventsession1->get('name'),
                'platform' => $eventsession1->get('platform'),
                'description' => $eventsession1->get('description'),
                'sourcestatus' => $eventsession1->get('sourcestatus'),
                'sourceeventid' => $event1->get('sourceid'),
                'sourceeventguid' => $event1->get('sourceguid'),
                'startdatetime' => $eventsession1->get('startdatetime'),
                'finishdatetime' => $eventsession1->get('finishdatetime')
        );
        $this->assertEquals($other, $event->other);
    }
    /**
     * Test for session updated event.
     */
    public function test_event_session_event_updated() {
        $plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_arlo');
        $this->resetAfterTest();
        // Catch the events.
        $sink = $this->redirectEvents();
        $template1 = $plugingenerator->create_event_template();
        $event1 = $plugingenerator->create_event($template1);
        $eventsession1 = $plugingenerator->create_event_session($event1);
        // Update name and Status.
        $eventsession1->set('name', '[Cancelled]');
        $eventsession1->set('sourcestatus', EventSessionStatus::CANCELLED);
        $eventsession1->save();
        // Capture the event.
        $events = $sink->get_events();
        $sink->clear();
        // Validate the event.
        $eventcreated = $events[1];
        $this->assertInstanceOf('enrol_arlo\event\session_created', $eventcreated);
        $eventupdated = $events[2];
        $this->assertInstanceOf('enrol_arlo\event\session_updated', $eventupdated);
        $other = array(
            'id' => $eventsession1->get('id'),
            'sourceid' => $eventsession1->get('sourceid'),
            'name' => '[Cancelled]',
            'platform' => $eventsession1->get('platform'),
            'description' => $eventsession1->get('description'),
            'sourcestatus' => EventSessionStatus::CANCELLED,
            'sourceeventid' => $event1->get('sourceid'),
            'sourceeventguid' => $event1->get('sourceguid'),
            'startdatetime' => $eventsession1->get('startdatetime'),
            'finishdatetime' => $eventsession1->get('finishdatetime')
        );
        $this->assertEquals($other, $eventupdated->other);
    }
}
