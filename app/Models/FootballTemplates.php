<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FootballTemplates extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'footballtemplates';

    protected $fillable = [
        'temp_id',
        'name',
        'sort',
        'type',
        'status',
        'grouped',
        'update_dat',
        'type_id',
        'api_id'
    ];




}
