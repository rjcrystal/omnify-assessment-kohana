<?php

class Controller_Events extends Controller {

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
                $events = ORM::factory('Events')
                            ->where('user_id','=',$user['id'])->order_by('event_starts_at','desc')
                            ->find_all();
                $this->response->body(View::factory('events')->set('events',$events->as_array())->bind('user',$user));
            }
        }
        else
        {
            $this->response->body(View::factory('error')->set('message',"Failed to get events, try logging in again"));
        }
    }

    public function action_webhook()
    {
        $headers = getallheaders();
        $resource_id = $headers['X-Goog-Resource-Id'];
        $state = $headers['X-Goog-Resource-State'];
        $log = Log::instance();
        $log->add(Log::INFO,$headers);
        if(!empty($resource_id) && $state=='exists')
        {
            $user = ORM::factory('GoogleUser')
                ->where('resource_id', '=', $resource_id)
                ->where('needs_events_refresh', '=', 0)
                ->find();
            if($user->loaded())
            {
                $user->set('needs_events_refresh',1);
                $user->save();
            }
        }
    }
}