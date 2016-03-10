<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gamemaster';

    protected $fillable = [
        'game_name',
        'title',
        'link',
        'active'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eventMasters(){
        return $this->hasMany('App\Models\EventMaster');
    }

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
    public function outcomeMasters(){
        return $this->hasMany('App\Models\OutcomeMaster');
    }


}
