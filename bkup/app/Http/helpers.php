<?php

//use DB;

function get_url( $url, $req_data=array(),$is_json=false, $timeout = 100)
{
    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $content = curl_exec( $ch );
    $response = curl_getinfo( $ch );
    // echo 'Curl error: ' . curl_error($ch);
    curl_close ( $ch );
    return array( $content, $response );
}

function get_url_j( $url, $req_data=array(),$is_json=false, $timeout = 100)
{
    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
#dd($url);
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    if( is_array($req_data) && $req_data!=array())
    {
       # dd('1');
        curl_setopt($ch, CURLOPT_POST, true);
        foreach($req_data as $k=>$v)
        {
            $post_str .=$k."=".$v."&";
        }
        $post_str = rtrim($post_str,"&");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_str);
    }
    elseif($req_data!=="")
    {
      #  dd(curl_setopt($ch, CURLOPT_POSTFIELDS,$req_data));
        curl_setopt($ch, CURLOPT_POSTFIELDS,$req_data);
    }

    if($is_json==true)
    {
      #  dd(curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($req_data))));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($req_data)));
    }

    $content = curl_exec( $ch );
    $response = curl_getinfo( $ch );
   # var_dump($content);
    # dd($content);
    curl_close ( $ch );

#dd('stop');
    return array( $content, $response );
}

function get_url_jb( $url, $req_data=array(),$is_json=false, $timeout = 100)
{
    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
#dd($url);
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    if( is_array($req_data) && $req_data!=array())
    {
       #  dd('1');
        curl_setopt($ch, CURLOPT_POST, true);
        foreach($req_data as $k=>$v)
        {
            $post_str .=$k."=".$v."&";
        }
        $post_str = rtrim($post_str,"&");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_str);
    }
    elseif($req_data!=="")
    {
       #   dd('2');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$req_data);
    }

    if($is_json==true)
    {
       #  dd('3');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($req_data)));
    }
  #  dd(curl_exec( $ch ));
    $content = curl_exec( $ch );
    $response = curl_getinfo( $ch );
    curl_close ( $ch );
#dd('stop');
    return array( $content, $response );
}

function rolling_curl($urls)
{

    $ch = array();
    $mh = curl_multi_init();
    $active = null;
    foreach($urls as $key=>$url)
    {
        $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
        $ch[$key] = curl_init();
        curl_setopt($ch[$key], CURLOPT_URL, $url);
        curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch[$key], CURLOPT_BINARYTRANSFER,1);
        curl_multi_add_handle($mh,$ch[$key]);

     #   print curl_multi_add_handle($mh,$ch[$key]);
    }

    do
    {
        while(($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
      //  dd($execrun);
    } while ($running);
    $output=array();
    foreach ($ch as $key=>$ch_data)
    {

        $output[$key] = curl_multi_getcontent($ch_data);
       # dd(curl_multi_getcontent($ch_data));
     #  dd($output);
    }
#    var_dump($output);

    foreach($urls as $key=>$val)
    {
        curl_multi_remove_handle($mh, $ch[$key]);
    }
    return array("output"=>$output);
}

function rolling_curl_batch($urls) {

    // make sure the rolling window isn't greater than the # of urls
    $rolling_window = 500;
    $rolling_window = (sizeof($urls) < $rolling_window) ? sizeof($urls) : $rolling_window;
#dd($rolling_window);
    $master = curl_multi_init();
    $curl_arr = array();

    // add additional curl options here
    $std_options = array(CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5);
    $options =$std_options;

    // start the first batch of requests
    $output_final = array();
    // foreach($urls as $i=> $v)
    for ($i = 0; $i < $rolling_window; $i++) {
        $ch = curl_init();
        $options[CURLOPT_URL] = $urls[$i];
        curl_setopt_array($ch, $options);
        curl_multi_add_handle($master, $ch);
        # }

        do {
            while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) ;


#$i = 1;
            if ($execrun != CURLM_OK)
                break;
            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($master)) {
                $info = curl_getinfo($done['handle']);
                if ($info['http_code'] == 200) {
                    $output = curl_multi_getcontent($done['handle']);
                    $output_final[] = $output;
                    // request successful.  process output using the callback function.
                    // $callback($output);
                    #dd($output);
#var_dump($options[CURLOPT_URL]);
                    // start a new request (it's important to do this before removing the old one)
                   # $ch = curl_init();
                  #  $options[CURLOPT_URL] = $urls[$i++];  // increment i
                  #  curl_setopt_array($ch, $options);
                  #  curl_multi_add_handle($master, $ch);
                    #
                    // remove the curl handle that just completed
                    curl_multi_remove_handle($master, $done['handle']);
                } else {
                    // request failed.  add error handling.
                }
            }
        } while ($running);
    }
#dd('stop');
    curl_multi_close($master);
    return array("output"=>$output_final);
}

function get_url_tofile( $url, $req_data=array(),$is_json=false, $timeout = 100)
{
    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
    $ch = curl_init();

    $newfilename="curl_output_".$i.".xml";
    $out =fopen("files/".$newfilename,"wb");
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_FILE, $out);
    $content = curl_exec( $ch );
    curl_close ( $ch );
    return array( $content);
}
function get_url_array( $url_array, $req_data="", $type="")
{
    $ch = array();
    $mh = curl_multi_init();
    $active = null;
    foreach($url_array as $key=>$url)
    {
        $url = str_replace( "&amp;", "&", urldecode(trim($url)) );
        $ch[$key] = curl_init();
        curl_setopt($ch[$key], CURLOPT_URL, $url);
        curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch[$key], CURLOPT_BINARYTRANSFER,1);
        if($type=="json" && $req_data!="")
        {
            curl_setopt($ch[$key], CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($req_data[$key])));
            curl_setopt($ch[$key], CURLOPT_POSTFIELDS,$req_data[$key]);
        }
        curl_multi_add_handle($mh,$ch[$key]);
    }

    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($active);
    $output=array();
    foreach ($ch as $key=>$ch_data) {
        $output[$key] = curl_multi_getcontent($ch_data);
    }
    foreach($url_array as $key=>$val)
    {
        curl_multi_remove_handle($mh, $ch[$key]);
    }
    return array("output"=>$output);
}

function curl_get_file_size( $url ) {
    // Assume failure.
    $result = -1;

    $curl = curl_init( $url );

    // Issue a HEAD request and follow any redirects.
    curl_setopt( $curl, CURLOPT_NOBODY, true );
    curl_setopt( $curl, CURLOPT_HEADER, true );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
    # curl_setopt( $curl, CURLOPT_USERAGENT, get_user_agent_string() );

    $data = curl_exec( $curl );
    curl_close( $curl );

    if( $data ) {
        $content_length = "unknown";
        $status = "unknown";

        if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
            $status = (int)$matches[1];
        }

        if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
            $content_length = (int)$matches[1];
        }

        // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        if( $status == 200 || ($status > 300 && $status <= 308) ) {
            $result = $content_length;
        }
    }

    return $result;
}


function validate_string($str)
{
    $str = remove_specchar($str);
    //$str=mysql_real_escape_string($str);
    return $str;
}
function remove_specchar($str)
{
    // $output = str_replace("\'","\\\'",$str);
    $output = str_replace("'","",$str);
    return $output;
}

function get_odd_type()
{
    $value = \Illuminate\Support\Facades\Session::get('odd_type');
    if(isset($value))
    {
        return $value;
    }
    return "fraction";
}

function set_odd_type($val)
{
    if($val=="fraction" || $val=="decimal")
        \Illuminate\Support\Facades\Session::put('odd_type', $val);

}

function maxValueInArray($array, $keyToSearch)
{
    $currentMax = NULL;
    foreach($array as $arr)
    {
        foreach($arr as $key => $value)
        {
            if ($key == $keyToSearch && ($value >= $currentMax))
            {
                $currentMax = $value;
            }
        }
    }

    return $currentMax;
}
function doublemax($mylist){
    $maxvalue=max($mylist);
    while(list($key,$value)=each($mylist)){
        if($value==$maxvalue)$maxindex=$key;
    }
    return array("m"=>$maxvalue,"i"=>$maxindex);
}

function GetEvents($venue_id)
{
    $today_stamp = strtotime("0:00:00");
    $today_date = date("Y-m-d");
    $horseVenueMasters = DB::table('venuemaster')
        ->leftJoin('eventmaster', 'venuemaster.venue_id', '=', 'eventmaster.venue_id')
        ->where('eventmaster.date_stamp', $today_stamp, '=')
        ->where('eventmaster.start_date', 'Like', $today_date.'%')
        ->where('eventmaster.api_id', 1, '=')
        ->where('eventmaster.game_id', 1, '=')
        ->where('eventmaster.venue_id', $venue_id, '=')
        ->orderby('start_date','asc')
        ->get();
    return $horseVenueMasters;
}

function HorseSpecificOdds($api_id, $label)
{
    $today_stamp = strtotime("0:00:00");
    $gameBetTypeTo = 0;
    $horse_odds = 0;
    (Session::get('changed_odd_type')== '')?$odd_type = 'Win & Each Way':$odd_type =  Session::get('changed_odd_type');

    $gameBetType=DB::table('bettype')->where('from',$odd_type,'=')->first();
    if($gameBetType->api_id != 0)
    {
        $gameBetTypeTo = $gameBetType->to;
    }

    $apiBetType = DB::table('bettype')->where('api_id', $api_id, '=')->where('to', $gameBetTypeTo, '=')->first();
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


function HorseAllOdds($api_id, $venue_name,$start_date)
{
    $today_stamp = strtotime("0:00:00");
    $gameBetTypeTo = 0;
    $fullOdds = 0;
    (Session::get('changed_odd_type')== '')?$odd_type = 'Win & Each Way':$odd_type =  Session::get('changed_odd_type');

    $gameBetType=DB::table('bettype')->where('from',$odd_type,'=')->first();
    if($gameBetType->api_id != 0)
    {
        $gameBetTypeTo = $gameBetType->to;
    }

    $apiBetType = DB::table('bettype')->where('api_id', $api_id, '=')->where('to', $gameBetTypeTo, '=')->first();
    if (count($apiBetType) > 0) {




  $fullOdds = DB::table('eventmaster')
      ->select('outcomemaster.label','outcomemaster.api_id','outcomemaster.other')
      ->join('venuemaster', 'venuemaster.venue_id', '=', 'eventmaster.venue_id')
      ->join('outcomemaster', 'eventmaster.event_id', '=', 'outcomemaster.event_id')
      ->where('outcomemaster.date_stamp', $today_stamp, '=')
      ->where('venuemaster.venue_name', $venue_name, '=')
      ->where('eventmaster.start_date', $start_date, '=')
      ->where('outcomemaster.game_id', 1, '=')
      ->where('eventmaster.api_id', $api_id, '=')
      ->where('outcomemaster.bet_type', $apiBetType->from, '=')

      ->get();

    }

    if(count($fullOdds)> 0) {
        return $fullOdds;
    }else{
        return '';
    }


}

