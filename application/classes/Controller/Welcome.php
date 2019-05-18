<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{

        try
        {
            $client = new Google_Client();
            $client->setApplicationName('Omnify-client');
            $client->setScopes([Google_Service_Calendar::CALENDAR_READONLY,Google_Service_Plus::USERINFO_EMAIL,Google_Service_Plus::PLUS_LOGIN]);
            $client->setAuthConfig(getenv('CREDENTIALS'));
            $client->setAccessType('offline');
            $client->setRedirectUri(REDIRECT_URL);
        }
        catch (Google_Exception $exception)
        {
            error_log(json_encode($exception));
        }

        $authUrl = $client->createAuthUrl();
        $session = Session::instance('cookie');
        $google_user_id = $session->get('id');
        if(!empty($google_user_id))
        {
            $user = ORM::factory('GoogleUser')
                ->where('google_id', '=', $google_user_id)
                ->find();
            $user = $user->as_array();
            if(!empty($user))
            {
                View::bind_global('user',$user);
            }
        }
        $this->response->body(View::factory('homepage')->set('auth_url',$authUrl));
    }

}
