<?php

Route::group(['prefix' => '', 'namespace' => 'Frontend'], function($name){

    Route::get('/', 'HomeController@index');
    Route::POST('/horse-racing/odds-selector', ['uses' => 'HorseRacingController@oddsSelector']);
    Route::get('/horse-racing', ['uses' => 'HorseRacingController@index']);
    Route::POST('/horse-racing/get_odd', ['uses' => 'HorseRacingController@getOdd']);
    Route::POST('/horse-racing/get_event', ['uses' => 'HorseRacingController@getEvent']);
    Route::POST('/horse-racing/get_bet_click', ['uses' => 'HorseRacingController@getBetClick']);
    Route::GET('/horse-racing/full-odds/{apiID}/{venue_name}/{event_id}/{event_date}/{label}', ['uses' => 'HorseRacingController@getFullOdds']);

});