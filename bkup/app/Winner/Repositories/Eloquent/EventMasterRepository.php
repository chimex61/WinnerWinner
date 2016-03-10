<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\EventMasterInterface;

class EventMasterRepository extends BaseRepository implements EventMasterInterface{

    public function model() {
        return 'App\Models\EventMaster';
    }
}