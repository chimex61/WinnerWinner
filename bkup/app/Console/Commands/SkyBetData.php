<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Winner\Repositories\Contracts\VenueMasterInterface as VenueMaster;
use App\Winner\Repositories\Contracts\EventMasterInterface as EventMasters;
use App\Winner\Repositories\Contracts\GameGroupMasterInterface as GameGroupMaster;
use App\Winner\Repositories\Contracts\ApiMasterInterface as ApiMaster;
use App\Winner\Repositories\Contracts\OutcomeMasterInterface as OutcomeMaster;
use App\Models\EventMaster;
use DB;

class SkyBetData extends Command {

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
	protected $name = 'winner:getSkyBetData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get Data From Sky Bet';

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
	public function __construct(VenueMaster $venueMaster, EventMasters $eventMaster, GameGroupMaster $gameGroupMaster, ApiMaster $apiMaster, OutcomeMaster $outcomeMaster)
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
        $apiMaster = $this->apiMaster->where('id',1,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        $this->SkyBetData($this->api_id);
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
            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
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
        $this->info(count($event_info). " Events retrieved in " . date('s', $duration) . " seconds\n");
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
