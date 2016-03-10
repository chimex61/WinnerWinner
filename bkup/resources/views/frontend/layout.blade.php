<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<!--<meta http-equiv="X-UA-Compatible" content="IE=edge">-->
	<title>Dashboard | Winner Winner</title>
	<meta name="_token" content="{!! csrf_token() !!}"/>
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://flatter.cloudtub.com/image/touch/apple-touch-icon-144x144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://flatter.cloudtub.com/image/touch/apple-touch-icon-114x114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://flatter.cloudtub.com/image/touch/apple-touch-icon-72x72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="http://flatter.cloudtub.com/image/touch/apple-touch-icon-57x57-precomposed.png">
	<link rel="shortcut icon" href="http://flatter.cloudtub.com/image/touch/apple-touch-icon.png">
	<!-- BEGIN GLOBAL MANDATORY STYLES -->
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/css/bootstrap.min.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/c3js/c3.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/switchery/switchery.min.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/weather-icons/css/weather-icons.min.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/calendario/css/calendar.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/offlinejs/themes/offline-theme-dark.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/css/bootstrap-override.css" type="text/css"/>
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/css/layout.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/css/library.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/css/style.css" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/alertify/alertify.core.css" type="text/css"/>
    <link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/alertify/alertify.default.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/datepicker/css/datepicker.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/datatables/bootstrap/3/dataTables.bootstrap.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/datatables/TableTools/css/TableTools.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/select2/select2-bootstrap.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/lib/select2/select2.css" type="text/css"/>
	<link rel="stylesheet" href="{{URL::to('/')}}/assets/css/sportsfont/stylesheet.css" type="text/css"/>
    <link href="{{URL::to('/')}}/assets/css/custom.css" rel="stylesheet" type="text/css"/>
<!--
	<script type="text/javascript" src="//w.sharethis.com/button/buttons.js"></script>
	<script type="text/javascript">stLight.options({publisher: "cccd8a8f-254a-482c-b259-4563a3524808", doNotHash: false, doNotCopy: true, hashAddressBar: false});</script>
-->
<script type="text/javascript" src="{{URL::to('/')}}/assets/js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/jqueryui/js/jquery-ui-1.10.4.custom.min.js"></script>
    @yield('frontend.inline_styles')
</head>
<body id="body" onLoad="show_clock()">
    <!-- Header Start-->
    <header class="navbar main-header">
        @include('frontend.partials.header')
    </header>
    <!-- Header End-->
    <!-- Sidebar Start-->
    <div class="sidebar sidebar-left">
        @include('frontend.partials.sidebar')
    </div>
    <!-- Sidebar End-->
    <!-- BEGIN ACTUAL CONTENT -->

        @yield('frontend.content')

    <!-- END ACTUAL CONTENT -->


    <script type="text/javascript" src="{{URL::to('/')}}/assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/easypiechart/jquery.easypiechart.min.js"></script>
     <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/switchery/switchery.min.js"></script>
 <!--   <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/c3js/d3.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/c3js/c3.min.js"></script>-->

    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/customscroll/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/jquery-easing/jquery.easing.1.3.js"></script>
  <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/calendario/js/jquery.calendario.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/offlinejs/offline.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/select2/select2.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/alertify/alertify.min.js"></script>
    <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/datatables/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/datatables/TableTools/js/TableTools.min.js"></script>
        <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/datatables/bootstrap/3/dataTables.bootstrap.js"></script>
         <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/datepicker/js/bootstrap-datepicker.js"></script>
            <script type="text/javascript" src="{{URL::to('/')}}/assets/lib/timepicker/js/bootstrap-timepicker.js"></script>



    <script type="text/javascript" src="{{URL::to('/')}}/assets/js/script.js"></script>
   <script type="text/javascript" src="{{URL::to('/')}}/assets/js/dashboard.js"></script>


    <script type="text/javascript" src="{{URL::to('/')}}/assets/js/datatables.js"></script>

    <script>
        function calculate()
        {
            if($.isNumeric($("#calc1").val()) && $.isNumeric($("#calc2").val()))
            {
                var temp = $("#calc1").val() / $("#calc2").val();
                $("#calc_result").val(temp.toFixed(2));
            }
            else
            {
                $("#calc_result").val(0);
            }
        }

        function convert(val)

        {

        	var odd_type = "";

        	var url = "/horse-racing/odds-selector";

        	if(val==1)

        	{

        		odd_type = "decimal";

        		$(".odds_decimal").show();

        		$(".odds_fraction").hide();

        	}

        	else

        	{

        		odd_type = "fraction";

        		$(".odds_decimal").hide();

        		$(".odds_fraction").show();

        	}

        	$.post(url, {set_odd_type:true, odd_type:odd_type}, function(result){});

        }
    </script>
    @yield('frontend.inline_scripts')
    <script type="text/javascript">
    $.ajaxSetup({
       headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
    });
    </script>
</body>
</html>
