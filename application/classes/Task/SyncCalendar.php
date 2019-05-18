<?php

class Task_SyncCalendar extends Minion_Task
{
    protected function _execute(array $params)
    {
        $users = ORM::factory('GoogleUser')
            ->where('needs_events_refresh', '=', '1')
            ->find_all();
        $client = new Google_Client();
        $client->setApplicationName('Omnify-test-application');
        $client->setScopes([Google_Service_Calendar::CALENDAR_READONLY, Google_Service_Plus::USERINFO_EMAIL, Google_Service_Plus::PLUS_LOGIN]);
        $client->setAuthConfig(getenv('CREDENTIALS'));
        $client->setRedirectUri(REDIRECT_URL);
        $client->setAccessType('offline');

        foreach ($users as $user) {
            $userAccessToken = $user->get('access_token');
            $userRefreshToken = $user->get('refresh_token');
            if (!empty($userAccessToken)) {
                $token = ['expires_in' => 3600, 'access_token' => $userAccessToken];
                $client->setAccessToken(json_encode($token));
                if ($client->isAccessTokenExpired() && !empty($userRefreshToken)) {
                    $userAccessToken = $client->fetchAccessTokenWithRefreshToken($userRefreshToken);
                }
                $client->setAccessToken($userAccessToken);
                try {
                    $user->sync_calender_events($client);
                    $user->set('needs_events_refresh', 0);
                    $user->save();
                } catch (Exception $exception) {
                    $log = Log::instance();
                    $log->add(Log::ERROR, $exception->getTraceAsString());
                }
            }
        }
    }
}