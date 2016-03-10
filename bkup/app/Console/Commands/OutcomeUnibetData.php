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

class OutcomeUnibetData extends Command {
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
	protected $name = 'winner:getOutcomeUniBetData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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
        //$this->CoralData($this->api_id);
        $this->UniBetOutcomeData($this->api_id);
    }

    public function UniBetOutcomeData($apiID)
    {
         #ini_set('memory_limit', '1024M');
        # ini_set('max_execution_time', 180000);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        $outcome_url=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        $outcome_id_array=array();
        $outcome_id_price = array();
        #$chunk_url_outcome = array();
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
        $getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->where('game_id', 1, '=')->get();
        foreach ($getEventMasters as $getEventMaster) {
            $start_date = strtotime($getEventMaster->start_date);
            if($start_date >= $today_stamp) {
                $event_id = $getEventMaster->event_id;
                $venue_id = $getEventMaster->venue_id;
                $game_id_array[$event_id] = $getEventMaster->game_id;
                $g_g_id_array[$event_id] = $getEventMaster->g_g_id;
                $outcome_url[$event_id] = $this->base_api_url . "sportsbook/betoffer/event/$event_id." . $this->response_formate . "?" . $this->api_auth;
            }
        }
        $chunk_url_outcome = array_chunk($outcome_url,50,true);

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
                            $type_array [$criterion_label] = $rk;
                            $event_id = $betoffer->eventId;

                            $this->info($criterion_label);
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

                                        $outcome_id = $outcome->id;

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
                    }
                }
            }
        }
        // dd('stop');

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
        $this->info(count($outcome_info) . " UniBet Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
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
