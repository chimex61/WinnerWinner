<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetType extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bettype';

    protected $fillable = [
        'from',
        'to',
        'api_id'
    ];




}
