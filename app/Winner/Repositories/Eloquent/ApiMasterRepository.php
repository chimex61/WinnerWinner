<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\ApiMasterInterface;

class ApiMasterRepository extends BaseRepository implements ApiMasterInterface{

    public function model() {
        return 'App\Models\ApiMaster';
    }
}