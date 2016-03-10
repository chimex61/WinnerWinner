<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'apimaster';

    protected $fillable = [
        'base_url',
        'auth',
        'name',
        'icon',
        'logo',
        'no_bets',
        'free_bet',
        'sign_up'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gameGroupMasters(){
        return $this->hasMany('App\Models\GameGroupMaster');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function venueMasters(){
        return $this->hasMany('App\Models\VenueMaster');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventMasters(){
        return $this->hasMany('App\Models\EventMaster');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outcomeMasters(){
        return $this->hasMany('App\Models\OutcomeMaster');
    }

}
