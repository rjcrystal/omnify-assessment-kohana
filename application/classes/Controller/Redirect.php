<?php

use Carbon\Carbon;

class Controller_Redirect extends Controller
{
    public static $log;

    /**
     * Controller_Redirect constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request,Response $response)
    {
        self::$log = Log::instance();
        parent::__construct($request,$response);
    }

    public function action_index()
    {
        $data = array_filter($_GET);
        try
        {
            $client = new Google_Client();
            $client->setApplicationName('Omnify-test-application');
            $client->setScopes([Google_Service_Calendar::CALENDAR_READONLY,Google_Service_Plus::USERINFO_EMAIL,Google_Service_Plus::PLUS_LOGIN]);
            $client->setAuthConfig(getenv('CREDENTIALS'));
            $client->setRedirectUri(REDIRECT_URL);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent prompt');
            $accessToken = $client->fetchAccessTokenWithAuthCode($data['code']);
            $client->setAccessToken($accessToken);
            $objOAuthService = new Google_Service_Oauth2($client);

            if (!empty($client->getAccessToken())) {
                $userData = $objOAuthService->userinfo->get();
                if(!empty($userData->getId()))
                {
                    try{
                        $user = ORM::factory('GoogleUser')
                            ->where('google_id', '=', $userData->getId())
                            ->find();
                        if(!$user->loaded())
                        {
                            $user = ORM::factory('GoogleUser');
                            $user->name = $userData->getName();
                            $user->email = $userData->getEmail();
                            $user->google_id = $userData->getId();
                            $user->access_token = $accessToken['access_token'];
                            $user->refresh_token = $accessToken['refresh_token'];
                            $user->created_at = Carbon::now()->toDateTimeString();
                            $user->save();
                            $user->sync_calender_events($client);
                            $user->enable_calendar_watch();
                            $session = Session::instance('cookie');
                            $session->set('id',$userData->getId());
                            Controller::redirect('/events',302);
                        }
                        else if (!empty($user->get('id')))
                        {
                            $user->sync_calender_events($client);
                            $session = Session::instance('cookie');
                            $session->set('id',$userData->getId());
                            Controller::redirect('/events',302);
                        }
                        else
                        {
                            $this->response->body(View::factory('error')->set('message',"Unknown error"));
                        }
                    }
                    catch (HTTP_Exception_Redirect $e) {
                        throw $e;
                    }
                    catch (Kohana_Exception $exception)
                    {
                        self::$log->add(Log::INFO,"kohana exception".$exception->getTraceAsString());
                    }
                }
                else{
                    $this->response->body(View::factory('error')->set('message',"Failed to get user details, please check your permissions."));
                }
            }
            else{
                $this->response->body(View::factory('error')->set('message',"Failed to get access token, please try again."));
            }
        }
        catch (Google_Exception $exception)
        {
            self::$log->add(Log::INFO,"Google exception".$exception->getMessage());
        }
        $this->response->body(View::factory('error')->set('message',"Failed to get calender events, please try again."));
    }
}