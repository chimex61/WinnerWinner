<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'eventmaster';

    protected $fillable = [
        'event_name',
        'start_date',
        #'bet_till_date',
        #'state',
        'venue_id',
        'event_id',
        #'other',
        'api_id',
        'date_stamp',
        'g_g_id',
        'game_id'
        #'type_id',
        #'master_event'
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

    public function venue_Master(){
        return $this->belongsTo('App\Models\VenueMaster', 'venue_id');
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
    public function outcomeMasters(){
        return $this->hasMany('App\Models\OutcomeMaster');
    }

}
