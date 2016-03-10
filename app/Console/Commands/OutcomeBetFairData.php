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
use Image;
use File;

class OutcomeBetFairData extends Command {
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
	protected $name = 'winner:getOutcomeBetFairData';

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
        $this->BetFairOutcomeData($this->api_id);
    }

    public function BetFairOutcomeData($apiID)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 180000);

        $this->api_id = $apiID;
        $today_stamp = strtotime("0:00:00");

        $start_times = time();
        $game_id_array=array();
        $g_g_id_array=array();
        $url_outcome =array();
        $url_selection = array();
        $data_json_outcome = array();
        $data_json_selection=array();
        $outcome_info = array();
        $bet_type_array = array();
        $outcome_id_price = array();

        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);
        $countOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->get();
        foreach($countOutcomeMasters as $countOutcomeMaster){
            #$outcome_id_array[$countOutcomeMaster->id] = $countOutcomeMaster->id;
            $outcome_id_price[$countOutcomeMaster->outcome_id] = $countOutcomeMaster->odd;
        }
        #$this->info(dd($countOutcomeMasters));
        # if(count($countOutcomeMasters) >0) {
        #    $deleteOutcomeMasters = $this->outcomeMaster->where('date_stamp', $today_stamp, '=')->where('api_id', $this->api_id, '=')->delete();
        #  }

        $getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->get();

       # dd('stop');

        foreach ($getEventMasters as $getEventMaster) {
            $event_id = $getEventMaster->event_id;
            $game_id_array[$event_id] = $getEventMaster->game_id;
            $g_g_id_array[$event_id] = $getEventMaster->g_g_id;

            //bettype
            $other = json_decode($getEventMaster->other);
           # dd($other);
            $bet_type_array[$event_id] = $other->market_type;
            //selection

            $url_selection[$event_id] = $this->base_api_url . "getListSelections";
            $data_array = array("token" => $this->api_auth, "bf_market_id" => "$event_id","locale"=> "UK");
            $data_json_selection[$event_id] = json_encode($data_array);
            //outcome
            $url_outcome[$event_id] = $this->base_api_url . "getListFixedOdds";
            $data_array = array("token" => $this->api_auth, "market_id" => "$event_id","locale"=> "UK");
            $data_json_outcome[$event_id] = json_encode($data_array);
        }



        $this->response['selection'] = get_url_array($url_selection, $data_json_selection, "json");
        $selection_names_array = array();
        foreach ($this->response['selection']['output'] as $rk => $response) {
            $this->json_data = json_decode($response);
            if (isset($this->json_data->status)) {
                continue;
            }
            foreach ($this->json_data as $sk => $selection) {
                $selection_id = $selection->bf_selection_id;
                $selection_name = $selection->selection_name;
                $selection_names_array[$selection_id] = $selection_name;
            }
        }

#dd($selection_names_array);
        $chunk_url_outcome = array_chunk($url_outcome,50,true);

        foreach ($chunk_url_outcome  as $chunk_url) {

            $this->response['outcome'] = get_url_array($chunk_url, $data_json_outcome, "json");
            #dd($this->response['outcome']['output']);
            foreach ($this->response['outcome']['output'] as $rk => $response) {
                #dd($response->status);
                $event_id = $rk;
                $game_id = $game_id_array[$rk];
                $g_g_id = $g_g_id_array[$rk];
                $bet_type = validate_string($bet_type_array[$rk]);
                $this->json_data = json_decode($response);
                #dd($this->json_data->status);
                if ($this->json_data->status=='400') {
                    continue;
                }
                // debug($this->json_data);
#dd($this->json_data->selections);
                foreach ($this->json_data->selections as $ok => $outcome) {
                    #dd($outcome->bf_selection_id);
                    if (array_key_exists($outcome->bf_selection_id, $selection_names_array)) {
                        // debug($outcome);
                        $label_id = $outcome->bf_selection_id;
#$this->info($label_id);
                        $label = $selection_names_array[$label_id];
                        $label = validate_string($label);
                        $lp_decimal = ($outcome->denominator != 0) ? $outcome->numerator / $outcome->denominator : 0;
                        if ($outcome->numerator == "") {
                            $lp_disp_fraction = "SP";
                        } else {
                            $lp_disp_fraction = $outcome->numerator . "/" . $outcome->denominator;
                        }
                        // $other_array = (array) $outcome;


                        if (array_key_exists($label_id, $outcome_id_price)) {
                            $price_direction = $outcome_id_price[$label_id];
                            #  $this->info($price_direction);
                        }else{
                            $price_direction = $lp_decimal;
                            #dd($price_direction);
                        }

                        /*
                        $other_array = array();
                        $other_data = validate_string(json_encode($other_array));
                        */
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
                            "outcome_id" => $label_id,
                        );
                        # $sql = $this->outcomeMaster->create($outcome_info);
                        # $this->info($sql);
                    }

                }

                #

            }
        #}
        }
        #dd('stop');
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
        $this->info(count($outcome_info) . " Betfair Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
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
