<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BettingCount extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'betting_count';

    protected $fillable = [
        'label',
        #'bet_click',
        'event_id',
        'date_stamp',
        'bet_type',
        'api_id',
	'outcome_id'
    ];



}
