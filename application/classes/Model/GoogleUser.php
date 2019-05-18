<?php

use GuzzleHttp\Client;

class Model_GoogleUser extends ORM
{
    protected $_table_name = 'users';
    protected $_created_column = 'created_at';

    public function sync_calender_events($client)
    {
        $service = new Google_Service_Calendar($client);
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 5,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        foreach ($events as $event) {
            Model_Events::insert_or_update_event($event,$this->get('id'));
        }
    }

    public function enable_calendar_watch()
    {
        $client = new Client();
        $header = array("Authorization"=>"Bearer " . $this->get('access_token'));
        $response = $client->request('POST', 'https://www.googleapis.com/calendar/v3/calendars/primary/events/watch', [
            'form_params' => [
                'id' => $this->get_guid(),
                'type' => 'web_hook',
                'address'=> WEBHOOK_URL,
                'token'=>$this->get_guid(),
            ],
            'headers'=>$header
        ]);
        if($response->getStatusCode()==200)
        {
            $body = json_decode($response->getBody(),true);
            $this->set('resource_id',$body['resourceId']);
            $this->save();
        }
        $log = Log::instance();
        $log->add(Log::INFO,json_encode($response->getBody()));
    }

    public function get_guid() {
        $data = PHP_MAJOR_VERSION < 7 ? openssl_random_pseudo_bytes(16) : random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}