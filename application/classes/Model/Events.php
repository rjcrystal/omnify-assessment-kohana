<?php

use Carbon\Carbon;

class Model_Events extends ORM {

    protected $_table_name = 'events';

    protected $_created_column = 'created_at';

    public static function insert_or_update_event(Google_Service_Calendar_Event $calendar_event,$userId)
    {
        try{
            $event_check = ORM::factory('Events')
                ->where('event_id', '=', $calendar_event->getId())
                ->where('user_id', '=', $userId)
                ->find();
            $event = new Model_Events();

            if($event_check->loaded())
            {
                $event->id = $event_check->get('id');
                $event->updated_at = Carbon::now()->toDateTimeString();
            }
            else{
                $event->created_at = Carbon::now()->toDateTimeString();
            }

            $event->user_id = $userId;
            $event->status = $calendar_event->getStatus();
            $start = $calendar_event->start->dateTime;
            if (empty($start)) {
                $start = $event->start->date;
            }

            $event->event_id = $calendar_event->getId();
            $event->event_starts_at = Carbon::parse($start)->toDateTimeString();
            $event->summary = $calendar_event->getSummary();
            $event->description = $calendar_event->getDescription();
            $event->location = $calendar_event->getLocation();
            $event->visibility = $calendar_event->getVisibility();
            $event->event_ends_at = Carbon::parse($calendar_event->getEnd()->dateTime)->toDateTimeString();
            $event->save();

        }
        catch (Exception $exception)
        {
            $log = Log::instance();
            $log->write(Log::ERROR,json_encode($exception));
        }
    }
}