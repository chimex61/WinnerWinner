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

class getOutcomes extends Command {

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
	protected $name = 'winner:getOutcome';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get Outcomes Data From Sky Bet API, Uni Bet API, William Hills API, Bet Fred API, Bet Fair API, Coral API.';

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
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 1800);
    //    $today_stamp = strtotime("0:00:00");
    //    $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->delete();

        #$apiMaster = $this->apiMaster->where('id',6,'=')->first();
        $apiMasters = $this->apiMaster->get();
        foreach($apiMasters as $apiMaster) {
            $this->base_api_url = $apiMaster->base_url;
            $this->api_id = $apiMaster->id;
            $this->api_auth = $apiMaster->auth;
            $this->api_name = $apiMaster->name;
            $this->response_formate = "json";
            if($this->api_id == 1){ $this->SkyBetOutcomeData($this->api_id);}
            if($this->api_id == 2){ $this->UniBetOutcomeData($this->api_id);}
            if($this->api_id == 3){ $this->WilliamHillsOutcomeData($this->api_id);}
            if($this->api_id == 4){ $this->BetFredOutcomeData($this->api_id);}
            if($this->api_id == 6){ $this->CoralOutcomeData($this->api_id);}
        }
	}

    public function SkyBetOutcomeData($apiID)
    {
        #ini_set('memory_limit', '512M');
        #ini_set('max_execution_time', 300);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $url_outcome=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);
        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->count();
        #$this->info(dd($countOutcomeMasters));
        if($countOutcomeMasters !=0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        }
        $getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->where('game_id', 1, '=')->get();
        foreach ($getEventMasters as $getEventMaster) {
            $start_date = strtotime($getEventMaster->start_date);
            # if ($start_date > $current_time) {
            $event_id = $getEventMaster->event_id;
            $venue_id = $getEventMaster->venue_id;
            $venueMaster = $this->venueMaster->where('venue_id', $venue_id, '=')->first();
            $game_id_array[$event_id] = $venueMaster->game_id;
            $g_g_id_array[$event_id] = $venueMaster->g_g_id;
            $url_outcome[$event_id] = $this->base_api_url . "sportsapi/v2/event/".$event_id."?" . $this->api_auth;
            # }
        }
        $chunk_url_outcome = array_chunk($url_outcome,50,true);
#$this->info(count($chunk_url_outcome));
        foreach ($chunk_url_outcome  as $chunk_url) {
            $this->response = rolling_curl($chunk_url);
            foreach ($this->response['output'] as $rk => $response) {
                #$this->info($rk);
                if ($response != "") {
                    $event_id = $rk;
                    $game_id = $game_id_array[$event_id];
                    $g_g_id = $g_g_id_array[$event_id];
                    $this->json_data = json_decode($response);
                    if(!empty($this->json_data->markets)) {
                        $started = $this->json_data->started;
                        $markets = $this->json_data->markets;
                        $markets = (array)$markets;
                        foreach ($markets as $mk => $market) {
                            if ($market != '') {
                                $outcomes = $market->outcomes;
                                $bet_type = validate_string($market->name);
                                if (substr($bet_type, 0, 10) == "Under/Over" && $game_id == 'football')//if bet type starts with under
                                {
                                    $bet_type = "Total Goals Over/Under";
                                }
                                foreach ($outcomes as $ok => $outcome) {
                                    if ($outcome != '') {
                                        $label = validate_string($outcome->desc);
                                        $result = $outcome->result;
                                        $lp_decimal = $outcome->lp_decimal;
                                        $lp_disp_fraction = $outcome->lp_disp_fraction;
                                        $other_array = (array)$outcome;
                                        $other_data = validate_string(json_encode($other_array));
                                        $outcome_info[] = array(
                                            "label" => $label,
                                            "bet_type" => $bet_type,
                                            "odd" => $lp_decimal,
                                            "odd_fractional" => $lp_disp_fraction,
                                            "event_id" => $event_id,
                                            "add_date" => '',
                                            "other" => $other_data,
                                            "game_id" => $game_id,
                                            "g_g_id" => $g_g_id,
                                            "date_stamp" => $today_stamp,
                                            "api_id" => $this->api_id,
                                        );
                                        #  $sql = $this->outcomeMaster->create($outcome_info);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($outcome_info)) {
            $chunk_outcome = array_chunk($outcome_info,5000);
            foreach ($chunk_outcome  as $chunk_data) {
                #dd($chunk_data);
                DB::table('outcomemaster')->insert($chunk_data);
            }
        }
        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($outcome_info) . " SkyBet Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
        //unset($outcome_info);
    }

    public function UniBetOutcomeData($apiID)
    {
       # ini_set('memory_limit', '512M');
       # ini_set('max_execution_time', 300);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $outcome_url=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);
        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->count();
        if($countOutcomeMasters !=0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        }
        $getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->where('game_id', 1, '=')->get();
        foreach ($getEventMasters as $getEventMaster) {
            $start_date = strtotime($getEventMaster->start_date);
            if($start_date >= $today_stamp) {
                $event_id = $getEventMaster->event_id;
                $venue_id = $getEventMaster->venue_id;
                $game_id_array[$event_id] = $getEventMaster->game_id;
                $g_g_id_array[$event_id] = $getEventMaster->game_id;
                $outcome_url[$event_id] = $this->base_api_url . "sportsbook/betoffer/event/$event_id." . $this->response_formate . "?" . $this->api_auth;
            }
        }
        $chunk_url_outcome = array_chunk($outcome_url,30,true);

        foreach ($chunk_url_outcome  as $chunk_url) {
            $this->response = get_url_array($chunk_url);
            $type_array = array();
            foreach($this->response['output'] as $rk=>$response) {

                $this->json_data = json_decode($response);

                if (isset($this->json_data->error->status)) {
                    continue;
                }
                $game_id = $game_id_array[$rk];
                $g_g_id = $g_g_id_array[$rk];
                if(!empty($this->json_data->betoffers)){
                    $betoffers = $this->json_data->betoffers;

                    if (!is_array($betoffers)) {
                        // echo"<pre>";
                        // print_r($this->response);
                    } else {
                        foreach ($betoffers as $betoffers_key => $betoffer) {
                            $criterion_label = $betoffer->criterion->label;
                            $this->info($criterion_label);
                            $type_array [$criterion_label] = $rk;
                            $event_id = $betoffer->eventId;
                            if (!empty($betoffer->outcomes)) {
                                $outcomes = $betoffer->outcomes;
                                if (is_array($outcomes)) {
                                    foreach ($outcomes as $outcomes_key => $outcome) {

                                        if(isset($outcome->oddsFractional)) {
                                            $lp_disp_fraction = $outcome->oddsFractional;
                                            if ($lp_disp_fraction == "-") {
                                                $lp_disp_fraction = "SP";
                                            }
                                        }else{
                                            $lp_disp_fraction = "SP";
                                        }
                                        $label = validate_string($outcome->label);
                                        $bet_type = validate_string($criterion_label);
                                        $lp_decimal = $outcome->odds / 1000;
                                        $other_array = array();
                                        $other_data = validate_string(json_encode($other_array));

                                        $outcome_info[] = array(
                                            "label" => $label,
                                            "bet_type" => $bet_type,
                                            "odd" => $lp_decimal,
                                            "odd_fractional" => $lp_disp_fraction,
                                            "event_id" => $event_id,
                                            "add_date" => '',
                                            "other" => $other_data,
                                            "game_id" => $game_id,
                                            "g_g_id" => $g_g_id,
                                            "date_stamp" => $today_stamp,
                                            "api_id" => $this->api_id,
                                        );
                                        #$sql = $this->outcomeMaster->create($outcome_info);

                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // dd('stop');

        if (!empty($outcome_info)) {
            $chunk_outcome = array_chunk($outcome_info,5000);
            foreach ($chunk_outcome  as $chunk_data) {
                #dd($chunk_data);
                DB::table('outcomemaster')->insert($chunk_data);
            }
        }

        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($outcome_info) . " UniBet Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
        //unset($outcome_info);
    }


    public function WilliamHillsOutcomeData($apiID)
    {
       # ini_set('memory_limit', '512M');
      #  ini_set('max_execution_time', 300);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $url_outcome=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);
        $event_array = array();
        $venue_array = array();
        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->count();
        #$this->info(dd($countOutcomeMasters));
        if($countOutcomeMasters !=0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        }

        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            $event_key = str_slug($getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id,'-');
            $event_array[$event_key] = $getEvent->event_id;
        }
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->where('game_id', 1, '=')->get();
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
                $type_id = $venue['id'];
                $markets = $venue->market;
                foreach($markets as $market)
                {
                    $event_id = "". $market['id'];
                    $event_name = $market['name'];
                    $start_time = $market['date'] ." ". $market['time'];
                    $bet_till_date = $market['betTillDate'] . " " . $market['betTillTime'];
                    $settled ="";
                    $other_array=array(
                        "ewPlaces"=> $market['ewPlaces'],
                        "ewReduction"=> $market['ewReduction'],
                    );
                    $other_data =json_encode($other_array);
                    $participants =$market->participant;
                    foreach($participants as $pk=>$participant)
                    {
                        $id = "".$participant['id'];
                        $name = "".$participant['name'];
                        $lp_disp_fraction = "".$participant['odds'];
                        $lp_decimal ="". $participant['oddsDecimal'];
                        $handicap = "".$participant['handicap'];

                        $lastUpdateDate = "".$participant['lastUpdateDate'];
                        $lastUpdateTime = "".$participant['lastUpdateTime'];

                        $label = validate_string($name);
                        $bet_type = "win";
                        $other_array = array(
                            "handicap" => $handicap,
                            "lastUpdateDate" => $lastUpdateDate,
                            "lastUpdateTime" => $lastUpdateTime,
                        );
                        $other_data = validate_string(json_encode($other_array));

                        $outcome_info[] = array(
                            "label" => $label,
                            "bet_type" => $bet_type,
                            "odd" => $lp_decimal,
                            "odd_fractional" => $lp_disp_fraction,
                            "event_id" => $event_id,
                            "add_date" => '',
                            "other" => $other_data,
                            "game_id" => $game_id,
                            "g_g_id" => $g_g_id,
                            "date_stamp" => $today_stamp,
                            "api_id" => $this->api_id,
                        );
                        #$sql = $this->outcomeMaster->create($outcome_info);
                    }
                }
            }
        }

        if (!empty($outcome_info)) {
            $chunk_outcome = array_chunk($outcome_info,5000);
            foreach ($chunk_outcome  as $chunk_data) {
                #dd($chunk_data);
                DB::table('outcomemaster')->insert($chunk_data);
            }
        }

        $end_time = time();
        $duration = $end_time - $start_times;
        $this->info(count($outcome_info) . " WilliamHills Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
        //unset($outcome_info);
    }

    public function BetFredOutcomeData($apiID)
    {
      #  ini_set('memory_limit', '512M');
      #  ini_set('max_execution_time', 300);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $url_outcome=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);
        $venue_array = array();
        $event_array = array();
        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->count();
        #$this->info(dd($countOutcomeMasters));
        if($countOutcomeMasters !=0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        }
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
        $getGameGroups = $this->gameGroupMaster->where('api_id', $this->api_id, '=')->where('active_flag', 1, '=')->where('game_id', 1, '=')->get();
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
                            $id = "" . $participant['id'];
                            $name = "" . $participant['name'];
                            $odds = "" . $participant['price'];
                            $oddsDecimal = "" . $participant['priceDecimal'];
                            $jockey_silk = "" . $participant['jockey-silk'];
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
                            $outcome_info[] = array(
                                "label" => $name,
                                "bet_type" => $bettype,
                                "odd" => $oddsDecimal,
                                "odd_fractional" => $odds,
                                "event_id" => $event_id,
                                "add_date" => '',
                                "other" => $other_data,
                                "game_id" => $game_id,
                                "g_g_id" => $g_g_id,
                                "date_stamp" => $today_stamp,
                                "api_id" => $this->api_id,
                            );
                        }
                    }
                }
            }
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

    public function CoralOutcomeData($apiID)
    {
        #ini_set('memory_limit', '512M');
        #ini_set('max_execution_time', 600);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $url_outcome=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);

        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->count();
        #$this->info(dd($countOutcomeMasters));
        if($countOutcomeMasters !=0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        }

        $getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->where('game_id', 1, '=')->get();
        #$getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->where('game_id', 1, '=')->get();
        foreach ($getEventMasters as $getEventMaster) {
            $event_id = $getEventMaster->event_id;
            $venue_id = $getEventMaster->venue_id;

            $ev_start_date = strtotime($getEventMaster->start_date);
            $cur_time = strtotime($today_date_time);
            $getvenueMaster = $this->venueMaster->where('venue_id', $venue_id, '=')->first();
            #$game_id = $getvenueMaster->game_id;
            #$g_g_id = $getvenueMaster->g_g_id;
            $game_id_array[$event_id]=$getvenueMaster->game_id;
            $g_g_id_array[$event_id]=$getvenueMaster->g_g_id;
            if ($cur_time > $ev_start_date) {
                $url_outcome[] = $this->base_api_url . "oxi/pub?template=getResultsByEvent&returnRaceInfo=Y&event=" . $event_id;
            } else {
                $url_outcome[] = $this->base_api_url . "oxi/pub?template=getEventDetails&event=" . $event_id . "&returnRaceInfo=Y";

            }

            $chunk_url_outcome = array_chunk($url_outcome, 50, true);

            foreach ($chunk_url_outcome as $chunk_url) {
                $this->response = rolling_curl_batch($chunk_url);


                #$this->response = get_url($url_outcome, '', true);
                #$this->xml_data = simplexml_load_string($this->response[0]);
                #$markets = $this->xml_data->response->event->market;
                foreach ($this->response['output'] as $rk => $response) {
                    $this->xml_data = simplexml_load_string($response);
                    // debug($this->xml_data);
                    if (!is_array($this->xml_data)) {
                        // file_put_contents("temp_out/$rk".".txt",$response);
                        // continue;
                    }
                    // debug($this->xml_data->response);
                    $event = $this->xml_data->response->event;
                    $event_id = $event['id'] . "";
                    $game_id = $game_id_array[$event_id];
                    $g_g_id = $g_g_id_array[$event_id];
                    $markets = $event->market;


                    if ($this->xml_data->response['code'] == "001") {
                        foreach ($markets as $mk => $market) {

                            $bet_type = validate_string($market['name']);
                            foreach ($market->outcome as $ok => $outcome) {
                                $last_update_dt = strtotime($outcome['lastUpdateDate'] . ' ' . $outcome['lastUpdateTime']);
                                $last_update_ot = $outcome['id'] . '-' . $last_update_dt;
                                $label_id = validate_string($outcome['id']);
                                $label = validate_string($outcome['name']);
                                $label = validate_string($label);
                                $lp_decimal = validate_string($outcome['oddsDecimal']);
                                $lp_disp_fraction = validate_string($outcome['odds']);
                                $result = validate_string($outcome['result'] . "");
                                $resultType = validate_string($outcome['resultType'] . "");
                                $scoreHome = validate_string($outcome['scoreHome'] . "");
                                $scoreAway = validate_string($outcome['scoreAway'] . "");
                                $runnerNumber = $outcome['runnerNumber'];
                                $status = $outcome['status'];
                                $runner = $outcome->runner;
                                #$this->info($runner['age']);
                                $other_array = array(
                                    "coral_result" => $result,
                                    "coral_resultType" => $resultType,
                                    "coral_scoreHome" => $scoreHome,
                                    "coral_scoreAway" => $scoreAway,
                                    "coral_drawNumber" => $runner['drawNumber'] . "",
                                    "coral_formGuide" => $runner['formGuide'] . "",
                                    "coral_age" => $runner['age'] . "",
                                    "coral_jockey" => $runner['jockey'] . "",
                                    "coral_owner" => $runner['owner'] . "",
                                    "coral_trainer" => $runner['trainer'] . "",
                                    #"silk" => rtrim($runner->silk['id']."",'.gif'),
                                    "coral_silk" => $runner['silkId'] . "",
                                    "coral_runner" => $runner->overview[0] . "",
                                    "coral_sire" => $runner->overview[1] . "",
                                    "coral_runnerNumber" => $runnerNumber . "",
                                    "coral_status" => $status . "",
                                    "bet_type" => $bet_type . "",
                                );
                                $other_data = validate_string(json_encode($other_array));
                                $outcome_info[] = array(
                                    "label" => $label,
                                    "bet_type" => $bet_type,
                                    "odd" => $lp_decimal,
                                    "odd_fractional" => $lp_disp_fraction,
                                    "event_id" => $event_id,
                                    "add_date" => '',
                                    "other" => $other_data,
                                    "game_id" => $game_id,
                                    "g_g_id" => $g_g_id,
                                    "date_stamp" => $today_stamp,
                                    "api_id" => $this->api_id,
                                    "last_update_ot" => $last_update_ot,
                                );
                            }
                        }
                    }
                }
            }
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
        $this->info(count($outcome_info) . " Coral Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
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
