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
    {!! Minify::stylesheet(array(
                "/assets/css/bootstrap.min.css",
                "/assets/lib/c3js/c3.css",
                "/assets/lib/switchery/switchery.min.css",
                "/assets/lib/weather-icons/css/weather-icons.min.css",
                "/assets/lib/calendario/css/calendar.css",
                "/assets/lib/offlinejs/themes/offline-theme-dark.css",
                "/assets/css/bootstrap-override.css",
                "/assets/css/layout.css",
                "/assets/css/library.css",
                "/assets/css/style.css",
                "/assets/lib/alertify/alertify.core.css",
                "/assets/lib/alertify/alertify.default.css",
                "/assets/lib/datepicker/css/datepicker.css",
                "/assets/lib/datatables/bootstrap/3/dataTables.bootstrap.css",
                "/assets/lib/datatables/TableTools/css/TableTools.css",
                "/assets/lib/select2/select2-bootstrap.css",
                "/assets/lib/select2/select2.css",
                "/assets/css/sportsfont/stylesheet.css",
                "/assets/css/custom.css"
                ))
    !!}
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    

    {!! Minify::javascript(array(
                   '/assets/js/jquery-1.11.0.min.js',
                   '/assets/lib/jqueryui/js/jquery-ui-1.10.4.custom.min.js'

                   ))
       !!}



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


    {!! Minify::javascript(array(
                '/assets/js/bootstrap.min.js',
                '/assets/lib/easypiechart/jquery.easypiechart.min.js',
                '/assets/lib/switchery/switchery.min.js',
                '/assets/lib/customscroll/jquery.mCustomScrollbar.concat.min.js',
                '/assets/lib/jquery-easing/jquery.easing.1.3.js',
                '/assets/lib/calendario/js/jquery.calendario.js',
                '/assets/lib/offlinejs/offline.min.js',
                '/assets/lib/select2/select2.min.js',
                '/assets/lib/alertify/alertify.min.js',
                '/assets/lib/datatables/js/jquery.dataTables.min.js',
                '/assets/lib/datatables/TableTools/js/TableTools.min.js',
                '/assets/lib/datatables/bootstrap/3/dataTables.bootstrap.js',
                '/assets/lib/datepicker/js/bootstrap-datepicker.js',
                '/assets/lib/timepicker/js/bootstrap-timepicker.js',
                '/assets/js/script.js',
                '/assets/js/dashboard.js',
                '/assets/js/datatables.js'

                ))
    !!}

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
