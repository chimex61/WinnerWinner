<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Winner\Repositories\Contracts\VenueMasterInterface as VenueMaster;
use App\Winner\Repositories\Contracts\EventMasterInterface as EventMaster;
use App\Winner\Repositories\Contracts\GameGroupMasterInterface as GameGroupMaster;
use App\Winner\Repositories\Contracts\ApiMasterInterface as ApiMaster;
use App\Winner\Repositories\Contracts\OutcomeMasterInterface as OutcomeMaster;
use DB;

class getGameEvent extends Command {

    var $api_id;
    var $base_api_url;
    var $response_formate;
    var $api_auth;
    var $call_url;
    var $response;
    var $json_data;
    var $xml_data;
    var $outcome_array;
    var $sql_array;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'winner:getEvent';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
    protected $description = 'Get Events Data From Sky Bet API, Uni Bet API, William Hills API, Bet Fred API, Bet Fair API, Coral API.';

    private $venueMaster;
    private $eventMaster;
    private $gameGroupMaster;
    private $apiMaster;
    private $outcomeMaster;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
    public function __construct(VenueMaster $venueMaster, EventMaster $eventMaster, GameGroupMaster $gameGroupMaster, ApiMaster $apiMaster, OutcomeMaster $outcomeMaster)
    {
        parent::__construct();
        $this->eventMaster = $eventMaster;
        $this->venueMaster = $venueMaster;
        $this->gameGroupMaster = $gameGroupMaster;
        $this->apiMaster = $apiMaster;
        $this->outcomeMaster = $outcomeMaster;
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $apiMasters = $this->apiMaster->get();
        foreach($apiMasters as $apiMaster) {
            $this->base_api_url = $apiMaster->base_url;
            $this->api_id = $apiMaster->id;
            $this->api_auth = $apiMaster->auth;
            $this->api_name = $apiMaster->name;
            $this->response_formate = "json";
            if($this->api_id == 1){ $this->SkyBetData($this->api_id);}

            if($this->api_id == 3){ $this->WilliamHillsData($this->api_id);}
            if($this->api_id == 4){/* $this->BetFredData($this->api_id);*/}
            if($this->api_id == 5){/* $this->BetFairData($this->api_id);*/}
            if($this->api_id == 6){ $this->CoralData($this->api_id);}
            if($this->api_id == 2){ $this->UniBetData($this->api_id);}
        }
	}

    public function SkyBetData($apiID)
    {
        $start_times = time();
        $this->api_id = $apiID;
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();
        $event_array = array();
        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            #$event_ids = $getEvent->event_id;
            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }


            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_array[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
        foreach ($getGameGroups as $getGameGroup) {
            #$g_g_name = $getGameGroup->g_g_name;
            #$g_g_main_id = $getGameGroup->id;
            #$game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $g_g_id = ltrim($g_g_id,"/");
            $url_event = $this->base_api_url ."/sportsapi/v2/"."$g_g_id"."?".$this->api_auth;
            $this->response = get_url($url_event);
            $this->json_data = json_decode($this->response[0]);
            $events = $this->json_data->events;
            $events = (array) $events;
            foreach($events as $ek=>$event)
            {
                #$venue_name = $event->name;
                if(array_key_exists($event->name,$venue_array))
                {
                    $venue_id =$venue_array[$event->name];
                }
                else
                {
                    $venue_id = uniqid("v");
                    $venue_array[$event->name] = $venue_id;
                    $venue_info[] = array(
                        "venue_name"=>$event->name,
                        "venue_id"=>$venue_id,
                        "g_g_id"=>$g_g_id,
                        "game_id"=>$getGameGroup->game_id,
                        "api_id"=>$this->api_id,
                        "date_stamp"=>$today_stamp,
                    );
                    #$sql = $this->venueMaster->create($venue_info);
#$this->info($sql);
                }
                $venue_events = $event->events;
                foreach($venue_events as $vek => $venue_event)
                {
                    #$event_name = $venue_event->desc;
                    $start_time = date("Y-m-d H:i:s",$venue_event->start_time);
                    #$settled = $venue_event->settled;
                    $event_id = $vek;
                    /*
                    $other_array =array(
                        "outright"=>$venue_event->outright,
                        "url"=>$venue_event->url,
                    );
                    $other_data =json_encode($other_array);
                    */
                    $event_keys = $venue_event->desc.'-'.$start_time.'-'.$venue_id;

                    $event_keys = str_slug($event_keys,'-');
                    if (array_key_exists($event_keys, $event_array)) {
                        $event_id =$event_array[$event_keys];
                        #$this->info($event_id);
                    } else {
                        $event_array[$event_keys] = $event_id;
                        $event_info[] = array(
                            "event_name" => $venue_event->desc,
                            "start_date" => $start_time,
                            #"state" => $venue_event->settled,
                            "venue_id" => $venue_id,
                            "event_id" => $vek,
                            #"other" => $other_data,
                            "g_g_id" => $g_g_id,
                            "game_id" => $getGameGroup->game_id,
                            "api_id" => $this->api_id,
                            "date_stamp" => $today_stamp,
                            #"type_id" => 0,
                            #"bet_till_date" => '0000-00-00 00:00:00',
                        );
                        #  $sql = $this->eventMaster->create($event_info);
#$this->info($sql);
                    }
                    # unset($event_array);
                }
            }
        }
        if(!empty($venue_info)){DB::table('venuemaster')->insert($venue_info);}
        if (!empty($event_info)) {
            $chunk_event = array_chunk($event_info, 1000);
            foreach ($chunk_event as $chunk_data) {
                #dd($chunk_data);
                DB::table('eventmaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($event_info). " SkyBet Events retrieved in " . date('s', $duration) . " seconds\n");
    }

    public function UniBetData($apiID)
    {
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();


        #$del_events =  $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->delete();

        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }

        $event_arrays = array();
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            #$event_ids = $getEvent->event_id;
            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }

            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_arrays[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }

      #  dd($event_arrays);
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
        foreach ($getGameGroups as $getGameGroup) {
            #$g_g_id=$getGameGroup->g_g_id;
            #$g_g_main_id =$getGameGroup->id;
            #$game_id=$getGameGroup->game_id;
            $venue_url = $this->base_api_url ."sportsbook/event/group/$getGameGroup->g_g_id.".$this->response_formate."?".$this->api_auth;
            #  dd($venue_url);
            $this->response = get_url($venue_url);
            $this->json_data = json_decode($this->response[0]);
            $event_array = array();
            foreach($this->json_data->events as $ek => $event)
            {
                $venue_name = $event->name;
                $event_id = $event->id;
                $event_array[$venue_name][$event_id]=$event;

            }
            #dd($event_array);
            foreach($event_array as $venue_name => $events)
            {

                if(array_key_exists($venue_name,$venue_array))
                {
                    $venue_id =$venue_array[$venue_name];
                }
                else
                {
                    $venue_id = uniqid("v");
                    $venue_array[$venue_name]=$venue_id;
                    $venue_info[] = array(
                        "venue_name"=>$venue_name,
                        "venue_id"=>$venue_id,
                        "g_g_id"=>$getGameGroup->g_g_id,
                        "game_id"=>$getGameGroup->game_id,
                        "api_id"=>$this->api_id,
                        "date_stamp"=>$today_stamp,
                    );
                    # $sql = $this->venueMaster->create($venue_info);

                }

                foreach($events as $event_id => $event)
                {
                    $event_name = $venue_name;
                    $start_time =$event->start;

                    $start_time = str_replace("T", " ",$start_time);
                    $start_time = str_replace("Z", " ",$start_time);
                    $start_time = trim($start_time);
                    $start_time = $start_time.':00';
                    $start_time = date("Y-m-d H:i:s",strtotime($start_time) +3600);
                    #$settled =$event->state;
                    $event_ids = $event_id;
                    #$other_array=array();
                    #$other_data = json_encode($other_array);
                    $event_keys = $event_name.'-'.$start_time.'-'.$venue_id;
                    $event_keys = str_slug($event_keys,'-');

                    if (array_key_exists($event_keys, $event_arrays)) {
                        $event_ids =$event_arrays[$event_keys];

                    } else {
                        $event_arrays[$event_keys] = $event_ids;
                        $event_info[] = array(
                            "event_name" => $event_name,
                            "start_date" => $start_time,
                            #"state" => $settled,
                            "venue_id" => $venue_id,
                            "event_id" => $event_id,
                            #"other" => $other_data,
                            "g_g_id" => $getGameGroup->g_g_id,
                            "game_id" => $getGameGroup->game_id,
                            "api_id" => $this->api_id,
                            "date_stamp" => $today_stamp,
                            #"type_id" => 0,
                            #"bet_till_date" => 0,
                        );
                        #   $sql = $this->eventMaster->create($event_info);
#$this->info($event_keys);
                    }
                   #  unset($event_array);
                }
            }
        }
        # dd($event_info);
        if(!empty($venue_info)){DB::table('venuemaster')->insert($venue_info);}
        if (!empty($event_info)) {
            $chunk_event = array_chunk($event_info, 1000);
            foreach ($chunk_event as $chunk_data) {
                #dd($chunk_data);
                DB::table('eventmaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($event_info). " UniBet Events retrieved in " . date('s', $duration) . " seconds\n");
    }

    public function WilliamHillsData($apiID)
    {
        $start_times = time();
        $this->api_id = $apiID;
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();
        $event_array = array();
        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            #$event_ids = $getEvent->event_id;
            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }

            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_array[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
        foreach ($getGameGroups as $getGameGroup) {
            $g_g_main_id = $getGameGroup->id;
            $game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $venue_url = $this->base_api_url ."openbet_cdn?action=template&template=getHierarchyByMarketType&classId=".$g_g_id."&marketSort=--&filterBIR=N";
            $this->response = get_url($venue_url);
            $this->xml_data = simplexml_load_string($this->response[0]);
            $venues=$this->xml_data->response->williamhill->class->type;
            foreach($venues as $v => $venue){
                $venue_name = $venue['name']."";
                if(array_key_exists($venue_name,$venue_array))
                {
                    $venue_id =$venue_array[$venue_name];
                }
                else
                {
                    $venue_id = uniqid("v");
                    $venue_array[$venue_name] = $venue_id;
                    $venue_info[] = array(
                        "venue_name"=>$venue_name,
                        "venue_id"=>$venue_id,
                        "g_g_id"=>$g_g_id,
                        "game_id"=>$game_id,
                        "api_id"=>$this->api_id,
                        "date_stamp"=>$today_stamp,
                    );
                    #  $sql = $this->venueMaster->create($venue_info);
#$this->info($sql);

                }
                #$type_id = $venue['id'];
                $markets = $venue->market;
                foreach($markets as $market)
                {
                    $event_id = "". $market['id'];
                    $event_name = $market['name'];
                    $start_time = $market['date'] ." ". $market['time'];
                    /*
                    $bet_till_date = $market['betTillDate'] . " " . $market['betTillTime'];
                    $settled ="";
                    $other_array=array(
                        "ewPlaces"=> $market['ewPlaces'],
                        "ewReduction"=> $market['ewReduction'],
                    );
                    $other_data =json_encode($other_array);
                    */
                    $event_keys = $event_name.'-'.$start_time.'-'.$venue_id;
                    $event_keys = str_slug($event_keys,'-');
                    if (array_key_exists($event_keys, $event_array)) {
                        $event_id =$event_array[$event_keys];
                    } else {
                        $event_array[$event_keys] = $event_id;
                        $event_info[] = array(
                            "event_name" => $event_name,
                            "start_date" => $start_time,
                            #"state" => $settled,
                            "venue_id" => $venue_id,
                            "event_id" => $event_id,
                            #"other" => $other_data,
                            "g_g_id" => $g_g_id,
                            "game_id" => $game_id,
                            "api_id" => $this->api_id,
                            "date_stamp" => $today_stamp,
                            #"type_id" => $type_id,
                            #"bet_till_date" => $bet_till_date,
                        );

                        #  $sql = $this->eventMaster->create($event_info);
#$this->info($sql);
                    }
                    # unset($event_array);
                }
            }
        }
        if(!empty($venue_info)){DB::table('venuemaster')->insert($venue_info);}
        if (!empty($event_info)) {
            $chunk_event = array_chunk($event_info, 1000);
            foreach ($chunk_event as $chunk_data) {
                #dd($chunk_data);
                DB::table('eventmaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($event_info). " WilliamHills Events retrieved in " . date('s', $duration) . " seconds\n");
    }

    public function BetFredData($apiID)
    {
        $start_times = time();
        $this->api_id = $apiID;
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();
        $event_array = array();
        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }

        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            #$event_ids = $getEvent->event_id;
            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }

            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_array[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
        foreach ($getGameGroups as $getGameGroup) {
            $group_name = $getGameGroup->g_g_name;
            $g_g_main_id = $getGameGroup->id;
            $game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $venue_url = $this->base_api_url . $group_name . ".xml";
            $this->response = get_url($venue_url);
            $this->xml_data = simplexml_load_string($this->response[0]);
            if (isset($this->xml_data['status'])) {
                #   $this->info('no file');
                continue;
            } else {
                $venues = $this->xml_data->event;
                foreach ($venues as $v => $venue) {
                    $venue_name = ($venue['venue'] == "") ? $venue['name'] : $venue['venue'];
                    $venue_name = $venue_name . "";
                    if (array_key_exists($venue_name, $venue_array)) {
                        $venue_id = $venue_array[$venue_name];
                    } else {
                        $venue_id = uniqid("v");
                        $venue_array[$venue_name] = $venue_id;
                        $venue_info[] = array(
                            "venue_name" => $venue_name,
                            "venue_id" => $venue_id,
                            "g_g_id" => $g_g_id,
                            "game_id" => $game_id,
                            "api_id" => $this->api_id,
                            "date_stamp" => $today_stamp,
                        );
                        #  $sql = $this->venueMaster->create($venue_info);
#$this->info($sql);
                    }
                    $event_name = $venue['name'];
                    $event_id = $venue['eventid'] . "";
                    $event_date = $venue['date'];
                    $event_time = $venue['time'];
                    if ($event_date == '' && $event_time == '') {
                        $start_time = '0000-00-00 00:00:00';
                    } elseif ($event_date != '' && $event_time == '') {
                        $start_time = substr($event_date, 0, 4) . "-" . substr($event_date, 4, 2) . "-" . substr($event_date, 6, 2) . " 00:00:00";
                    } elseif ($event_date == '' && $event_time != '') {
                        $start_time = "0000-00-00 " . substr($event_time, 0, 2) . ":" . substr($event_time, 2, 2) . ":00";
                    } else {
                        $start_time = substr($event_date, 0, 4) . "-" . substr($event_date, 4, 2) . "-" . substr($event_date, 6, 2) . " " . substr($event_time, 0, 2) . ":" . substr($event_time, 2, 2) . ":00";
                    }
                   $event_keys = $event_name . '-' . $start_time . '-' . $venue_id;
                    $event_keys = str_slug($event_keys,'-');
                    if (array_key_exists($event_keys, $event_array)) {
                        // $this->info('already exists');
                        $event_id = $event_array[$event_keys];
                    } else {
                        $event_array[$event_keys] = $event_id;
                        $event_info[] = array(
                            "event_name" => $event_name,
                            "start_date" => $start_time,
                            #"state" => "",
                            "venue_id" => $venue_id,
                            "event_id" => $event_id,
                            #"other" => "",
                            #"type_id" => "",
                            #"bet_till_date" => "",
                            "g_g_id" => $g_g_id,
                            "game_id" => $game_id,
                            "api_id" => $this->api_id,
                            "date_stamp" => $today_stamp,
                        );
                        # $sql = $this->eventMaster->create($event_info);
#$this->info($sql);
                    }
                    # unset($event_array);
                }
            }
        }
        if(!empty($venue_info)){DB::table('venuemaster')->insert($venue_info);}
        if (!empty($event_info)) {
            $chunk_event = array_chunk($event_info, 1000);
            foreach ($chunk_event as $chunk_data) {
                #dd($chunk_data);
                DB::table('eventmaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($event_info). " BetFred Events retrieved in " . date('s', $duration) . " seconds\n");
    }

    public function BetFairData($apiID)
    {
        $start_times = time();
        $this->api_id = $apiID;
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();
        $event_array = array();
        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            #$event_ids = $getEvent->event_id;
            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }

            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_arrays[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
        foreach ($getGameGroups as $getGameGroup) {
            $g_g_name = $getGameGroup->g_g_name;
            $g_g_main_id = $getGameGroup->id;
            $game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $url_event = $this->base_api_url ."getListParents";
            $data_array=array("token"=>$this->api_auth,"bf_sport_id"=>7);
            $data_json=json_encode($data_array);
            $this->response = get_url_j($url_event,$data_json,true);
            $this->json_data = json_decode($this->response[0]);
            $venues = $this->json_data;

            foreach($venues as $vk=>$venue)
            {

                $venue_name = $venue->bf_parent_name;

                if(array_key_exists($venue_name,$venue_array))
                {
                    #$this->info(dd('exists'));
                    $venue_id =$venue_array[$venue_name];
                }
                else
                {
                    $venue_id = uniqid("v");
                    $venue_array[$venue_name] = $venue_id;
                    $venue_info[] = array(
                        "venue_name"=>$venue_name,
                        "venue_id"=>$venue_id,
                        "g_g_id"=>$g_g_id,
                        "game_id"=>$game_id,
                        "api_id"=>$this->api_id,
                        "date_stamp"=>$today_stamp,
                    );
                    #   $sql = $this->venueMaster->create($venue_info);

                }


                $bf_parent_id = $venue->bf_parent_id;
                #  dd($bf_parent_id);

                #   $this->info($bf_parent_id);
                #$this->info(dd($bf_parent_id));
                $url_event = $this->base_api_url ."getListEvents";
                #$this->info(dd($url_event));
                $data_array=array("token"=>$this->api_auth,"bf_parent_id"=>$bf_parent_id);
                $data_json=json_encode($data_array);
                #  $this->info(dd($data_json));
                $this->response = get_url_jb($url_event,$data_json,true);
                $this->json_data = json_decode($this->response[0]);
                # $this->info($this->json_data->status);
                # dd($this->json_data);
                if(isset($this->json_data->status)) {
                    continue;

                }else{
                    foreach ($this->json_data as $mk => $market) {
                        $bf_market_id = $market->bf_event_id;
                        #dd($bf_market_id);
                        #$market_type = $market->market_type;
                        $real_start_time = (array)$market->real_start_time; //mili seconds
                        $real_start_time = $real_start_time['$date'] / 1000;

                        $event_name = $market->event_name;
                        $start_time = date("Y-m-d H:i:s", $real_start_time);
                        #dd($start_time);
                        #$settled = $market->status;
                        $event_id = $bf_market_id;
                        #$other_array = array();
                        #$other_data = json_encode($other_array);

                        $event_keys = $event_name . '-' . $start_time . '-' . $venue_id;
                        if (array_key_exists($event_keys, $event_array)) {
                            // $this->info('already exists');
                            $event_id =$event_array[$event_keys];
                        } else {
                            $event_array[$event_keys] = $event_id;
                            $event_info[] = array(

                                "event_name" => $event_name,
                                "start_date" => $start_time,
                                #"state" => "",
                                "venue_id" => $venue_id,
                                "event_id" => $event_id,
                                #"other" => $other_data,
                                "g_g_id" => $g_g_id,
                                "game_id" => $game_id,
                                "api_id" => $this->api_id,
                                "date_stamp" => $today_stamp,
                                #"type_id" => 0,
                                #"bet_till_date" => 0,
                            );
                            #   $sql = $this->eventMaster->create($event_info);

                        }
                        #   unset($event_array);
                    }

                }


            }



        }
#dd('stop');
        if(!empty($venue_info)){DB::table('venuemaster')->insert($venue_info);}
        if (!empty($event_info)) {
            $chunk_event = array_chunk($event_info, 1000);
            foreach ($chunk_event as $chunk_data) {
                #dd($chunk_data);
                DB::table('eventmaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($event_info). " Events retrieved in " . date('s', $duration) . " seconds\n");
    }

    public function CoralData($apiID)
    {
        $start_times = time();
        $this->api_id = $apiID;
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();
        $event_array = array();
        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }

            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_array[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }

        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
       #dd($getGameGroups);
        foreach ($getGameGroups as $getGameGroup) {
            #$g_g_name = $getGameGroup->g_g_name;
            #$g_g_main_id = $getGameGroup->id;
            $game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $url_event = $this->base_api_url . "oxi/pub/getNextEvents?class=" . $g_g_id . "&numEvents=100";
         #  dd($url_event);
            $this->response = get_url($url_event, '', true);
         //   dd($this->response);
            $this->xml_data = simplexml_load_string($this->response[0]);
            $events = $this->xml_data->response->event;

            foreach ($events as $vk => $event) {
                $event_name = validate_string($event['name'] . "");
                $venue_name = validate_string($event['typeName'] . "");
                $event_id = $event['id'];
                #$type_id = $event['typeId'];
                if (array_key_exists($venue_name, $venue_array)) {
                    $venue_id = $venue_array[$venue_name];
                } else {
                    $venue_id = uniqid("v");
                    $venue_array[$venue_name] = $venue_id;
                    $venue_info[] = array(
                        "venue_name" => $venue_name,
                        "venue_id" => $venue_id,
                        "g_g_id" => $g_g_id,
                        "game_id" => $game_id,
                        "api_id" => $this->api_id,
                        "date_stamp" => $today_stamp,
                    );
                }
                $start_time = $event['date'] . " " . $event['time'];
                /*
                $bet_till_date = $event['betTillDate'] . " " . $event['betTillTime'];
                $settled = $event['status'];
                $other_array = array(// "name"=> $event_name_sub,
                );
                $other_data = json_encode($other_array);
                */
                $event_keys = $event_name.'-'.$start_time.'-'.$venue_id;
                $event_keys = str_slug($event_keys,'-');
                if (array_key_exists($event_keys, $event_array)) {
                    $event_id =$event_array[$event_keys];
                } else {
                    $event_array[$event_keys] = $event_id;
                    $event_info[] = array(
                        "event_name" => $event_name,
                        "start_date" => $start_time,
                        #"state" => $settled,
                        "venue_id" => $venue_id,
                        "event_id" => $event_id,
                        #"other" => $other_data,
                        #"type_id" => $type_id,
                        #"bet_till_date" => $bet_till_date,
                        "g_g_id" => $g_g_id,
                        "game_id" => $game_id,
                        "api_id" => $this->api_id,
                        "date_stamp" => $today_stamp,
                    );
                    #   $sql = $this->eventMaster->create($event_info);
                }
            }
        }
        if(!empty($venue_info)){DB::table('venuemaster')->insert($venue_info);}
        if (!empty($event_info)) {
            $chunk_event = array_chunk($event_info, 1000);
            foreach ($chunk_event as $chunk_data) {
                #dd($chunk_data);
                DB::table('eventmaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($event_info). " Coral Events retrieved in " . date('s', $duration) . " seconds\n");
    }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
