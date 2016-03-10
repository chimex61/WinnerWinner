<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutcomeParticipantMaster extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'outcomeparticipantmaster';

    protected $fillable = [
        'label',
        'real_id',
        'api_id',
        'flag'
    ];




}
