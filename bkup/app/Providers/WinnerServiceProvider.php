<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WinnerServiceProvider extends ServiceProvider {

    protected $repositories = [
                        'User','GameMaster','GameGroupMaster','VenueMaster','EventMaster','ApiMaster','OutcomeMaster', 'BettingCount'
                        ];

    public function register(){
    	//Loops through all repositories and binds them with their Eloquent implementation
        array_walk($this->repositories, function($repository){
            $this->app->bind('App\Winner\Repositories\Contracts\\'.$repository.'Interface',
                'App\Winner\Repositories\Eloquent\\'.$repository.'Repository'
            );
        });
    }
} 