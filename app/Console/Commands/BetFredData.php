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

class BetFredData extends Command {

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
	protected $name = 'winner:getBetFredData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
    protected $description = 'Get Data From Betfred API';

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
        $apiMaster = $this->apiMaster->where('id',4,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        $this->BetFredData($this->api_id);
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

            $start_dt = strtotime(date('Y-m-d',strtotime($getEvent->start_date)));
            $date_st = $getEvent->date_stamp;

            if($start_dt != $date_st){
                $event_delete = $this->eventMaster->where('event_id',$getEvent->event_id,'=')->delete();
            }

            $event_ids = $getEvent->event_id;
            $event_key = $getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id;
            $event_array[$event_key] = $event_ids;
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
