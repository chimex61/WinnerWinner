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

class BetFairData extends Command {

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
	protected $name = 'winner:getBetFairData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
    protected $description = 'Get Data From BetFair';

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
        $apiMaster = $this->apiMaster->where('id',5,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        $this->BetFairData($this->api_id);
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
        $event_keys = array();
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
            $event_arrays[$event_key] = $getEvent->event_id;
            $event_keys[$getEvent->event_id] = $getEvent->event_id;
        }
        $getGameGroup = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('g_g_id', 7, '=')->where('active_flag', 1, '=')->where('game_id', 1, '=')->first();
        #foreach ($getGameGroups as $getGameGroup) {
            $g_g_name = $getGameGroup->g_g_name;
            $g_g_main_id = $getGameGroup->id;
            $game_id = $getGameGroup->game_id;
            $g_g_id = $getGameGroup->g_g_id;
            $url_event = $this->base_api_url ."getListParents";
            $data_array=array("token"=>$this->api_auth,"bf_sport_id"=>7,"locale"=> "UK");

            $data_json=json_encode($data_array);
            $this->response = get_url_j($url_event,$data_json,true);
            $this->json_data = json_decode($this->response[0]);
            $venues = $this->json_data;
            foreach($venues as $vk=>$venue)
            {
                $venue_name = $venue->bf_parent_name;
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
                }
                $bf_parent_id = $venue->bf_parent_id;
                $url_event = $this->base_api_url ."getListEvents";
                $data_array=array("token"=>$this->api_auth,"bf_parent_id"=>"$bf_parent_id","locale"=> "UK");
                $data_json=json_encode($data_array);
                $this->response = get_url_jb($url_event,$data_json,true);
                $this->json_data = json_decode($this->response[0]);
                if(isset($this->json_data->status)) {
                    continue;
                }
                foreach ($this->json_data as $ev => $events) {
                    $bf_event_id = $events->bf_event_id;
                    $url_event = $this->base_api_url ."getListMarkets";
                    $data_array=array("token"=>$this->api_auth,"bf_event_id"=>"$bf_event_id","locale"=> "UK");
                    $data_json=json_encode($data_array);
                    $this->response = get_url_jb($url_event,$data_json,true);
                    $this->json_data = json_decode($this->response[0]);
                    if(isset($this->json_data->status)) {
                        continue;
                    }
                    foreach($this->json_data as $mk=>$market) {
                        $bf_market_id = $market->bf_market_id;
                        $market_type = $market->market_type;
                        $real_start_time = (array)$market->real_start_time; //mili seconds
                        $real_start_time = $real_start_time['$date'] / 1000;
                        $event_name = $venue_name;
                        if($event_name == 'ANTEPOST'){
                            continue;
                        }
                        $start_time = date("Y-m-d H:i:s", $real_start_time);
                        #$settled = $market->status;
                        $event_id = $bf_market_id;

                        $other_array = array("market_type" => $market_type,
                        );
                        $other_data = json_encode($other_array);

                        $event_keyss = $event_name . '-' . $start_time . '-' . $venue_id;
                        $event_keyss = str_slug($event_keyss, '-');
                        if (array_key_exists($event_id, $event_keys)) {
                            $event_id = $event_keys[$event_id];
                        } else {
                            $event_keys[$event_id] = $event_id;
                            $event_info[] = array(
                                "event_name" => $event_name,
                                "start_date" => $start_time,
                                #"state" => $settled,
                                "venue_id" => $venue_id,
                                "event_id" => $event_id,
                                "other" => $other_data,
                                "g_g_id" => $g_g_id,
                                "game_id" => $game_id,
                                "api_id" => $this->api_id,
                                "date_stamp" => $today_stamp,
                                #"type_id" => 0,
                                #"bet_till_date" => 0,
                            );
                        }
                    }
                }
            }
        #}
        #var_dump($event_array);
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
