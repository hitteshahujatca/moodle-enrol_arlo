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
 * Event sessions job class.
 *
 * @package   enrol_arlo {@link https://docs.moodle.org/dev/Frankenstyle}
 * @copyright 2022 Palmeira Group Ltd.{@link https://palmeiragroup.co.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_arlo\local\job;

defined('MOODLE_INTERNAL') || die();

use enrol_arlo\api;
use enrol_arlo\Arlo\AuthAPI\RequestUri;
use enrol_arlo\Arlo\AuthAPI\Resource\AbstractCollection;
use enrol_arlo\local\client;
use enrol_arlo\local\config\arlo_plugin_config;
use enrol_arlo\local\persistent\event_session_persistent;
use enrol_arlo\local\response_processor;
use GuzzleHttp\Psr7\Request;
use Exception;
use moodle_exception;

class event_sessions_job extends job {

    /** @var int TIME_PERIOD_DELAY override base class. */
    const TIME_PERIOD_DELAY = 0;

    /** @var int TIME_PERIOD_EXTENSION override base class. */
    const TIME_PERIOD_EXTENSION = 0;

    /** @var string area */
    const AREA = 'enrolment';

    /** @var string type */
    const TYPE = 'event_sessions';

    /**
     * Run the job.
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function run() {
        echo "+++++++ RUNNING EVENT SESSIONS JOB....";
        $pluginconfig = new arlo_plugin_config();
        $jobpersistent = $this->get_job_persistent();
        echo "...for...".$jobpersistent->get('endpoint');
        try {
            $hasnext = true;
            while ($hasnext) {
                $hasnext = false; // Break paging by default.
                $uri = new RequestUri();
                $uri->setHost($pluginconfig->get('platform'));
                $uri->setPagingTop(250);
                $uri->setResourcePath($jobpersistent->get('endpoint'));
                $uri->addExpand('EventSession/Event');
                $filter = "(LastModifiedDateTime gt datetime('" . $jobpersistent->get('lastsourcetimemodified') . "'))";
                if ($jobpersistent->get('lastsourceid')) {
                    $filter .= " OR ";
                    $filter .= "(LastModifiedDateTime eq datetime('" . $jobpersistent->get('lastsourcetimemodified') . "')";
                    $filter .= " AND ";
                    $filter .= "SessionID gt " . $jobpersistent->get('lastsourceid') . ")";
                }
                $uri->setFilterBy($filter);
                $uri->setOrderBy("LastModifiedDateTime ASC,SessionID ASC");
                $request = new Request('GET', $uri->output(true));
                $response = client::get_instance()->send_request($request);
                $collection = response_processor::process($response);
                $lastsourcetimemodified = 0;
                if ($collection instanceof AbstractCollection && $collection->count() > 0) {
                    foreach ($collection as $resource) {
                        $sourceid       = $resource->SessionID;
                        $name           = $resource->Name;
                        $description    = $resource->Description;
                        $startdatetime  = $resource->StartDateTime;
                        $finishdatetime = $resource->FinishDateTime;
                        $starttimezoneabbr = $resource->StartTimeZoneAbbr;
                        $finishtimezoneabbr = $resource->FinishTimeZoneAbbr;
                        $sessiontype = $resource->SessionType;
                        $sourcestatus   = $resource->Status;
                        $sourcecreated  = $resource->CreatedDateTime;
                        $sourcemodified = $resource->LastModifiedDateTime;
                        $event  = $resource->getEvent();
                        if ($event) {
                            $sourceeventid   = $event->EventID;
                            $sourceeventguid = $event->UniqueIdentifier;
                        }
                        try {
                            $eventsession = event_session_persistent::get_record(
                                ['sourceid' => $sourceid]
                            );
                            if (!$eventsession) {
                                $eventsession = new event_session_persistent();
                            }
                            $eventsession->set('sourceid', $sourceid);
                            if (is_null($resource->Name)) {
                                // For events that have no / single day sessions, use the Event Code as Name.
                                $name = $event->Code;
                            }
                            $eventsession->set('name', $name);
                            if (is_null($resource->Description)) {
                                $description = '';
                            }
                            $eventsession->set('description', $description);
                            $eventsession->set('startdatetime', $startdatetime);
                            $eventsession->set('finishdatetime', $finishdatetime);
                            $eventsession->set('starttimezoneabbr', $starttimezoneabbr);
                            $eventsession->set('finishtimezoneabbr', $finishtimezoneabbr);
                            $eventsession->set('sessiontype', $sessiontype);
                            $eventsession->set('sourcestatus', $sourcestatus);
                            $eventsession->set('sourcecreated', $sourcecreated);
                            $eventsession->set('sourcemodified', $sourcemodified);
                            $eventsession->set('sourceeventid', $sourceeventid);
                            $eventsession->set('sourceeventguid' , $sourceeventguid);
                            $eventsession->save();
                            $lastsourcetimemodified = $sourcemodified;
                            // Update scheduling information on persistent after successfull save.
                            $jobpersistent->set('timelastrequest', time());
                            $jobpersistent->set('lastsourceid', $sourceid);
                            $jobpersistent->set('lastsourcetimemodified', $lastsourcetimemodified);
                            $jobpersistent->update();
                        } catch (moodle_exception $exception) {
                            \debugging($exception->getMessage(), DEBUG_DEVELOPER);
                            $this->add_error($exception->getMessage());
                        }
                    }
                }
                $hasnext = (bool) $collection->hasNext();
            }
        } catch (moodle_exception $exception) {
            \debugging($exception->getMessage(), DEBUG_DEVELOPER);
            $this->add_error($exception->getMessage());
            return false;
        }
        return true;
    }
}
