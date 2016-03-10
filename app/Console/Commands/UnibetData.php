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

class UnibetData extends Command {

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
	protected $name = 'winner:getUniBetData';



	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Collect Data from Uni Bet API';

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
        $apiMaster = $this->apiMaster->where('id',2,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        $this->UniBetData($this->api_id);
        //$this->CoralOutcomeData($this->api_id);
    }

    public function UniBetData($apiID)
    {
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $venue_array = array();
        $event_info = array();
        $venue_info = array();
       $event_array = array();

        $del_events =  $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->delete();

        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }

        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            #$event_ids = $getEvent->event_id;
            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_key = str_slug($event_key,'-');
            $event_array[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }

        #dd($event_array);
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
#$this->info($sql);
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
                    $this->info($start_time);
                    #$settled =$event->state;
                    $event_ids = $event_id;
                    /*
                    $other_array=array();
                    $other_data = json_encode($other_array);
                    */
                    $event_keys = $event_name.'-'.$start_time.'-'.$venue_id;
                    $event_keys = str_slug($event_keys,'-');
                   # dd($event_keys);

                    if (array_key_exists($event_keys, $event_array)) {
                        $event_ids =$event_array[$event_keys];

                    } else {
                        $event_array[$event_keys] = $event_ids;
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
#$this->info($event_ids);
                    }
                   # unset($event_array);
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
