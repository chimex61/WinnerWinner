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

class OutcomeWilliamHillsData extends Command {
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
	protected $name = 'winner:getOutcomeWilliamHillsData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
    protected $description = 'Get Data from William Hills Api.';

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
        $apiMaster = $this->apiMaster->where('id',3,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        $this->WilliamHillsOutcomeData($this->api_id);
    }

    public function WilliamHillsOutcomeData($apiID)
    {
        # ini_set('memory_limit', '256M');
         # ini_set('max_execution_time', 18000);
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
        $event_array = array();
        $venue_array = array();

        $getVenues = $this->venueMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getVenues as $getVenue) {
            $venue_array[$getVenue->venue_name] = $getVenue->venue_id;
        }
        $getEvents = $this->eventMaster->where('api_id', $this->api_id, '=')->get();
        foreach ($getEvents as $getEvent) {
            $event_key = str_slug($getEvent->event_name.'-'.$getEvent->start_date.'-'.$getEvent->venue_id,'-');
            $event_array[$event_key] = $getEvent->event_id;
        }

        #$ev_start_date = strtotime($getEventMaster->start_date);


        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->get();
        foreach($countOutcomeMasters as $countOutcomeMaster){
            #$outcome_id_array[$countOutcomeMaster->id] = $countOutcomeMaster->id;
            $outcome_id_price[$countOutcomeMaster->outcome_id] = $countOutcomeMaster->odd;
        }
        #$this->info(dd($countOutcomeMasters));
        # if(count($countOutcomeMasters) >0) {
        #    $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        #  }


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
                $type_id = $venue['id'];
                $markets = $venue->market;
                foreach($markets as $market)
                {
                    $event_id = "". $market['id'];
                    $event_name = $market['name'];
                    /*
                    $start_time = $market['date'] ." ". $market['time'];
                    $bet_till_date = $market['betTillDate'] . " " . $market['betTillTime'];
                    $settled ="";
                    $other_array=array(
                        "ewPlaces"=> $market['ewPlaces'],
                        "ewReduction"=> $market['ewReduction'],
                    );
                    $other_data =json_encode($other_array);
                    */
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
                        $bet_typo2 = str_contains($event_name, 'Place Only - 2 Places');
                        $bet_typo3 = str_contains($event_name, 'Place Only - 3 Places');
                        $bet_typo4 = str_contains($event_name, 'Insure - 2 Places');
                        $bet_typo5 = str_contains($event_name, 'Insure - 3 Places');
                        $bet_typo = str_contains($event_name, 'Win');
                        if($bet_typo==true){
                            $bet_type = 'Win';
                        }elseif($bet_typo2==true){
                            $bet_type = 'Place Only - 2 Places';
                        }elseif($bet_typo3==true){
                            $bet_type = 'Place Only - 3 Places';
                        }elseif($bet_typo4==true){
                            $bet_type = 'Insure - 2 Places';
                        }elseif($bet_typo5==true){
                            $bet_type = 'Insure - 3 Places';
                        }else{
                            $bet_type = $event_name;
                        }


                        $outcome_id = $id;

                        if (array_key_exists($outcome_id, $outcome_id_price)) {
                            $price_direction = $outcome_id_price[$outcome_id];
                            #dd($price_direction);
                        }else{
                            $price_direction = $lp_decimal;
                            #dd($price_direction);
                        }

                        /*
                        $other_array = array();
                        $other_data = validate_string(json_encode($other_array));
                        */
                        //$this->info($label);
                        $outcome_info[] = array(
                            "label" => $label,
                            "bet_type" => $bet_type,
                            "odd" => $lp_decimal,
                            "odd_fractional" => $lp_disp_fraction,
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
                        #$sql = $this->outcomeMaster->create($outcome_info);
                    }
                }
            }
        }

        if(count($countOutcomeMasters) >0) {
            $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
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
