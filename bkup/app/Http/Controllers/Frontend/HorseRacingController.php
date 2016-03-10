<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ApiMaster;
use App\Models\BettingCount;
use App\Models\BetType;
use App\Models\EventMaster;
use App\Models\GameGroupMaster;
use App\Models\OutcomeMaster;
use App\Models\VenueMaster;
use App\Winner\Repositories\Contracts\GameMasterInterface as GameMasters;
use App\Winner\Repositories\Contracts\GameGroupMasterInterface as GameGroupMasters;
use App\Winner\Repositories\Contracts\OutcomeMasterInterface as OutcomeMasters;
use App\Winner\Repositories\Contracts\VenueMasterInterface as VenueMasters;
use App\Winner\Repositories\Contracts\EventMasterInterface as EventMasters;
use App\Winner\Repositories\Contracts\BettingCountInterface as BettingCounts;
use App\Http\Requests\HorseGetOddFormRequest;
use App\Http\Requests\HorseGetEventFormRequest;
use App\Http\Requests\HorseGetBetClickRequest;
use Image;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
#use Carbon\Carbon;
use DB;

class HorseRacingController extends Controller
{

    private $gameMaster;
    private $gameGroupMaster;
    private $outcomeMaster;
    private $venueMaster;
    private $eventMaster;
    private $bettingCount;

    public function __construct(GameMasters $gameMaster, GameGroupMasters $gameGroupMaster, OutcomeMasters $outcomeMaster, VenueMasters $venueMaster, EventMasters $eventMaster, BettingCounts $bettingCount)
    {
        //$this->middleware('auth');
        $this->gameMaster = $gameMaster;
        $this->gameGroupMaster = $gameGroupMaster;
        $this->outcomeMaster = $outcomeMaster;
        $this->venueMaster = $venueMaster;
        $this->eventMaster = $eventMaster;
        $this->bettingCount = $bettingCount;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $today_stamp = strtotime("0:00:00");
        $today_date = date("Y-m-d");
        #$api_ids = '';
        #$venueLists = array();
        Session::forget('changed_odd_type');

        $events = array();
        $event_start = array();
        $getEventStartTimes =EventMaster::
           select('eventmaster.event_id','eventmaster.start_date','venuemaster.venue_name','venuemaster.venue_id')
           #->join('apimaster','eventmaster.api_id','=','apimaster.id')
           #->join('gamemaster','eventmaster.game_id','=','gamemaster.id')
           #->join('gamegroupmaster','eventmaster.g_g_id','=','gamegroupmaster.g_g_id')
           ->join('venuemaster','eventmaster.venue_id','=','venuemaster.venue_id')
           ->where('eventmaster.api_id','=',1)
           ->where('eventmaster.game_id','=',1)
           ->where('eventmaster.g_g_id','=','horse-racing')
           ->where('eventmaster.date_stamp','=',$today_stamp)
           ->where('eventmaster.start_date','LIKE',$today_date.'%')
           #->orderby('eventmaster.start_date','asc')
           ->orderby('venuemaster.venue_name','asc')
           ->get();
        foreach($getEventStartTimes as $getEventStartTime){
            $events[$getEventStartTime->venue_id.'|'.$getEventStartTime->event_id] = $getEventStartTime->venue_name.'|'.$getEventStartTime->start_date;
        }
        asort($events);
        foreach($events as $key => $val) {
            $event_start[$key]=$val;
            #dd(explode("|", $key)[0]);
        }
        #dd($event_start);

        $gameMasters=$this->gameMaster->where('active',1,'=')->get();

        /*
        $apiMasters = ApiMaster::select('id')->orderby('id','asc')->get();
        foreach($apiMasters as $apiMaster){
            $api_ids .= ','.$apiMaster->id;
        }
        $api_ids = ltrim($api_ids,",");
        */
        $bet_types=OutcomeMaster::distinct()
            ->select('bet_type')
            ->join('eventmaster','outcomemaster.event_id','=','eventmaster.event_id')
            ->where('outcomemaster.game_id','=',1)
            ->where('outcomemaster.date_stamp','>=',$today_stamp)
            ->where('eventmaster.start_date','LIKE',$today_date.'%')
            ->wherein('outcomemaster.api_id',[1,2,3,4,6])
            ->orderby('outcomemaster.api_id','asc')
            ->get();

        /*
        $skyBetGameGroup =GameGroupMaster::select('g_g_id')->where('game_id','=',1)->where('api_id','=',1)->first();

        $horseVenueLists = DB::table('venuemaster')
            ->select('venuemaster.venue_name')
            ->join('gamegroupmaster','gamegroupmaster.g_g_id','=','venuemaster.g_g_id')
            ->where('venuemaster.game_id', '=', 1)
            ->where('venuemaster.api_id', '=', 6)
            ->get();

        foreach($horseVenueLists as $horseVenueList){
          $venueLists[$horseVenueList->venue_name] = $horseVenueList->venue_name;
        }

        */
        #$horseVenueMasters = VenueMaster::where('g_g_id','=',$skyBetGameGroup->g_g_id)->orderby('venue_name','asc')->get();

        #return view('frontend.horse.index', compact('gameMasters','bet_types','horseVenueMasters','api_ids','venueLists'));
        return view('frontend.horse.index', compact('gameMasters','bet_types','event_start'));
    }

    public function getOdd(HorseGetOddFormRequest $request)
    {
        #$horseEvents = '';
        #$event_id_cond="";
        $get_odd_output="";
        #$gameBetTypeTo = '';
        #$today_date = date("Y-m-d");
        #$today_date_time = date("Y-m-d H:i:s");
        $today_stamp = strtotime("0:00:00");
        $other_datas = array();
        $tr_class ='';

        $venue_name = urldecode($request->venue_name);
        $event_date = $request->event_date;
        $event_id = $request->event_id;
        $table_type = $request->table_type;

        (Session::get('changed_odd_type')== '')?$odd_type = 'Win & Each Way':$odd_type =  urldecode(Session::get('changed_odd_type'));

        #$getAPIID =EventMaster::where('game_id','=',1)->where('event_id','=',$event_id)->first();

        $venue_name_id ="table".$event_id;
$get_odd_output .= <<<EOT
<table class='table table-striped BetTable table-hover table-bordered' cellpadding='0' cellspacing='0' border='0' id='{$venue_name_id}'>
    <thead>
        <tr class='SortingPart'>
        <th>#</th>
        <th class='no-sort'>Silk</th>
        <th>Jockey</th>
        <th>Horse</th>
EOT;
        if($table_type!==" red"){
            $get_odd_output .= "<th>Odds</th><th class='no-sort'>Bet Count</th><th class='no-sort'>All odds</th>";
        }
        else {

            $get_odd_output .= "<th class='no-sort'>Bet Count</th>";
            $get_odd_output .= "<th>Result</th>";
        }
        $get_odd_output .= "</tr></thead><tbody>";

        if($odd_type != 'Win & Each Way'){
            $horseOutcomeMasters = DB::table('outcomemaster')
                ->select('outcomemaster.label','outcomemaster.odd','outcomemaster.odd_fractional','outcomemaster.other','outcomemaster.api_id','outcomemaster.date_stamp','outcomemaster.event_id','outcomemaster.bet_type','outcomemaster.price_direction')
                ->join('eventmaster','eventmaster.event_id','=','outcomemaster.event_id')
                ->join('venuemaster','eventmaster.venue_id','=','venuemaster.venue_id')
                ->where('outcomemaster.game_id', '=', 1)
                ->where('outcomemaster.date_stamp','=',$today_stamp)
                ->where('eventmaster.event_id','=',$event_id)
                ->where('eventmaster.start_date','=',$event_date)
                ->where('venuemaster.venue_name','=',$venue_name)
                ->where('outcomemaster.bet_type','LIKE',$odd_type)
                ->orderby('outcomemaster.odd','desc')
                ->get();


        }else{

            $horseOutcomeMasters = DB::table('outcomemaster')
                ->select('outcomemaster.label','outcomemaster.other','outcomemaster.odd','outcomemaster.odd_fractional','outcomemaster.api_id','outcomemaster.date_stamp','outcomemaster.event_id','outcomemaster.bet_type','outcomemaster.price_direction','outcomemaster.outcome_id')
                ->join('eventmaster','eventmaster.event_id','=','outcomemaster.event_id')
                ->join('venuemaster','eventmaster.venue_id','=','venuemaster.venue_id')
                ->where('eventmaster.start_date','=',$event_date)
                ->where('venuemaster.venue_name','=',$venue_name)
                ->where('outcomemaster.date_stamp','=',$today_stamp)
                ->where('outcomemaster.game_id', '=', 1)
                ->wherein('outcomemaster.bet_type',['Win & Each Way','Win or Each Way','To Win','Win','Outright Betting'])
		        #->groupby('outcomemaster.label')
                ->orderby('outcomemaster.label','asc')
                ->get();

            $horseOutcomes = array();
            $outcomes = array();

            foreach($horseOutcomeMasters as $horseOutcomeMaster){
                $horse_name = str_replace("'","",$horseOutcomeMaster->label);
                $horseOutcomes[$horse_name.'|'.$horseOutcomeMaster->outcome_id.'|'.$horseOutcomeMaster->bet_type.'|'.$horseOutcomeMaster->odd.'|'.$horseOutcomeMaster->odd_fractional.'|'.$horseOutcomeMaster->event_id.'|'.$horseOutcomeMaster->api_id .'|'.$horseOutcomeMaster->price_direction] = $horseOutcomeMaster->other;
            }


            $horse_nam0='';
            $horse_nam1='';

            #ksort($horseOutcomes);
            #dd($horseOutcomes);
            foreach($horseOutcomes as $key => $val) {

               # $outcomes[$key]=$val;
               # dd(explode("|", $val)[1]);
                $horse_nam0 = explode("|", $key)[0];

                $matches = array();
                $outcome_list = array();

                if ($horse_nam0 == "UNNAMED FAVOURITE" || $horse_nam0 == "UNNAMED 2nd FAVOURITE" || $horse_nam0 == "2nd Favourite" || $horse_nam0 == "MultiPosition") {
                    //
                }else {

                    if ($horse_nam0 != $horse_nam1) {
                        $i=0;
                        $j=0;
                        $new_val="";
                        $l = 1;
                        foreach ($horseOutcomes as $k => $v) {


                                if (preg_match("/\b$horse_nam0\b/i", $k)) {



                                    $i = explode("|", $k)[3];
                                    if ($i > $j) {
                                        $new_val = $k;
                                        $j = $i;
                                    }
                                    $bet_val = explode("|", $k)[2];
                                    if ($bet_val == 'Win & Each Way' || $bet_val == 'Win or Each Way') {
                                        $data = (array)json_decode(explode("|", $v)[0]);
                                        $other_datas = array_merge($other_datas, $data);
                                    }

                                }


                        }
                        if($new_val !='') {
                            $matches[$new_val] = $other_datas;
                        }
                        $horse_nam1 = $horse_nam0;
                    }
                }

/*

if(empty($matches)){}else {
    echo '<pre>';

    var_dump($matches);

    echo '</pre>';
}

                */





                if(empty($matches)){}else {


                    foreach($matches as $outcome_key => $outcome_val) {

                       # dd($matches);
                        $price_directions = 0;
                        $evntid = explode("|", $outcome_key)[5];
                        $evnt_oddf = explode("|", $outcome_key)[4];
                        $evnt_api = explode("|", $outcome_key)[6];
                        $evnt_lbl = str_slug(explode("|", $outcome_key)[0]);
                        $evnt_od = explode("|", $outcome_key)[2];
                        $price_directions = $evnt_od - explode("|", $outcome_key)[7];

                        $horseBetClick = BettingCount::
                        where('event_id', '=', $event_id)
                            ->where('label', '=', explode("|", $outcome_key)[0])
                            ->where('bet_type', 'LIKE', $odd_type)
                            ->where('date_stamp', '=', $today_stamp)
                            ->count();

                        ($horseBetClick != 0) ? $bet_count = $horseBetClick : $bet_count = 0;

                        $other_data = $outcome_val;
#dd($other_data);
                        (isset($other_data['silk_id'])) ? $silk = asset('assets/img/silk/' . $other_data['silk_id']) : $silk = asset('assets/img/generic_silk.gif');
                        (isset($other_data['suspended'])) ? $suspended = $other_data['suspended'] : $suspended = '';

                        if (isset($other_data['result'])) {
                            if ($other_data['result'] == "W" || $other_data['result'] == "w") {
                                $result_output = "Win";
                            } elseif ($other_data['result'] == "L") {
                                $result_output = "Lost";
                            } elseif ($other_data['result'] == "P") {
                                $result_output = "Placed";
                            } elseif ($other_data['result'] == "") {
                                $result_output = "Lost";
                            }
                        } else {
                            $result_output = "Lost";
                        }


                        if ($evnt_api == 1 and isset($other_data['price_direction'])) {
                            $price_direction = $other_data['price_direction'];
                            $arrow_direction = ($price_direction != -1) ? "up" : "down";
                            if ($price_direction == -1) {
                                $arrow_direction = "fa-long-arrow-down downArrow";
                            } elseif ($price_direction == 1) {
                                $arrow_direction = "fa-long-arrow-up UpArrow";
                            } else {
                                $arrow_direction = "";
                            }
                        } else {

                            if ($price_directions > 0) {
                                $arrow_direction = "fa-long-arrow-up UpArrow";
                            } elseif ($price_directions < 0) {
                                $arrow_direction = "fa-long-arrow-down downArrow";
                            } else {
                                $arrow_direction = "";
                            }

                        }

                        if ($suspended && $table_type != " red") {
                            $tr_class = "red";
                        }
                        $get_odd_output .= "<tr class='$tr_class'>";
                        if (isset($other_data['cloth_num'])) {
                            $cloth_num = $other_data['cloth_num'];
                        } else {
                            $cloth_num = 0;
                        }
                        $get_odd_output .= "<td>" . $cloth_num . "</td>";
                        $get_odd_output .= "<td><img src='" . $silk . "' alt=''></td>";
                        if (isset($other_data['jockey'])) {
                            $jockey = $other_data['jockey'];
                        } elseif (isset($other_data['coral_jockey'])) {
                            $jockey = $other_data['coral_jockey'];
                        } else {
                            $jockey = '';
                        }

                        if (isset($other_data['desc'])) {
                            $desc = $other_data['desc'];
                        } elseif (isset($other_data['coral_horse'])) {
                            $desc = explode("|", $outcome_key)[0];
                        } else {
                            $desc = '';
                        }


                        if (isset($other_data['coral_owner'])) {
                            $owner = $other_data['coral_owner'];
                        } else {
                            $owner = '';
                        }

                        if (isset($other_data['trainer'])) {
                            $trainer = $other_data['trainer'];
                        } elseif (isset($other_data['coral_trainer'])) {
                            $trainer = $other_data['coral_trainer'];
                        } else {
                            $trainer = '';
                        }

                        if (isset($other_data['coral_age'])) {
                            $age = $other_data['coral_age'];
                        } else {
                            $age = '';
                        }

                        if (isset($other_data['coral_formGuide'])) {
                            $form = $other_data['coral_formGuide'];
                        } else {
                            $form = '';
                        }


                        #$get_odd_output .= "<td><a href='/horse-racing/full-odds/".urlencode($venue_name)."/".urlencode($event_id)."/".urlencode(strtotime($event_date))."/".urlencode($horseOutcomeMaster->label)."'>". $jockey."</a></td>";
                        #$get_odd_output .="<td><a href='/horse-racing/full-odds/".urlencode($venue_name)."/".urlencode($event_id)."/".urlencode(strtotime($event_date))."/".urlencode($horseOutcomeMaster->label)."'>". $other_data['desc'] ."</a></td>";
                        $get_odd_output .= "<td>" . $jockey . "</td>";
                        $get_odd_output .= "<td><span id=\"tooltip-top\" data-toggle=\"tooltip\" data-placement=\"top\" title = \"Owner: {$owner} Trainer: {$trainer} Age: {$age} Form: {$form} \">" . $desc . "</span></td>";
                        if ($evnt_oddf == 'SP') {
                            $decimal_val = 'SP';
                        } else {

                            $new_val = 0;
                            $parts = explode(".", $evnt_od);

                            if (strlen($parts[0]) == 1) {
                                $new_val = '00' . $evnt_od;
                            } elseif (strlen($parts[0]) == 2) {
                                $new_val = '0' . $evnt_od;
                            }

                            #  $decimal_val =   number_format($new_val, 2, '.', '');
                            $decimal_val = $new_val;
                        }
                        if ($table_type !== " red") {
                            #dd('stop');
                            $get_odd_output .= "<td  onclick=\"get_bet_count({explode('|', $outcome_key)[5]},'{$venue_name}','{explode('|', $outcome_key)[0]}','{$odd_type}');\">
                   <span class='Odds odds_decimal'>$decimal_val</span>
<span class='Odds odds_fraction'>$evnt_oddf</span>
<i class='$arrow_direction  fa'></i>
<img class='Bookmaker' src='/assets/images/api_logo$evnt_api.svg'>

                    </td>";


                            $get_odd_output .= "<td><span id='total_bet_count{$evnt_lbl}'>{$bet_count}</span></td>";
                            $get_odd_output .= "<td><a class='btn btn-success' href='/horse-racing/full-odds/" . urlencode(explode("|", $outcome_key)[6]) . "/" . urlencode($venue_name) . "/" . urlencode($event_id) . "/" . urlencode(strtotime($event_date)) . "/" . urlencode(explode("|", $outcome_key)[0]) . "'>ALL ODDS</td>";


                        } else {
                            $get_odd_output .= "<td class=''>{$bet_count}</td>";
                            $get_odd_output .= "<td class=''>{$result_output}</td>";
                        }
                        $get_odd_output .= "</tr>";


                    }


                }






            }

        }

        $get_odd_output .= "</tbody></table>";
        echo $get_odd_output;


/*

         dd('stop');


        $new_lbl = '';
        $new_lbl1 = '';

        foreach($horseOutcomeMasters as $horseOutcomeMaster) {
            $result_output = '';
            $bet_count = '';
            $arrow_direction = "";
            $tr_class = "";


            $label = str_replace(" N/R", "", $horseOutcomeMaster->label);

            $new_lbl = $label;

             if($new_lbl == $new_lbl1){

             //
             }else {

                 if ($odd_type == 'Win & Each Way') {


                     $label = str_replace(" N/R", "", $horseOutcomeMaster->label);
                     $horseOutcomeOtherData = DB::table('outcomemaster')
                         ->select('outcomemaster.other')
                         ->where('outcomemaster.date_stamp', '=', $today_stamp)
                         ->where('outcomemaster.game_id', '=', 1)
                         ->wherein('outcomemaster.bet_type', ['Win & Each Way', 'Win or Each Way'])
                         ->where('outcomemaster.label', '=', $label)
                         ->orderby('outcomemaster.odd', 'desc')
                         ->get();
                     foreach ($horseOutcomeOtherData as $horseOtherData) {

                         $data = (array)json_decode($horseOtherData->other);
                         $other_datas = array_merge($other_datas, $data);

                     }

                     dd($other_datas);

                 } else {


                     $horseOutcomeOtherData = DB::table('outcomemaster')
                         ->select('outcomemaster.other')
                         ->where('outcomemaster.date_stamp', '=', $today_stamp)
                         ->where('outcomemaster.game_id', '=', 1)
                         ->wherein('outcomemaster.bet_type', '=', $odd_type)
                         ->where('outcomemaster.label', '=', $label)
                         ->orderby('outcomemaster.odd', 'desc')
                         ->get();
                     foreach ($horseOutcomeOtherData as $horseOtherData) {

                         $data = (array)json_decode($horseOtherData->other);
                         $other_datas = array_merge($other_datas, $data);

                     }

                 }

                 #dd($other_datas);
                 $price_directions = 0;
                 $evntid = $horseOutcomeMaster->event_id;
                 $evnt_oddf = $horseOutcomeMaster->odd_fractional;
                 $evnt_api = $horseOutcomeMaster->api_id;
                 $evnt_lbl = str_slug($horseOutcomeMaster->label);
                 $evnt_od = $horseOutcomeMaster->odd;
                 $price_directions = $evnt_od - $horseOutcomeMaster->price_direction;

                 $horseBetClick = BettingCount::
                 where('event_id', '=', $event_id)
                     ->where('label', '=', $label)
                     ->where('bet_type', 'LIKE', $odd_type)
                     ->where('date_stamp', $today_stamp, '=')
                     ->count();

                 ($horseBetClick != 0) ? $bet_count = $horseBetClick : $bet_count = 0;

                 if ($horseOutcomeMaster->label == "UNNAMED FAVOURITE" || $horseOutcomeMaster->label == "UNNAMED 2nd FAVOURITE" || $horseOutcomeMaster->label == "MultiPosition" || $horseOutcomeMaster->api_id == "") {
                     continue;
                 }

                 $other_data = $other_datas;

                 (isset($other_data['silk_id'])) ? $silk = asset('assets/img/silk/' . $other_data['silk_id']) : $silk = asset('assets/img/generic_silk.gif');
                 (isset($other_data['suspended'])) ? $suspended = $other_data['suspended'] : $suspended = '';

                 if (isset($other_data['result'])) {
                     if ($other_data['result'] == "W" || $other_data['result'] == "w") {
                         $result_output = "Win";
                     } elseif ($other_data['result'] == "L") {
                         $result_output = "Lost";
                     } elseif ($other_data['result'] == "P") {
                         $result_output = "Placed";
                     } elseif ($other_data['result'] == "") {
                         $result_output = "Lost";
                     }
                 } else {
                     $result_output = "Lost";
                 }


                 if ($evnt_api == 1 and isset($other_data['price_direction'])) {
                     $price_direction = $other_data['price_direction'];
                     $arrow_direction = ($price_direction != -1) ? "up" : "down";
                     if ($price_direction == -1) {
                         $arrow_direction = "fa-long-arrow-down downArrow";
                     } elseif ($price_direction == 1) {
                         $arrow_direction = "fa-long-arrow-up UpArrow";
                     } else {
                         $arrow_direction = "";
                     }
                 } else {

                     if ($price_directions > 0) {
                         $arrow_direction = "fa-long-arrow-up UpArrow";
                     } elseif ($price_directions < 0) {
                         $arrow_direction = "fa-long-arrow-down downArrow";
                     } else {
                         $arrow_direction = "";
                     }

                 }

                 if ($suspended && $table_type != " red") {
                     $tr_class = "red";
                 }
                 $get_odd_output .= "<tr class='$tr_class'>";
                 if (isset($other_data['cloth_num'])) {
                     $cloth_num = $other_data['cloth_num'];
                 } else {
                     $cloth_num = 0;
                 }
                 $get_odd_output .= "<td>" . $cloth_num . "</td>";
                 $get_odd_output .= "<td><img src='" . $silk . "' alt=''></td>";
                 if (isset($other_data['jockey'])) {
                     $jockey = $other_data['jockey'];
                 } elseif (isset($other_data['coral_jockey'])) {
                     $jockey = $other_data['coral_jockey'];
                 } else {
                     $jockey = '';
                 }

                 if (isset($other_data['desc'])) {
                     $desc = $other_data['desc'];
                 } elseif (isset($other_data['coral_horse'])) {
                     $desc = $label;
                 } else {
                     $desc = '';
                 }


                 if (isset($other_data['coral_owner'])) {
                     $owner = $other_data['coral_owner'];
                 } else {
                     $owner = '';
                 }

                 if (isset($other_data['trainer'])) {
                     $trainer = $other_data['trainer'];
                 } elseif (isset($other_data['coral_trainer'])) {
                     $trainer = $other_data['coral_trainer'];
                 } else {
                     $trainer = '';
                 }

                 if (isset($other_data['coral_age'])) {
                     $age = $other_data['coral_age'];
                 } else {
                     $age = '';
                 }

                 if (isset($other_data['coral_formGuide'])) {
                     $form = $other_data['coral_formGuide'];
                 } else {
                     $form = '';
                 }


                 #$get_odd_output .= "<td><a href='/horse-racing/full-odds/".urlencode($venue_name)."/".urlencode($event_id)."/".urlencode(strtotime($event_date))."/".urlencode($horseOutcomeMaster->label)."'>". $jockey."</a></td>";
                 #$get_odd_output .="<td><a href='/horse-racing/full-odds/".urlencode($venue_name)."/".urlencode($event_id)."/".urlencode(strtotime($event_date))."/".urlencode($horseOutcomeMaster->label)."'>". $other_data['desc'] ."</a></td>";
                 $get_odd_output .= "<td>" . $jockey . "</td>";
                 $get_odd_output .= "<td><span id=\"tooltip-top\" data-toggle=\"tooltip\" data-placement=\"top\" title = \"Owner: {$owner} Trainer: {$trainer} Age: {$age} Form: {$form} \">" . $desc . "</span></td>";
                 if ($horseOutcomeMaster->odd_fractional == 'SP') {
                     $decimal_val = 'SP';
                 } else {

                     $new_val = 0;
                     $parts = explode(".", $evnt_od);

                     if (strlen($parts[0]) == 1) {
                         $new_val = '00' . $evnt_od;
                     } elseif (strlen($parts[0]) == 2) {
                         $new_val = '0' . $evnt_od;
                     }

                     #  $decimal_val =   number_format($new_val, 2, '.', '');
                     $decimal_val = $new_val;
                 }
                 if ($table_type !== " red") {
                     $get_odd_output .= "<td  onclick=\"get_bet_count({$horseOutcomeMaster->event_id},'{$venue_name}','{$horseOutcomeMaster->label}','{$odd_type}');\">
                   <span class='Odds odds_decimal'>$decimal_val</span>
<span class='Odds odds_fraction'>$evnt_oddf</span>
<i class='$arrow_direction  fa'></i>
<img class='Bookmaker' src='/assets/images/api_logo$evnt_api.svg'>

                    </td>";


                     $get_odd_output .= "<td><span id='total_bet_count{$evnt_lbl}'>{$bet_count}</span></td>";
                     $get_odd_output .= "<td><a class='btn btn-success' href='/horse-racing/full-odds/" . urlencode($horseOutcomeMaster->api_id) . "/" . urlencode($venue_name) . "/" . urlencode($event_id) . "/" . urlencode(strtotime($event_date)) . "/" . urlencode($horseOutcomeMaster->label) . "'>ALL ODDS</td>";


                 } else {
                     $get_odd_output .= "<td class=''>{$bet_count}</td>";
                     $get_odd_output .= "<td class=''>{$result_output}</td>";
                 }
                 $get_odd_output .= "</tr>";

                 $new_lbl1 = $new_lbl;
             }

        }
        $get_odd_output .= "</tbody></table>";
        echo $get_odd_output;

        */
    }


#
#
#
#
    public function getEvent(HorseGetEventFormRequest $request)
    {
        $today_stamp = strtotime("0:00:00");
        $today_date_time = date("Y-m-d H:i:s");
        $today_date = date("Y-m-d");
        $curent_call_js="";
        $get_event_output = "";
        $venueNames = array();
        $outcomeEvents = array();

        Session::put('changed_odd_type', $request->odd_type);
        $event_date = $request->event_date;
        $odd_type = urldecode($request->odd_type);

#dd($odd_type);
        $time_obj = \DateTime::createFromFormat('d/m/Y',$event_date);
        $event_date = $time_obj->format('Y-m-d');
        #dd($event_date);
        $gameBetType=BetType::where('from','=',$odd_type)->first();

        if(count($gameBetType)>0) {
            if ($gameBetType->api_id > 0) {
                $gameBetTypeTo = $gameBetType->to;
                $apiBetType = BetType::where('api_id', $gameBetType->api_id, '=')->where('to', $gameBetTypeTo, '=')->first();
                $getAPIfromOutcome = OutcomeMaster::distinct()
                    ->select('api_id')
                    ->where('bet_type', '=', $apiBetType->from)
                    ->where('game_id', '=', 1)
                    ->first();
            } else {
                $getAPIfromOutcome = OutcomeMaster::distinct()
                    ->select('api_id')
                    ->where('bet_type', '=', $odd_type)
                    ->where('game_id', '=', 1)
                    ->first();

            }
        }else{
            $getAPIfromOutcome = OutcomeMaster::distinct()
                ->select('api_id')
                ->where('bet_type', '=', $odd_type)
                ->where('game_id', '=', 1)
                ->first();

            #dd($getAPIfromOutcome);

        }
        #dd($getAPIfromOutcome->api_id);
        #$horseRacing=$this->gameMaster->where('active',1,'=')->where('id',1,'=')->first();
        $horseRacingGameGroup =GameGroupMaster::where('game_id','=',1)
            ->where('api_id','=',$getAPIfromOutcome->api_id)
            ->where('active_flag','=',1)
            ->orderby('g_g_name','asc')
            ->first();
        $ggm_g_g_id=$horseRacingGameGroup->g_g_id;
        #dd($ggm_g_g_id);
        $outcomeEventIds=OutcomeMaster::distinct()
            ->select('event_id')
            ->where('game_id','=',1)
            ->where('g_g_id','=',$ggm_g_g_id)
            ->where('date_stamp','=',$today_stamp)
            ->where('api_id','=',$getAPIfromOutcome->api_id)
            ->where('bet_type','=',$odd_type)
            ->get();
        #dd($outcomeEventIds);
        foreach($outcomeEventIds as $outcomeEventId)
        {
            $outcomeEvents[$outcomeEventId->event_id] = $outcomeEventId->event_id;
            #dd($outcomeEvents);
            $horseEventVenues = EventMaster::where('event_id','=',$outcomeEventId->event_id)->where('api_id','=',$getAPIfromOutcome->api_id)->get();
            #dd($horseEventVenues);
            foreach($horseEventVenues as $horseEventVenue)
            {
                $venueNames[$horseEventVenue ->venue_id] = $horseEventVenue ->venue_id;

            }
        }
        #dd($outcomeEvents);
        #$curent_call_js="";
        $horseVenueMasters = VenueMaster::where('g_g_id','=',$ggm_g_g_id)->where('game_id','=',1)->where('api_id','=',$getAPIfromOutcome->api_id)->orderby('venue_name','asc')->get();
        #dd($horseVenueMasters);
        foreach($horseVenueMasters as $horseVenueMaster){
            //if(in_array($horseVenueMaster->venue_id,$venueNames)) {

            if (array_key_exists($horseVenueMaster->venue_id, $venueNames)) {
               # var_dump($venueNames);
                #dd($horseVenueMaster->venue_id);
                $flag_init = false;
                #$countHorseEventMasters = EventMaster::where('venue_id', $horseVenueMaster->venue_id, '=')->where('api_id', 6, '=')->where('start_date', 'LIKE', $event_date . '%')->orderby('start_date', 'asc')->count();
                $horseEventMasters = EventMaster::where('venue_id', '=', $horseVenueMaster->venue_id)->where('game_id','=',1)->where('api_id', '=', $getAPIfromOutcome->api_id)->where('start_date', 'LIKE', $event_date . '%')->orderby('start_date', 'asc')->get();
#dd(count($horseEventMasters));
                $is_hidden = "";
                if (count($horseEventMasters) <= 0) {
                    $is_hidden = "hide";
                }
                #$state_class = "finished";
                $get_event_output .= <<<EOT
<div class='panel panel-default {$is_hidden}' data-VenueId='{$horseVenueMaster->venue_id}'>
    <div class='panel-heading'>
        <h3 class='panel-title'><img class='Icon' src='/assets/images/Horse.png'> {$horseVenueMaster->venue_name}</h3>
        <div class='panel-options pull-right'>
            <i class='fa fa-chevron-down'></i> <i class='fa fa-times'></i>
        </div>
    </div>
    <div id='event_{$horseVenueMaster->venue_id}' class='panel-body collapse'>
        <ul class='nav nav-pills nav-justified' id='myTab{$horseVenueMaster->venue_id}'>
EOT;
                $state_class="finished";
                $activated=false;
                $ev_first_key = 1;
                $start_counters = 1;
                foreach ($horseEventMasters as $horseEventMaster) {



                    if (array_key_exists($horseEventMaster->event_id, $outcomeEvents)) {

                        $venue_name = urlencode($horseVenueMaster->venue_name);
                       # dd($outcomeEvents);
                        $start_time_obj = \DateTime::createFromFormat('Y-m-d H:i:s', $horseEventMaster->start_date);
                        // $event_date = $horseEventMaster->start_date;
                        $start_time = $start_time_obj->format('H:i');
                        $dtA = strtotime($horseEventMaster->start_date);


                        // $dtB = time()+(1*60*60);
                        $dtB = time();
                        if ($dtA < $dtB) {
                            $em_state = "event-started";
                        } else {
                            $em_state = "event-not-started";
                        }
                        $is_active = $state_class;
                        #$out_odds = "no odds";
                        if ($flag_init === false) {
                            $flag_init = true;
                            #$out_odds = "";
                            #$out_odds_venue_id = $horseVenueMaster->venue_id;
                            $out_odds_event_id = $horseEventMaster->event_id;
                            #$out_odds_venue_name = $horseVenueMaster->venue_name;
                            #$out_odds_start_date = $horseEventMaster->start_date;
                        }
                        $class_add = "";
                        if ((time()) > strtotime($horseEventMaster->start_date)) {
                            $class_add = " red";
                            $em_state = "event-started";
                        }
                        if ($em_state == "event-started") {
                            $state_class = "";
                            #$out_odds = "";
                            #$out_odds_venue_id = $horseVenueMaster->venue_id;
                            $out_odds_event_id = $horseEventMaster->event_id;
                            #$out_odds_venue_name = $horseVenueMaster->venue_name;
                            #$out_odds_start_date = $horseEventMaster->start_date;
                        } elseif ($activated == false) {
                            $activated = true;
                            $is_active = "active";
                            $out_odds_event_id = $horseEventMaster->event_id;
                        }

                        $ev_start_date = strtotime($horseEventMaster->start_date);
                        $cur_time = strtotime($today_date_time);

                        if ($cur_time > $ev_start_date) {
                            $get_event_output .= <<<EOT
                        <li class='{$em_state}' id='{$horseEventMaster->event_id}'>
                            <a data-toggle='tab' class='{$is_active} {$class_add}' href='#Tab{$horseVenueMaster->venue_id}' onclick="get_odds('myTabContent__body{$horseVenueMaster->venue_id}','{$venue_name}','{$horseEventMaster->start_date}','{$horseEventMaster->event_id}','{$class_add}','{$request->odd_type}');" >{$start_time} </a>
                        </li>
EOT;
                            $start_counters = 0;
                        } else {
                            if ($start_counters == 1) {
                                $get_event_output .= <<<EOT
                            <li class='{$em_state} active' id='{$horseEventMaster->event_id}'>
                                <a data-toggle='tab' class='{$is_active} {$class_add}' href='#Tab{$horseVenueMaster->venue_id}' onclick="get_odds('myTabContent__body{$horseVenueMaster->venue_id}','{$venue_name}','{$horseEventMaster->start_date}','{$horseEventMaster->event_id}','{$class_add}','{$request->odd_type}');" >{$start_time} </a>
                            </li>
EOT;
                            } else {
                                $get_event_output .= <<<EOT
                            <li class='{$em_state}' id='{$horseEventMaster->event_id}'>
                                <a data-toggle='tab' class='{$is_active} {$class_add}' href='#Tab{$horseVenueMaster->venue_id}' onclick="get_odds('myTabContent__body{$horseVenueMaster->venue_id}','{$venue_name}','{$horseEventMaster->start_date}','{$horseEventMaster->event_id}','{$class_add}','{$request->odd_type}');" >{$start_time} </a>
                            </li>
EOT;
                            }
                        }


                        # if(in_array($horseEventMaster->event_id,$outcomeevents)) {


                        # }

                        #if ($is_hidden != "hide") {
                        $curent_call_js .= "\n\n$('#e{$out_odds_event_id} a').click();";
                        $curent_call_js .= "\n\n$('#e{$out_odds_event_id}').addClass('active');";
                        $start_counters = $start_counters + 1;
                        #}


                    }


                }
                $get_event_output .= "</ul>";
                $get_event_output .= "<div class='tab-content' id='myTabContent_{$horseVenueMaster->venue_id}'>";
                $fade_in = "active";
                $get_event_output .= <<<EOT
<div class='tab-pane $fade_in' id='Tab{$horseVenueMaster->venue_id}'>
			<div class='panel-body active' id='myTabContent__body{$horseVenueMaster->venue_id}'>
EOT;

                $eventsarr = array();
                $start_counter = 1;
                $changed_odd_type = urldecode(Session::get('changed_odd_type'));
                if($changed_odd_type==''){
                    $odd_type = 'Win & Each Way';
                }else{
                  $odd_type = $changed_odd_type;
                }
                foreach($horseEventMasters as $getHorseEvents) {
                    $ev_start_date = strtotime($getHorseEvents->start_date);
                    $cur_time = strtotime($today_date_time);

                    if ($cur_time > $ev_start_date) {

                    } else {
                        if ($start_counter == 1) {
                            $get_event_output .= <<<EOT
                                <script type="text/javascript">
                                    $(function() {
                                       // $("#{$horseVenueMaster->venue_id}").onClick(function(){
                                            //console.log('working');
                                            var url="/horse-racing/get_odd";
                                            var tab_id = "myTabContent__body{$horseVenueMaster->venue_id}";
                                            var venueName = "{$horseVenueMaster->venue_name}";
                                            var eventDate = "{$getHorseEvents->start_date}";
                                            var eventId = "{$getHorseEvents->event_id}";
                                            var oddType = "{HTML::decode($odd_type)}";
                                            var tableType;
                                                venue_id =venueName.replace(/ /g,"-");
                                                venue_id =venueName.replace(/[^a-zA-Z0-9_-]/g,'');
                                                $('#'+tab_id).parent().append("<div class='loading'></div>");
                                                $('#'+tab_id).parent().css("position","relative");
                                                $.post(url, {venue_name:venueName,event_date:eventDate,event_id:eventId,table_type:tableType,odd_type:oddType}, function(result){
                                                    $('#'+tab_id).html(result);
                                                    $('.loading').remove();
                                                    if(oTable)
                                                        oTable.fnDestroy();
                                                    console.log("#table"+eventId);
                                                    if(tableType==" red")
                                                    {
                                                        var oTable  = $("#table"+eventId).dataTable({
                                                            "bPaginate": false,
                                                            "oLanguage": { "sSearch": "" },
                                                            "aaSorting": [[ 5, "desc" ]]
                                                        });
                                                    }
                                                    else
                                                    {
                                                        var oTable  = $("#table"+eventId).dataTable({
                                                                    "bPaginate": false,
                                                                    "oLanguage": {"sSearch": ""},
                                                                    "aaSorting": [[ 5, "desc" ]],
                                                                   "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 1, 6 ] } ]
                                                        });
                                                    }
                                                    $('div.dataTables_filter input').attr('placeholder', 'Filter this race ...');
                                                    convert(odds_converter);
                                                    $("#table"+eventId).css("width","100%");
                                                    //$('<a href="/horse-racing-compare.php?event_id='+eventId+'&odd_type='+encodeURIComponent(oddType)+'" class="btn btn-primary">Compare</a>').appendTo('#table'+eventId+'_wrapper div.dataTables_filter');
                                                });


                                          //});
                                    });

                                </script>


EOT;

                        }
                        $start_counter = $start_counter + 1;
                    }
                }


                $get_event_output .= <<<EOT
</div>
		</div>
		</div>
		</div>
		</div>
EOT;



            }

        }

   print  $get_event_output;

    }



    public function oddsSelector(Request $request)
    {
        if($request->set_odd_type==true)
        {
            set_odd_type($request->odd_type);
            die("save");
        }


    }



    public function getFullOdds($apiID,$venue_name,$event_id,$event_date,$label)
    {
        $label = urldecode($label);
        $venue_name = urldecode($venue_name);
        #var_dump($event_date);
        $event_start = date('Y-m-d H:i:s',$event_date);
        #dd($event_start);
        $gameBetTypeTo = '';
        $api_array =array();
        $today_date = date("Y-m-d");
        $today_date_time = date("Y-m-d H:i:s");
        $today_stamp = strtotime("0:00:00");
        $other_data = array();
        #$changed_odd_type = Session::get('changed_odd_type');
        (Session::get('changed_odd_type')== '')?$odd_type = 'Win & Each Way':$odd_type =  urldecode(Session::get('changed_odd_type'));

        $gameBetType=BetType::where('from',$odd_type,'=')->first();
        if(count($gameBetType)>0){
            if($gameBetType->api_id > 0)
            {
                $gameBetTypeTo = $gameBetType->to;
            }else{
                $gameBetTypeTo = '';
            }
        }

        #dd(count($gameBetType));

        $getAPIID =EventMaster::where('game_id','=',1)->where('event_id','=',$event_id)->first();
        $gameMasters=$this->gameMaster->where('active','=',1)->get();
        $apiMasters = ApiMaster::orderby('id','asc')->get();

        if($gameBetTypeTo == ''){

            $getOutcomeAPIID = DB::table('outcomemaster')
                ->select('outcomemaster.other','outcomemaster.bet_type', 'apimaster.id', 'outcomemaster.odd_fractional', 'outcomemaster.odd', 'outcomemaster.label','outcomemaster.price_direction')
                ->join('apimaster', 'outcomemaster.api_id', '=', 'apimaster.id')
                ->join('eventmaster', 'eventmaster.event_id', '=', 'outcomemaster.event_id')
                ->join('venuemaster', 'venuemaster.venue_id', '=', 'eventmaster.venue_id')
                ->where('outcomemaster.date_stamp', '=', $today_stamp)
                ->where('venuemaster.venue_name', '=', $venue_name)
                ->where('eventmaster.event_id', '=', $event_id)
                ->where('eventmaster.start_date', '=', $event_start)
                ->where('outcomemaster.game_id', '=', 1)
                ->where('outcomemaster.bet_type', '=', $odd_type)
                #->where('outcomemaster.label', '=', $label)
                ->first();


            $fullbettingodds = DB::table('outcomemaster')
                ->select('outcomemaster.other','outcomemaster.bet_type', 'apimaster.id', 'outcomemaster.odd_fractional', 'outcomemaster.odd', 'outcomemaster.label','outcomemaster.price_direction','outcomemaster.event_id')
                ->join('apimaster', 'outcomemaster.api_id', '=', 'apimaster.id')
                ->join('eventmaster', 'eventmaster.event_id', '=', 'outcomemaster.event_id')
                ->join('venuemaster', 'venuemaster.venue_id', '=', 'eventmaster.venue_id')
                ->where('outcomemaster.date_stamp', '=', $today_stamp)
                ->where('venuemaster.venue_name', '=', $venue_name)
                ->where('eventmaster.event_id', '=', $event_id)
                ->where('eventmaster.start_date', '=', $event_start)
                ->where('outcomemaster.game_id', '=', 1)
                ->where('outcomemaster.bet_type', '=', $odd_type)
                #->where('outcomemaster.label', '=', $label)
                ->get();

            #var_dump($label);
            #var_dump($odd_type);
            #var_dump($event_start);
            #var_dump($event_id);
            #var_dump($venue_name);
            #var_dump($today_stamp);


            $fullOdds = DB::table('outcomemaster')
                ->select('outcomemaster.other', 'apimaster.id', 'outcomemaster.odd_fractional', 'outcomemaster.odd', 'outcomemaster.bet_type')
                ->join('apimaster', 'outcomemaster.api_id', '=', 'apimaster.id')
                ->join('eventmaster', 'outcomemaster.event_id', '=', 'eventmaster.event_id')
                ->where('outcomemaster.date_stamp', '=', $today_stamp)
                ->where('outcomemaster.label', '=', $label)
                ->where('eventmaster.start_date', '=', $event_start)
                ->where('outcomemaster.game_id', '=', 1)
                ->where('eventmaster.event_id', '=', $event_id)
                ->get();
            #dd($fullOdds);

            if($gameBetTypeTo == '' && $getAPIID->api_id == 1) {

                if (count($fullOdds) > 0) {
                    foreach ($fullOdds as $fullOdd) {
                        if($fullOdd->id == 1 && $fullOdd->bet_type =='Win & Each Way') {
                            $data = (array)json_decode($fullOdd->other);
                            $other_data = array_merge($other_data, $data);
                        }
                    }
                }

            }else {

                if (count($fullOdds) > 0) {
                    foreach ($fullOdds as $fullOdd) {
                        if($fullOdd->id == 6 && $fullOdd->bet_type =='Win or Each Way') {
                            $data = (array)json_decode($fullOdd->other);
                            $other_data = array_merge($other_data, $data);
                        }
                    }
                }
            }

////////////////////////////////////

            if($getAPIID->api_id != 1 && $getAPIID->api_id != 6) {

                $horseOutcomeOthers = OutcomeMaster::
                select('outcomemaster.event_id', 'outcomemaster.odd_fractional', 'outcomemaster.api_id', 'outcomemaster.label', 'outcomemaster.odd', 'outcomemaster.other', 'outcomemaster.price_direction', 'outcomemaster.bet_type')
                    ->join('eventmaster', 'outcomemaster.event_id', '=', 'eventmaster.event_id')
                    #->join('bettype', 'bettype.from', '=', 'outcomemaster.bet_type')
                    ->where('outcomemaster.label', '=', $label)
                    ->wherein('outcomemaster.api_id', [1, 6])
                    ->where('outcomemaster.date_stamp', '=', $today_stamp)
                    ->where('eventmaster.start_date', '=', $event_start)
                    ->where('outcomemaster.game_id', '=', 1)
                    ->orderby('outcomemaster.odd', 'desc')->get();

                #  dd($horseOutcomeOthers);
                foreach ($horseOutcomeOthers as $horseOutcomeOther) {
                    $data = (array)json_decode($horseOutcomeOther->other);
                    $other_data = array_merge($other_data, $data);
                }
                # dd($other_datas);
                #echo '<pre>';
                #var_dump($other_data);
                #echo '</pre>';
                #dd('stop');


            }elseif($getAPIID->api_id != 1 && $getAPIID->api_id == 6){

                    $horseOutcomeOthers = OutcomeMaster::
                    select('outcomemaster.event_id','outcomemaster.odd_fractional','outcomemaster.api_id','outcomemaster.label','outcomemaster.odd','outcomemaster.other','outcomemaster.price_direction', 'outcomemaster.bet_type')
                        ->join('eventmaster', 'outcomemaster.event_id', '=', 'eventmaster.event_id')
                        #->join('bettype', 'bettype.from', '=', 'outcomemaster.bet_type')
                        ->where('outcomemaster.label','=',$label)
                        ->where('outcomemaster.api_id','=',1)
                        ->where('outcomemaster.date_stamp','=',$today_stamp)
                        ->where('eventmaster.start_date', '=', $event_start)
                        ->where('outcomemaster.game_id', '=', 1)
                        ->orderby('outcomemaster.odd','desc')->get();

                    #  dd($horseOutcomeOthers);
                    foreach($horseOutcomeOthers as $horseOutcomeOther) {
                        if($horseOutcomeOther->bet_type =='Win & Each Way') {
                            $data = (array)json_decode($horseOutcomeOther->other);
                            $other_data = array_merge($other_data, $data);
                        }
                    }

                    #echo '<pre>';
                    #var_dump($other_data);
                    #echo '</pre>';
                    #dd('stop');

            }
 /*
            else{

                if($gameBetTypeTo == '' ) {
                    $horseOutcomeOthers = OutcomeMaster::
                    select('outcomemaster.event_id', 'outcomemaster.odd_fractional', 'outcomemaster.api_id', 'outcomemaster.label', 'outcomemaster.odd', 'outcomemaster.other')
                        ->join('bettype', 'bettype.from', '=', 'outcomemaster.bet_type')
                        ->where('outcomemaster.label', $label, '=')
                        ->where('outcomemaster.api_id', 1, '=')
                        ->where('outcomemaster.date_stamp', $today_stamp, '=')
                        ->where('outcomemaster.game_id', 1, '=')
                        ->orderby('outcomemaster.odd', 'desc')->first();

                    #  dd($horseOutcomeOthers);
                    $data = (array)json_decode($horseOutcomeOthers->other);
                    $other_data = array_merge($other_data, $data);

                    # dd($other_datas);
                }

            }
*/
////////////////////////////
            /*
            if($gameBetTypeTo == '' && $getAPIID->api_id == 1) {}else {

                if (count($fullOdds) > 0) {
                    foreach ($fullOdds as $fullOdd) {
                        $data = (array)json_decode($fullOdd->other);
                        $other_data = array_merge($other_data, $data);

                        #var_dump($other_data);
                    }
                }
            }
            */
            $other_data = (object) $other_data;


        }else {


            $fullbettingodds = DB::table('apimaster')
                ->select('outcomemaster.other','outcomemaster.bet_type', 'apimaster.id', 'outcomemaster.odd_fractional', 'outcomemaster.odd', 'outcomemaster.label','outcomemaster.price_direction','outcomemaster.event_id')
                ->join('outcomemaster', 'outcomemaster.api_id', '=', 'apimaster.id')
                ->join('eventmaster', 'eventmaster.event_id', '=', 'outcomemaster.event_id')
                ->join('venuemaster', 'venuemaster.venue_id', '=', 'eventmaster.venue_id')
                ->join('bettype', 'bettype.from', '=', 'outcomemaster.bet_type')
                ->where('outcomemaster.date_stamp', $today_stamp, '=')
                ->where('venuemaster.venue_name', $venue_name, '=')
                ->where('eventmaster.start_date', $event_start, '=')
                ->where('outcomemaster.game_id', 1, '=')
                ->where('eventmaster.api_id', 1, '=')
                # ->where('outcomemaster.bet_type', $odd_type, '=')
                ->where('bettype.to', $gameBetTypeTo, '=')
                ->get();


            $fullOdds = DB::table('apimaster')
                ->select('outcomemaster.other', 'apimaster.id', 'outcomemaster.odd_fractional', 'outcomemaster.odd')
                ->join('outcomemaster', 'outcomemaster.api_id', '=', 'apimaster.id')
                ->join('eventmaster', 'outcomemaster.event_id', '=', 'eventmaster.event_id')
                ->join('bettype', 'bettype.from', '=', 'outcomemaster.bet_type')
                ->where('outcomemaster.date_stamp', $today_stamp, '=')
                ->where('outcomemaster.label', $label, '=')
                ->where('eventmaster.start_date', $event_start, '=')
                ->where('outcomemaster.game_id', 1, '=')
                ->where('bettype.to', $gameBetTypeTo, '=')
                ->get();

            if (count($fullOdds) > 0) {
                foreach($fullOdds as $fullOdd) {
                    $data = (array)json_decode($fullOdd->other);
                    $other_data = array_merge($other_data, $data);

                    #var_dump($other_data);
                }
            }
            $other_data = (object) $other_data;

        }

        #dd(count($fullOdds));






        foreach($apiMasters as $apiId) {
            $api_array[$apiId->id] = $apiId->icon;
        }
      /*
        foreach($apiMasters as $apiId)
        {
            $api_array[$apiId->id] = $apiId->icon;
            if($apiId->id != 5) {

               # $apiBetType = BetType::where('api_id', $apiId->id, '=')->where('to', $gameBetTypeTo, '=')->first();

              #  if (count($apiBetType) > 0) {
                    $outcomeMaster = OutcomeMaster::where('label', $label, '=')->where('api_id', $apiId->id, '=')->where('bet_type', $odd_type, '=')->first();

                    if (count($outcomeMaster) != 0) {
                        $data = (array)json_decode($outcomeMaster->other);
                        $other_data = array_merge($other_data, $data);
                        #var_dump($other_data);
                    }
               # }
            }
        }

        */
        #dd($other_data);


        $silk = ($other_data->silk_id)?$other_data->silk_id:"generic_silk.gif";
       # $eventMaster=EventMaster::select('start_date')->where('event_id',$event_id,'=')->first();
       # $start_date =$eventMaster->start_date;
        #$event_date = strtotime($event_date);
        $start_time_obj = \DateTime::createFromFormat('Y-m-d H:i:s',$event_start);
        $event_time = $start_time_obj->format('H:i');
        $pageTitle = $venue_name." - ".$event_time." - Winner";
        $data = array(
            'venue_name' => $venue_name,
            'event_id' => $event_id,
            'label' => $label,
            'page_title' => $pageTitle,
            'silk' => $silk,
            'odd_type' =>$odd_type,
            'today_date' =>$today_date,
            'event_date' =>$event_start,
            'today_date_time'=>$today_date_time
        );

        $horse_odds = ApiMaster::
            leftJoin('outcomemaster', 'apimaster.id', '=', 'outcomemaster.api_id')
            ->where('outcomemaster.label',$label,'=')
            ->where('outcomemaster.game_id',1,'=')

            ->where('outcomemaster.date_stamp',$today_stamp,'=')
            ->get();



        return view('frontend.horse.horse', compact('gameMasters','other_data','api_array','data','horse_odds','fullOdds','fullbettingodds','gameBetTypeTo','getOutcomeAPIID'));
    }


    public function HorseSpecificOdds($api_id, $label, $gameBetTypeTo)
    {
        $today_stamp = strtotime("0:00:00");

        $horse_odds = 0;
        #(Session::get('changed_odd_type')== '')?$odd_type = 'Win & Each Way':$odd_type =  Session::get('changed_odd_type');

       # $gameBetType=BetType::where('from',$odd_type,'=')->first();
       # if($gameBetType->api_id != 0)
      #  {
       #     $gameBetTypeTo = $gameBetType->to;
      #  }

        $apiBetType = BetType::where('api_id', $api_id, '=')->where('to', $gameBetTypeTo, '=')->first();
        if (count($apiBetType) > 0) {
            $horse_odds = DB::table('outcomemaster')
                #->leftJoin('outcomemaster', 'apimaster.id', '=', 'outcomemaster.api_id')
                ->where('label', $label, '=')
                ->where('game_id', 1, '=')
                ->where('api_id', $api_id, '=')
                ->where('bet_type', $apiBetType->from, '=')
                ->where('date_stamp', $today_stamp, '=')
                ->first();
        }

        if(count($horse_odds)> 0) {
            return $horse_odds;
        }else{
            return '';
        }


    }



    public function getBetClick(HorseGetBetClickRequest $request)
    {
        $today_date = date("Y-m-d");
        $today_date_time = date("Y-m-d H:i:s");
        $today_stamp = strtotime("0:00:00");

        $venue_name = $request->venue_name;
        $odd_type = $request->odd_type;
        $event_id = $request->event_id;
        $label = $request->name;



        $betclick = array(
            "label"=>$label,
            "bet_click"=>1,
            "event_id"=>$event_id,
            "date_stamp"=>$today_stamp,
            "bet_type"=>$odd_type,
        );

        $this->bettingCount->create($betclick);

        $new_lbl = 'total_bet_count'.str_slug($label);

        $horseBetClick = BettingCount::where('event_id',$event_id,'=')
            ->where('label',$label,'=')
            ->where('bet_type','LIKE',$odd_type)
            ->where('date_stamp',$today_stamp,'=')

            ->count();

        $json['type'] = 'success';
        $json['name'] = $new_lbl;
        $json['count'] = $horseBetClick;

        header('Content-Type: application/json');
        die(json_encode($json));


    }



}
