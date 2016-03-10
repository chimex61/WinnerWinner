{{--*/
    $curent_call_js='';
    $first_itr = 1;
    $venue_nam0 = '';
    $venue_nam1 = '';
$start_counters = 1;
    $activated=0;
/*--}}
@extends('frontend.layout')

@section('frontend.inline_styles')
@endsection

@section('frontend.content')
<section id="main-wrapper" class="HorseRacing">
    <h3 class="subtitle">Horse Racing</h3>
    <hr>
    <div class="col-md-12 ">
        <div class="row">
	        <form class="form-horizontal DateTime" role="form">
		        <div class="form-group">
			        <div class="col-sm-4">
				        <div class="input-group">
                            <span class="input-group-addon">Date</span>
                            <input type='text' class='form-control' data-date-format='dd/mm/yyyy' value='{{Carbon\Carbon::today()->format('d/m/Y')}}' id='dp3' >
				            <span class="input-group-btn">
                                <button class="btn btn-success" onClick="return get_events('')" type="button">Go!</button>
                            </span>
				         </div>
			        </div>
					<div class="col-sm-4">
				        <div class="input-group">
                            <span class="input-group-addon">Time</span>
				            <div class="LocalTime form-control">
                                <script language="javascript" src="{{URL::to('/')}}/assets/js/liveclock.js"></script>
				            </div>
				        </div>
			        </div>
			        <div class="col-sm-4 ShareBlock">
				        <div class="input-group">
                            <span class="input-group-addon">Share</span>
				            <div class="LocalTime form-control">
                                <a href="" class="TwitterShare"><i class="fa fa-twitter"></i></a>
                                <a href="" class="FacebookShare"><i class="fa fa-facebook"></i></a>
                                <a href="whatsapp://send?text=The text to share!" data-action="share/whatsapp/share" class="WatsappShare"><i class="fa fa-whatsapp"></i></a>
                                <a href="" class="GoogleShare"><i class="fa fa-google-plus"></i></a>
				            </div>
				         </div>
			        </div>
		        </div>
	        </form>
	        <hr>
	        <div class="form-group BetTypeDropdown">
	            <label class="col-sm-2 control-label">Select bet type</label>
	            <div class="col-sm-4">
                    <select style="width:100%" id="bet_type" onChange="get_events('');">
                    @foreach($bet_types as $bet_type)
                        @if($bet_type->bet_type !="" )
                            @if(
                                    $bet_type->bet_type =="Winning Distance" ||
                                    $bet_type->bet_type =="Multi position" ||
                                    $bet_type->bet_type =="win" ||
                                    $bet_type->bet_type =="Outright NRNB" ||
                                    $bet_type->bet_type =="Win Only" ||
                                    $bet_type->bet_type =="Win or Each Way" ||
                                    $bet_type->bet_type =="Outright Betting" ||
                                    $bet_type->bet_type =="Antepost Outright" ||
                                    $bet_type->bet_type =="2016 Betfred Cheltenham Gold Cup" ||
                                    $bet_type->bet_type =="2016 Champion Hurdle" ||
                                    $bet_type->bet_type =="Silviniaco Conti to win the Grand National"
                                )
                            @else
                                <option value='{!!$bet_type->bet_type!!}' >{!!$bet_type->bet_type!!}</option>
                            @endif
                        @endif
                    @endforeach
                    </select>
	            </div>
	        </div>
	        <hr>
            {{'optimized site'}}
		    <div id="venue_list">
                <?php $i=1;?>
            @foreach($event_start as $key => $val)
            {{--*/

                $venue_nam0 = explode("|", $val)[0];
                $matches = array();

            /*--}}
                @foreach($event_start as $k=>$v)
                    @if(preg_match("/\b$venue_nam0\b/i", $v))
                    {{--*/ $matches[$k] = $v; /*--}}
                    @endif
                @endforeach
                @if($venue_nam0 == $venue_nam1)
                    @if(strtotime(date("Y-m-d H:i:s")) > strtotime(explode("|", $val)[1]))
                        <li class='{{"event-started"}}' id='{{explode("|", $key)[1]}}'>
                            <a data-toggle='tab' class='red' href='#Tab{{explode("|", $key)[0]}}' onclick="get_odds('myTabContent__body{{explode("|", $key)[0]}}','{{explode("|", $val)[0]}}','{{explode("|", $val)[1]}}','{{explode("|", $key)[1]}}','red','Win & Each Way');" >{{substr(explode("|", $val)[1],11,5)}} </a>
                        </li>
                        {{--*/ $start_counters = 0; /*--}}
                    @else
                        @if($start_counters == 1)
                        {{--*/
                            $activated = 1;
                            $venue_id=explode("|", $key)[0];
                            $event_id=explode("|", $key)[1];
                            $venue_name=explode("|", $val)[0];
                            $start_time=explode("|", $val)[1];
                        /*--}}
                            <li class='{{"event-not-started"}} active' id='{{explode("|", $key)[1]}}'>
                                <a data-toggle='tab' class='active' href='#Tab{{explode("|", $key)[0]}}' onclick="get_odds('myTabContent__body{{explode("|", $key)[0]}}','{{explode("|", $val)[0]}}','{{explode("|", $val)[1]}}','{{explode("|", $key)[1]}}','','Win & Each Way');" >{{substr(explode("|", $val)[1],11,5)}} </a>
                            </li>
                        @else

                            <li class='{{"event-not-started"}}' id='{{explode("|", $key)[1]}}'>
                                <a data-toggle='tab' class='' href='#Tab{{explode("|", $key)[0]}}' onclick="get_odds('myTabContent__body{{explode("|", $key)[0]}}','{{explode("|", $val)[0]}}','{{explode("|", $val)[1]}}','{{explode("|", $key)[1]}}','','Win & Each Way');" >{{substr(explode("|", $val)[1],11,5)}} </a>
                            </li>
                        @endif
                    @endif
                    {{--*/
                        $curent_call_js .="\n\n$('#e{explode("|", $key)[1]} a').click();";
                        $curent_call_js .="\n\n$('#e{explode("|", $key)[1]}').addClass('active');";
                        $start_counters = $start_counters + 1;
                        $venue_nam1 = $venue_nam0;
                        $first_itr = $first_itr + 1;
                    /*--}}
                @else

                    <div class='panel panel-default' data-VenueId='{{explode("|", $key)[0]}}' id="{{explode("|", $key)[0]}}">
                        <div class='panel-heading'>
                            <h3 class='panel-title'><img class='Icon' src='{{URL::to('/')}}/assets/images/Horse.png'> {{explode("|", $val)[0]}}</h3>
                            <div class='panel-options pull-right'>
                                <i class='fa fa-chevron-down'></i> <i class='fa fa-times'></i>
                            </div>
                        </div>
                        <div id='event_{{explode("|", $key)[0]}}' class='panel-body collapse'>
                            <ul class='nav nav-pills nav-justified' id='myTab{{explode("|", $key)[0]}}'>
                            @if(strtotime(date("Y-m-d H:i:s")) > strtotime(explode("|", $val)[1]))
                                <li class='{{"event-started"}}' id='{{explode("|", $key)[1]}}'>
                                    <a data-toggle='tab' class='red' href='#Tab{{explode("|", $key)[0]}}' onclick="get_odds('myTabContent__body{{explode("|", $key)[0]}}','{{explode("|", $val)[0]}}','{{explode("|", $val)[1]}}','{{explode("|", $key)[1]}}',' red','Win & Each Way');" >{{substr(explode("|", $val)[1],11,5)}} </a>
                                </li>
                                {{--*/ $start_counters = 0; /*--}}
                            @else
                                @if($start_counters == 1)
                                {{--*/
                                    $activated = 1;
                                    $venue_id=explode("|", $key)[0];
                                    $event_id=explode("|", $key)[1];
                                    $venue_name=explode("|", $val)[0];
                                    $start_time=explode("|", $val)[1];
                                /*--}}
                                    <li class='{{"event-not-started"}} active' id='{{explode("|", $key)[1]}}'>
                                        <a data-toggle='tab' class='active' href='#Tab{{explode("|", $key)[0]}}' onclick="get_odds('myTabContent__body{{explode("|", $key)[0]}}','{{explode("|", $val)[0]}}','{{explode("|", $val)[1]}}','{{explode("|", $key)[1]}}','','Win & Each Way');" >{{substr(explode("|", $val)[1],11,5)}} </a>
                                    </li>
                                @else
                                    <li class='{{"event-not-started"}}' id='{{explode("|", $key)[1]}}'>
                                        <a data-toggle='tab' class='' href='#Tab{{explode("|", $key)[0]}}' onclick="get_odds('myTabContent__body{{explode("|", $key)[0]}}','{{explode("|", $val)[0]}}','{{explode("|", $val)[1]}}','{{explode("|", $key)[1]}}','','Win & Each Way');" >{{substr(explode("|", $val)[1],11,5)}} </a>
                                    </li>
                                @endif
                            @endif
                            {{--*/
                                $curent_call_js .="\n\n$('#e{explode("|", $key)[1]} a').click();";
                                $curent_call_js .="\n\n$('#e{explode("|", $key)[1]}').addClass('active');";
                                $start_counters = $start_counters + 1;
                                $venue_nam1 = $venue_nam0;
                                $first_itr = $first_itr + 1;
                                $i=$i+1;
                            /*--}}
                @endif

                <?php // echo $first_itr .' :: '.count($matches);  ?>

                @if($first_itr>count($matches))
                    </ul>
                    @if($activated==1)
                        <div class='tab-content' id='myTabContent_{{$venue_id}}'>
                            <div class='tab-pane active' id='Tab{{$venue_id}}'>
                                <div class='panel-body active' id='myTabContent__body{{$venue_id}}'>
                                {{--*/
                                    $eventsarr = array();
                                    $changed_odd_type = Session::get('changed_odd_type');
                                /*--}}
                                @if($changed_odd_type=='')
                                    {{--*/  $odd_type = 'Win & Each Way'; /*--}}
                                @else
                                    {{--*/ $odd_type = $changed_odd_type; /*--}}
                                @endif
                                    <script type="text/javascript">
                                        $(function() {
                                            var url="{{URL::to('/')}}/horse-racing/get_odd";
                                            var tab_id = "myTabContent__body{!!$venue_id!!}";
                                            var venueName = "{!!$venue_name!!}";
                                            var eventDate = "{!!$start_time!!}";
                                            var eventId = "{!!$event_id!!}";
                                            var oddType = "{!!HTML::decode($odd_type)!!}";
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
                                                        "aaSorting": [[ 4, "desc" ]]
                                                    });
                                                }
                                                else
                                                {
                                                    var oTable  = $("#table"+eventId).dataTable({
                                                        "bPaginate": false,
                                                        "oLanguage": {
                                                            "sSearch": "Filter records:",
                                                            "sSearchPlaceholder": "Search records"
                                                        },
                                                        "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 1, 6 ] } ]
                                                    });
                                                }
                                                convert(odds_converter);
                                                $("#table"+eventId).css("width","100%");
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    @endif
                        </div>
                    </div>
                    {{--*/
                    $first_itr = 1;
                   $start_counters = 1;
                     /*--}}
                @endif

                <?php unset($matches);?>
		    @endforeach
		    </div>
        </div>
    </div>
</section>

@endsection

@section('frontend.inline_scripts')

<script type="text/javascript">
    var search_visible = false;
    var is_expanded = false;
    var odds_converter = 0;
    function get_odds(tab_id,venueName,eventDate,eventId,tableType,oddType)
    {
        var url="{{URL::to('/')}}/horse-racing/get_odd";
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
                    "aaSorting": [[ 4, "desc" ]]
                });
            }
            else
            {
                var oTable  = $("#table"+eventId).dataTable({
                            "bPaginate": false,

                           "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 1, 6 ] } ]
                });
            }
            convert(odds_converter);
            $("#table"+eventId).css("width","100%");
            //$('<a href="/horse-racing-compare.php?event_id='+eventId+'&odd_type='+encodeURIComponent(oddType)+'" class="btn btn-primary">Compare</a>').appendTo('#table'+eventId+'_wrapper div.dataTables_filter');
        });
    }
    function get_events(tab_id)
    {
        // $('#'+tab_id).html("Loading..");
        var url="{{URL::to('/')}}/horse-racing/get_event";
        eventDate = $("#dp3").val();
        var odd_type = $("#bet_type option:selected").val();
        $('#venue_list').parent().append("<div class='loading'></div>");
        $('#venue_list').parent().css("position","relative");
        $(".alertify-buttons").css("display", "none");
        $.post(url, {event_date:eventDate, odd_type:odd_type}, function(result){
            $("#venue_list").html(result);
            $(".panel-heading").on("click", function(e){
                var $_target =  $(e.currentTarget);
                var $_panelBody = $_target.parent().find(".panel-body");
                if($_panelBody){
                    $_panelBody.collapse('toggle')
                }
            });
            $("li").removeClass("active");
            $(".nav-pills").each(function(){
                var li = $(this).find("li:not(.event-started):eq(0)");
                $(li).addClass("active");
                var a = $(li).find("a:eq(0)");
                $(a).click();
                $(a).addClass("active");
            });
            $('.loading').remove();
        });
        return false;
    }

    function get_bet_count(event_id,outcome_id,venue_name,name,odd_type)
    {
	    var bet_data = {
                event_id: event_id,
            outcome_id: outcome_id,
                venue_name:venue_name,
                name:name,
                odd_type:odd_type
        }
        var url="{{URL::to('/')}}/horse-racing/get_bet_click";
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            cache: false,
            success: function (json) {
                        $( '#' + json.name ).text( json.count );
            },
            data: bet_data
        });
    }
</script>

@endsection
