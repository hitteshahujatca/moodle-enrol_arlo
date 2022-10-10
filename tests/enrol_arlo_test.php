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
 * Contact merge tests
 *
 * @package   enrol_arlo {@link https://docs.moodle.org/dev/Frankenstyle}
 * @category  phpunit
 * @copyright 2018 LearningWorks Ltd {@link http://www.learningworks.co.nz}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use enrol_arlo\local\persistent\event_template_persistent;
use enrol_arlo\local\persistent\event_persistent;
use enrol_arlo\local\handler\event_session_persistent;
use enrol_arlo\Arlo\AuthAPI\Enum\EventSessionStatus;

global $CFG;
class enrol_arlo_testcase extends advanced_testcase {
    /**
     * Test set up.
     */
    protected function setUp(): void {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/arlo/lib.php');
        $this->setAdminUser();
        /** @var enrol_arlo_generator $plugingenerator */
        $this->plugingenerator = $this->getDataGenerator()->get_plugin_generator('enrol_arlo');
        // Enable and setup plugin.
        $this->plugingenerator->enable_plugin();
        $this->plugingenerator->setup_plugin();
    }

    /**
     * Test event session calendar entries are created.
     */
    public function test_event_session_calendar_entries_created() {
        global $DB;
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user1->userid = $user1->id;
        $contact1 = $this->plugingenerator->create_contact($user1);

        $template1 = $this->plugingenerator->create_event_template();
        $event1 = $this->plugingenerator->create_event($template1);
        $eventsession1 = $this->plugingenerator->create_event_session($event1);
        $category1 = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(['category' => $category1->id]);

        $enrolinstance1 = $this->plugingenerator->create_event_enrolment_instance($course1, $event1, ['customint2' => -1]);
        enrol_get_plugin('arlo')->enrol($enrolinstance1, $user1);
        $count = count($DB->get_records('event', ['uuid' => $eventsession1->get('sourceid')]));
        $this->assertEquals(1, $count);

        // Add multiple sessions to the event.
        $eventsession2 = $this->plugingenerator->create_event_session($event1);
        $eventsession3 = $this->plugingenerator->create_event_session($event1);
        $count = count($DB->get_records('event', ['instance' => $enrolinstance1->id]));
        $this->assertEquals(3, $count);
    }

    /**
     * Test event session calendar entries are updated.
     */
    public function test_event_session_calendar_entries_updated() {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $user1->userid = $user1->id;
        $template1 = $this->plugingenerator->create_event_template();
        $event1 = $this->plugingenerator->create_event($template1);
        $eventsession1 = $this->plugingenerator->create_event_session($event1);
        $category1 = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(['category' => $category1->id]);
        $enrolinstance1 = $this->plugingenerator->create_event_enrolment_instance($course1, $event1, ['customint2' => -1]);
        enrol_get_plugin('arlo')->enrol($enrolinstance1, $user1);
        $count = count($DB->get_records('event', ['uuid' => $eventsession1->get('sourceid')]));
        $this->assertEquals(1, $count);
        // Change session name.
        $eventsession1->set('description', 'Changed Description of Session Event');
        $eventsession1->save();
        $calevent = $DB->get_record('event', ['uuid' => $eventsession1->get('sourceid')]);
        $this->assertEquals($calevent->description, $eventsession1->get('description'));
        // Check that the calendar event is updated too.
        $eventsession1->set('sourcestatus', EventSessionStatus::CANCELLED);
        $eventsession1->save();
        // Calendar entry should be deleted.
        $this->assertFalse($DB->record_exists('event', ['uuid' => $eventsession1->get('sourceid')]));
    }
}
