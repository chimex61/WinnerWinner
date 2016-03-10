<div class="content">
    <a class="navbar-brand" href="{{URL::to('/')}}">
        <img class="MenuLogo" src="{{URL::to('/')}}/assets/images/Logo.png" alt="Winner Winner"/>
    </a>
    <div class="tab-content sidetabs">
	    <div class="tab-pane active" id="home">
            <div class="col-md-12 SignIn">
			    <h2>Sports</h2>
                <hr>
			    <ul class="sidemenu">
                @foreach($gameMasters as $gameMaster)
                    <li>
                        <a href="{{URL::to('/')}}/{!!$gameMaster->link!!}">
                            <span class="name">{!!$gameMaster->title!!}</span>
                        </a>
                    </li>
                 @endforeach
                </ul>
                <div class="btn-group ShowOddsAs" data-toggle="buttons">
                    <h2>Display Odds</h2>
                    <hr>

                    <label class="btn btn-success  @if(get_odd_type()=="fraction") {{'active'}} @endif" onClick="convert(0);">
                        <input type="radio" name="options" id="option1" @if(get_odd_type()=="fraction") {{'selected'}} @endif /> Fractions
                    </label>
                    <label class="btn btn-success  @if(get_odd_type()!="fraction") {{'active'}}@endif" onClick="convert(1);">
                        <input type="radio" name="options" id="option2" @if(get_odd_type()!="fraction") {{'selected'}} @endif> Decimals
                    </label>
                </div>
                <h2>Fraction to Decimal Converter</h2>
                <hr>
                <div class="form-group">
                    <input type="text" class="form-control success" id="calc1" maxlength="3" style="width:55px;display:inline" onChange="calculate();"> / <input type="text" class="form-control success" id="calc2" maxlength="3" style="width:55px;display:inline" onChange="calculate();"> = 	<input type="text" readonly class="form-control success" id="calc_result" maxlength="3" style="width:60px;display:inline">
                </div>
                <!-- Inline Form -->
            </div>
        </div>
    </div>
</div>
