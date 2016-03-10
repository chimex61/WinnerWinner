{{--*/
    use App\Models\EventMaster;
    use App\Models\OutcomeMaster;
    use App\Models\VenueMaster;
    use App\Models\ApiMaster;
    use App\Models\BetType;
    use App\Models\BettingCount;
    $today_date = date("Y-m-d");
    $today_date_time = date("Y-m-d H:i:s");
    $today_stamp = strtotime("0:00:00");

/*--}}
@extends('frontend.layout')

@section('frontend.inline_styles')
@endsection

@section('frontend.content')

<section id="main-wrapper" class="HorseRacing FullOdds">

	<div id="specific_horse_info">
        <h3 class="sub-title">{!!$data['page_title']!!}</h3>
        <hr>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><img class="Icon" src="{{URL::to('/')}}/assets/images/Horse.png"> Horse Information</h3>
                    <div class="panel-options pull-right">
                        <i class="fa fa-chevron-down"></i> <i class="fa fa-times"></i>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="myTabContent" class="tab-content">
                        <div id="PremierLeague" class="tab-pane fade active in">
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Horse</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Jockey</td>
                                            <td>@if(isset($other_data->jockey)){!!$other_data->jockey!!}@else {!!$other_data->coral_jockey!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Horse</td>
                                            <td>{!!$data['label']!!}</td>
                                        </tr>
                                        <tr>
                                            <td>Owner</td>
                                            <td>@if(isset($other_data->coral_owner)){!!$other_data->coral_owner!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Trainer</td>
                                            <td>@if(isset($other_data->trainer)){!!$other_data->trainer!!}@else{!!$other_data->coral_trainer!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Number</td>
                                            <td>@if(isset($other_data->trainer)){!!$other_data->cloth_num!!}@else{!!$other_data->runnerNumber!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Silk</td>
                                            <td><img src="{{URL::to('/')}}/assets/img/silk/{!!$data['silk']!!}" alt=''></td>
                                        </tr>
                                        <tr>
                                            <td>Age</td>
                                            <td>@if(isset($other_data->coral_age)){!!$other_data->coral_age!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Draw</td>
                                            <td>@if(isset($other_data->coral_drawNumber)){!!$other_data->coral_drawNumber!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>@if(isset($other_data->coral_formGuide)){!!$other_data->coral_formGuide!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Horse Overview</td>
                                            <td>@if(isset($other_data->coral_runner)){!!$other_data->coral_runner!!}@endif</td>
                                        </tr>
                                        <tr>
                                            <td>Sire Overview</td>
                                            <td>@if(isset($other_data->coral_sire)){!!$other_data->coral_sire!!}@endif</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><img class="Icon" src="{{URL::to('/')}}/assets/images/Horse.png">
                        {!!$data['page_title']!!}
                    </h3>
                    <div class="panel-options pull-right">
                        <i class="fa fa-chevron-down"></i> <i class="fa fa-times"></i>
                    </div>
                </div>
                <div class="panel-body">
                    <div id="myTabContent" class="tab-content">
                        <div id="PremierLeague" class="tab-pane fade active in">
                            <div class="panel-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Bookmaker</th>
                                            <th>Odds</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($fullOdds as $horse_odds)

                                        <tr>
                                           <td class="Odds">
                                               <img class="Bookmaker" src="/assets/images/api_logo{{$horse_odds->id}}.svg">
                                           </td>
                                           <td class="Odds"><i class="@if($horse_odds->id == 1){{(json_decode($horse_odds->other)->price_direction!=-1)?"fa-long-arrow-up UpArrow":"fa-long-arrow-down downArrow"}}@endif fa"></i>
                                               <span class="Odds odds_fraction">{{($horse_odds->odd_fractional== 'SP')?'SP':$horse_odds->odd}}</span>
                                               <span class="Odds odds_decimal">{{$horse_odds->odd_fractional}}</span>
                                           </td>
                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="col-md-12">
	<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><img class="Icon" src="{{URL::to('/')}}/assets/images/Horse.png">
                {{'All Odds'}}
            </h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i> <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="panel-body">
            <div id="myTabContent" class="tab-content">
                <div id="PremierLeague" class="tab-pane fade active in">
                    <div class="panel-body">

                        <table class='table table-striped BetTable table-hover table-bordered' cellpadding='0' cellspacing='0' border='0' id=''>
                            <thead>
                                <tr class='SortingPart'>
                                    <th>#</th>
                                    <th>Silk</th>
                                    <th>Jockey</th>
                                    <th>Horse</th>
                                    @foreach($api_array as $vk=>$apisd)
                                    @if($vk != 5)
                                    <th><img src="/assets/images/api_logo{{$vk}}.svg" /></th>
                                    @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>


                                @foreach($fullbettingodds as $horse_full_odd)
                                 @if($horse_full_odd->label=="UNNAMED FAVOURITE" ||$horse_full_odd->label=="UNNAMED 2nd FAVOURITE" ||$horse_full_odd->label=="MultiPosition" || $horse_full_odd->id=="")

                                                {{--*/continue;/*--}}
                                            @endif
                                    {{--*/
                                        $other_data = json_decode($horse_full_odd->other);
                                    /*--}}
                                    <tr>
                                        <td>
                                            @if(isset($other_data->cloth_num))
                                                {{$other_data->cloth_num}}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($other_data->silk_id))
                                                <img src="{{URL::to('/')}}/assets/img/silk/{!!$other_data->silk_id!!}" alt=''>
                                            @else
                                                <img src="{{URL::to('/')}}/assets/img/generic_silk.gif" alt=''>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($other_data->jockey))
                                                {{$other_data->jockey}}
                                            @endif
                                        </td>
                                        <td>{{$horse_full_odd->label}}</td>
                                        <?php
                                            $gameBetTypeTo = '';
                                            (Session::get('changed_odd_type')== '')?$odd_type = 'Win & Each Way':$odd_type =  Session::get('changed_odd_type');
                                            $gameBetType=BetType::where('from',$odd_type,'=')->first();
                                            if($gameBetType->api_id != 0)
                                            {
                                                $gameBetTypeTo = $gameBetType->to;
                                            }
                                            $allOdds = ApiMaster::
                                                select('outcomemaster.other','apimaster.id','outcomemaster.odd_fractional','outcomemaster.odd')
                                                ->join('outcomemaster', 'outcomemaster.api_id', '=', 'apimaster.id')
                                                ->join('eventmaster', 'outcomemaster.event_id', '=', 'eventmaster.event_id')
                                                ->join('bettype', 'bettype.from', '=', 'outcomemaster.bet_type')
                                                ->where('outcomemaster.date_stamp', $today_stamp, '=')
                                                ->where('outcomemaster.label', $horse_full_odd->label, '=')
                                                ->where('eventmaster.start_date', $data['event_date'], '=')
                                                ->where('outcomemaster.game_id', 1, '=')
                                                ->where('bettype.to', $gameBetTypeTo, '=')
                                                ->orderby('apimaster.id','asc')
                                                ->get();
                    ?>
                                            @foreach($allOdds as $horse_od)


                                                   <td class="Odds"><i class="@if($horse_od->id == 1){{(json_decode($horse_od->other)->price_direction!=-1)?"fa-long-arrow-up UpArrow":"fa-long-arrow-down downArrow"}}@endif fa"></i>
                                                       <span class="Odds odds_fraction">{{($horse_od->odd_fractional== 'SP')?'SP':$horse_od->odd}}</span>
                                                       <span class="Odds odds_decimal">{{$horse_od->odd_fractional}}</span>
                                                   </td>


                                            @endforeach




                                    </tr>
                                @endforeach



                            </tbody>

                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>




</section>
<style type="text/css">
	<?php

		if(get_odd_type()=="fraction")
		{
			echo ".odds_decimal {display:none;}";
			echo ".odds_fraction {display:inline;}";
		}
		else
		{
			echo ".odds_fraction {display:none;}";
			echo ".odds_decimal {display:inline;}";
		}

	?>
</style>
<section id="main-wrapper"></section>

@endsection

@section('frontend.inline_scripts')
@endsection





