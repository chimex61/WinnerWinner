<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\OutcomeMasterInterface;

class OutcomeMasterRepository extends BaseRepository implements OutcomeMasterInterface{

    public function model() {
        return 'App\Models\OutcomeMaster';
    }
}