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

class OutcomeBetFredData extends Command {

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
	protected $name = 'winner:getOutcomeBetFredData';

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
        $this->BetFredOutcomeData($this->api_id);
    }

    public function BetFredOutcomeData($apiID)
    {
          ini_set('memory_limit', '1024M');
          ini_set('max_execution_time', 180000);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $url_outcome=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        $outcome_id_array=array();
        $outcome_id_price = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);
        $venue_array = array();
        $event_array = array();
        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->get();
        #$this->info(dd($countOutcomeMasters));
        foreach($countOutcomeMasters as $countOutcomeMaster){
            #$outcome_id_array[$countOutcomeMaster->id] = $countOutcomeMaster->id;
            $outcome_id_price[$countOutcomeMaster->outcome_id] = $countOutcomeMaster->odd;
        }
        #$this->info(dd($countOutcomeMasters));
        # if(count($countOutcomeMasters) >0) {
        #    $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        #  }
        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
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
                    $markets = $venue->bettype;
                    $bettype = $markets['name'] . "";

                    $eachway = $markets['eachway'] . "";
                    $suspended = $markets['suspended'] . "";
                    $betstartdate = $markets['bet-start-date'] . "";
                    $betstarttime = $markets['bet-start-time'] . "";

                    # $this->info(dd($markets));
                    foreach ($markets as $market) {
                        //add outcomes to array
                        $participants = $market->bet;
                        foreach ($participants as $pk => $participant) {
                            //$this->info($participant['name']);
                            $id = "" . $participant['id'];
                            $name = "" . $participant['name'];
                            $odds = "" . $participant['price'];
                            $oddsDecimal = "" . $participant['priceDecimal'];
                            $jockey_silk = "" . $participant['jockey-silk'];



                            $outcome_id = $id;

                            if (array_key_exists($outcome_id, $outcome_id_price)) {
                                $price_direction = $outcome_id_price[$outcome_id];
                                #dd($price_direction);
                            }else{
                                $price_direction = $oddsDecimal;
                                #dd($price_direction);
                            }


                            /*
                            $other_array = array(
                                "betfred_had_value" => $participant['had-value'],
                                "betfred_name" => $participant['name'],
                                "betfred_short_name" => $participant['short-name'],
                                "betfred_active_price_types" => $participant['active-price-types'],
                                "betfred_jockey_silk" => $participant['jockey_silk'],
                                "betfred_price" => $participant['price'],
                                "betfred_priceDecimal" => $participant['priceDecimal'],
                                "betfred_priceUS" => $participant['priceUS'],
                                "betfred_eachway" => $eachway,
                                "betfred_suspended" => $suspended,
                                "betfred_betstartdate" => $betstartdate,
                                "betfred_betstarttime" => $betstarttime,
                            );
                            $other_data = validate_string(json_encode($other_array));

                            */
                            $outcome_info[] = array(
                                "label" => $name,
                                "bet_type" => $bettype,
                                "odd" => $oddsDecimal,
                                "odd_fractional" => $odds,
                                "event_id" => $event_id,
                                #"add_date" => '',
                                #"other" => $other_data,
                                "game_id" => $game_id,
                                "g_g_id" => $g_g_id,
                                "date_stamp" => $today_stamp,
                                "api_id" => $this->api_id,
                                "price_direction" => $price_direction,
                                "outcome_id" => $outcome_id,
                            );
                        }
                    }
                }
            }
        }

        if(count($countOutcomeMasters) >0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        }

        if (!empty($outcome_info)) {
            $chunk_outcome = array_chunk($outcome_info, 5000);
            foreach ($chunk_outcome as $chunk_data) {
                #dd($chunk_data);
                DB::table('outcomemaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($outcome_info) . " BetFred Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
        //unset($outcome_info);
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
