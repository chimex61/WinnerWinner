<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameGroupMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gamegroupmaster';

    protected $fillable = [
        'game_id',
        'g_g_name',
        'g_g_id',
        'active_flag',
        'api_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function apiMaster(){
        return $this->belongsTo('App\Models\ApiMaster', 'api_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function gameMaster(){
        return $this->belongsTo('App\Models\GameMaster', 'game_id');
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
