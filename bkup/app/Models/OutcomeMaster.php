<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutcomeMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'outcomemaster';

    protected $fillable = [
        'label',
        'bet_type',
        'odd',
        'odd_fractional',
        'event_id',
        #'add_date',
        'other',
        'game_id',
        'g_g_id',
        'date_stamp',
        'api_id',
        #'last_update_ot',
        #'event_start_date',
        'price_direction',
        'outcome_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function eventMaster(){
        return $this->belongsTo('App\Models\EventMaster', 'event_id');
    }
    /**
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */

    public function apiMaster(){
        return $this->belongsTo('App\Models\ApiMaster', 'api_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function gameGroupMaster(){
        return $this->belongsTo('App\Models\GameGroupMaster', 'g_g_id');
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

    public function venueMaster(){
        return $this->belongsTo('App\Models\VenueMaster', 'venue_id');
    }

}
