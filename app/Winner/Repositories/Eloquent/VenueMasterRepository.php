<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\VenueMasterInterface;

class VenueMasterRepository extends BaseRepository implements VenueMasterInterface{

    public function model() {
        return 'App\Models\VenueMaster';
    }
}