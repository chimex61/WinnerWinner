<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'venuemaster';

    protected $fillable = [
        'venue_name',
        'venue_id',
        #'master_venue',
        'g_g_id',
        'game_id',
        'date_stamp',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function gameGroupMaster(){
        return $this->belongsTo('App\Models\GameGroupMaster', 'g_g_id');
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
