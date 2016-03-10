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

class CoralData extends Command {

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
	protected $name = 'winner:getCoralData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get Data From Coral';

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
        $apiMaster = $this->apiMaster->where('id',6,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        $this->CoralData($this->api_id);
       // $this->CoralOutcomeData($this->api_id);
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
            #$event_ids = $getEvent->event_id;
            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_array[$event_key] = $getEvent->event_id;
            #$this->info($event_key);
        }
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->get();
        foreach ($getGameGroups as $getGameGroup) {
            #$g_g_name = $getGameGroup->g_g_name;
            #$g_g_main_id = $getGameGroup->id;
            $game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $url_event = $this->base_api_url . "oxi/pub/getNextEvents?class=" . $g_g_id . "&numEvents=100";
            $this->response = get_url($url_event, '', true);
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
