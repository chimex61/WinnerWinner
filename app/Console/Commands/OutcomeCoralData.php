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

class OutcomeCoralData extends Command {

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
	protected $name = 'winner:getOutcomeCoralData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get Outcome Data From Coral';

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
        //$this->CoralData($this->api_id);
         $this->CoralOutcomeData($this->api_id);
    }

    public function CoralOutcomeData($apiID)
    {
        ini_set('memory_limit', '1500M');
        ini_set('max_execution_time', 180000);
        $this->api_id = $apiID;
        $start_times = time();
        $today_stamp = strtotime("0:00:00");
        # $url_outcome=array();
        $game_id_array = array();
        $g_g_id_array = array();
        $outcome_info = array();
        $outcome_id_array=array();
        $outcome_id_price = array();
        #$chunk_url_outcome = array();
        $today_date_time = date("Y-m-d H:i:s");
        $current_time = strtotime($today_date_time);

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

        #$getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->get();
        $getEventMasters = $this->eventMaster->where('api_id', $this->api_id, '=')->where('date_stamp', $today_stamp, '=')->where('game_id', 1, '=')->get();
        foreach ($getEventMasters as $getEventMaster) {
            $event_id = $getEventMaster->event_id;
            $venue_id = $getEventMaster->venue_id;
            $ev_start_date = strtotime($getEventMaster->start_date);
            $cur_time = strtotime($today_date_time);
            $getvenueMaster = $this->venueMaster->where('venue_id', $venue_id, '=')->first();
            $game_id = $getvenueMaster->game_id;
            $g_g_id = $getvenueMaster->g_g_id;

            if($cur_time > $ev_start_date){
                $url_outcome = $this->base_api_url . "oxi/pub?template=getResultsByEvent&returnRaceInfo=Y&event=".$event_id;
            }else {
                $url_outcome = $this->base_api_url . "oxi/pub?template=getEventDetails&event=" . $event_id . "&returnRaceInfo=Y";

            }
            $this->response = get_url($url_outcome,'',true);
            $this->xml_data = simplexml_load_string($this->response[0]);
if($this->xml_data->response['code']!="001"){

}else {
    $markets = $this->xml_data->response->event->market;
}
            if($this->xml_data->response['code']=="001")
            {
                foreach($markets as $mk=>$market)
                {

                    $bet_type=validate_string($market['name']);
                    #$this->info($bet_type);
                    foreach($market->outcome as $ok=>$outcome)
                    {
                        $last_update_dt = strtotime($outcome['lastUpdateDate'].' '.$outcome['lastUpdateTime']);
                        $last_update_ot = $outcome['id'] . '-' . $last_update_dt;
                        $label_id=validate_string($outcome['id']);
                        $label=validate_string($outcome['name']);
                        $label = validate_string($label);
                        $lp_decimal =  validate_string($outcome['oddsDecimal']);
                        $lp_disp_fraction = validate_string($outcome['odds']);
                        $result =validate_string($outcome['result'] ."");
                        $resultType = validate_string($outcome['resultType']."");
                        $scoreHome =validate_string($outcome['scoreHome']."");
                        $scoreAway =validate_string($outcome['scoreAway']."");
                        $runnerNumber = $outcome['runnerNumber'];
                        $status = $outcome['status'];
                        $runner = $outcome->runner;
                        #$this->info($runner['age']);

                        $outcome_id = $ok;

                        if (array_key_exists($outcome_id, $outcome_id_price)) {
                            $price_direction = $outcome_id_price[$outcome_id];
                            #dd($price_direction);
                        }else{
                            $price_direction = $lp_decimal;
                            #dd($price_direction);
                        }

                        $other_array = array(
                            "coral_result" => $result,
                            "coral_resultType" => $resultType,
                            "coral_scoreHome" => $scoreHome,
                            "coral_scoreAway" => $scoreAway,
                            "coral_drawNumber" => $runner['drawNumber']."",
                            "coral_formGuide" => $runner['formGuide']."",
                            "coral_age" => $runner['age']."",
                            "coral_jockey" => $runner['jockey']."",
                            "coral_owner" => $runner['owner']."",
                            "coral_trainer" => $runner['trainer']."",
                            #"silk" => rtrim($runner->silk['id']."",'.gif'),
                            "coral_silk" => $runner['silkId']."",
                            "coral_runner" => $runner->overview[0]."",
                            "coral_sire" => $runner->overview[1]."",
                            "coral_runnerNumber" => $runnerNumber."",
                            "coral_status" => $status."",
                            "bet_type" => $bet_type."",
                        );
                        $other_data = validate_string(json_encode($other_array));
                        $outcome_info[] = array(
                            "label"=>$label,
                            "bet_type"=>$bet_type,
                            "odd"=>$lp_decimal,
                            "odd_fractional"=>$lp_disp_fraction,
                            "event_id"=>$event_id,
                            #"add_date"=>'',
                            "other"=>$other_data,
                            "game_id"=>$game_id,
                            "g_g_id"=>$g_g_id,
                            "date_stamp"=>$today_stamp,
                            "api_id"=>$this->api_id,
                            #"last_update_ot"=>$last_update_ot,
                            "price_direction" => $price_direction,
                            "outcome_id" => $outcome_id,
                        );
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
