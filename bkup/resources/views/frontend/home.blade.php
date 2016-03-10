@extends('frontend.layout')

@section('frontend.inline_styles')
@endsection

@section('frontend.content')
<section id="main-wrapper">
<h3 class="subtitle">Dashboard</h3>
<hr/>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-trophy"></i> Top Odds Table Bookmakers</h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i>
                <i class="fa fa-times"></i>

            </div>
        </div>
        <div class="panel-body">
            <table id="table17877156" class="table table-striped BetTable table-hover table-bordered dataTable">
                <thead>
                    <tr class='SortingPart'>

                        <th>Bookmaker</th>
                        <th>Best Odds</th>
                        <th>Free Bets</th>
                        <th>Claim</th>

                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-star Icon"></i>Winner Winner's Tips</h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i>
                <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="panel-body">
            <table id="table17877156" class="table table-striped BetTable table-hover table-bordered dataTable">
                <thead>
                    <tr class='SortingPart'>
                        <th>Bookmaker</th>
                        <th>Best Odds</th>
                        <th>Free Bets</th>
                        <th>Claim</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><img class="Icon" src="{{URL::to('/')}}/assets/images/Horse.png"/> Racing - In Play</h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i>
                <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="panel-body">
            <table id="table17877156" class="table table-striped BetTable table-hover table-bordered dataTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Jocky</th>
                        <th>Bet Type</th>
                        <th>Odds</th>
                        <th>Watch</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-futbol-o"></i> Football - In Play</h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i>
                <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="panel-body">
            <table id="table17877156" class="table table-striped BetTable table-hover table-bordered dataTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Jocky</th>
                        <th>Bet Type</th>
                        <th>Odds</th>
                        <th>Watch</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><img class="Icon" src="{{URL::to('/')}}/assets/images/Horse.png"/> Racing - Most Popular</h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i>
                <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="panel-body">
            <table id="table17877156" class="table table-striped BetTable table-hover table-bordered dataTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Jocky</th>
                        <th>Bet Type</th>
                        <th>Odds</th>
                        <th>Watch</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-futbol-o"></i> Football - Most Popular</h3>
            <div class="panel-options pull-right">
                <i class="fa fa-chevron-down"></i>
                <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="panel-body">
            <table id="table17877156" class="table table-striped BetTable table-hover table-bordered dataTable">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Time</th>
                        <th>Home</th>
                        <th>Draw</th>
                        <th>Away</th>
                        <th>Watch</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
</section>
@endsection

@section('frontend.inline_scripts')
@endsection





