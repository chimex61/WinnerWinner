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

class OutcomeSkyBetData extends Command {

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
	protected $name = 'winner:getOutcomeSkyBetData';

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
        $apiMaster = $this->apiMaster->where('id',1,'=')->first();
        $this->base_api_url = $apiMaster->base_url;
        $this->api_id = $apiMaster->id;
        $this->api_auth = $apiMaster->auth;
        $this->api_name = $apiMaster->name;
        $this->response_formate = "json";
        //$this->CoralData($this->api_id);
        $this->SkyBetOutcomeData($this->api_id);
    }

    public function SkyBetOutcomeData($apiID)
    {
        #ini_set('memory_limit', '256M');
        #ini_set('max_execution_time', 18000);
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

#$this->info($ok);


                                        if (array_key_exists($ok, $outcome_id_price)) {
                                            $price_direction = $outcome_id_price[$ok];
                                          #  $this->info($price_direction);
                                        }else{
                                            $price_direction = $lp_decimal;
                                            #dd($price_direction);
                                        }



                                        $other_array = (array)$outcome;
                                        $other_data = validate_string(json_encode($other_array));

                                        if(isset($other_array['silk_id'])) {
                                            $path = base_path('assets/img/silk/');

                                            $get_silk_path = $path . $other_array['silk_id'];
                                            # var_dump($get_silk_path);
                                            if (File::exists($get_silk_path)) {
                                                //
                                              #  $this->info('exists');
                                            }else{


                                                $profile_Image = 'http://st1.skybet.com/bet/img/silks/' . $other_array['silk_id'] . '.GIF'; //image url

                                                if(curl_get_file_size($profile_Image) != -1) {
                                                    $userImage = $other_array['silk_id']; // renaming image
                                                    # $path = 'assets/img/silk/';  // your saving path
                                                    if (false !== ($contents = @file_get_contents($profile_Image))) {
                                                        $thumb_image = file_get_contents($profile_Image);
                                                        if ($http_response_header != NULL) {
                                                            $thumb_file = $path . $userImage;
                                                            #  var_dump($thumb_file);
                                                            file_put_contents($thumb_file, $thumb_image);
                                                        }
                                                        //
                                                    } else {
                                                        //
                                                    }

                                                }

                                            }

                                        }

                                        $outcome_info[] = array(
                                            "label" => $label,
                                            "bet_type" => $bet_type,
                                            "odd" => $lp_decimal,
                                            "odd_fractional" => $lp_disp_fraction,
                                            "event_id" => $event_id,
                                            #"add_date" => '',
                                            "other" => $other_data,
                                            "game_id" => $game_id,
                                            "g_g_id" => $g_g_id,
                                            "date_stamp" => $today_stamp,
                                            "api_id" => $this->api_id,
                                            "price_direction" => $price_direction,
                                            "outcome_id" => $ok,
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
        $this->info(count($outcome_info) . " SkyBet Outcomes Data retrieved in " . date('s', $duration) . " seconds\n");
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
