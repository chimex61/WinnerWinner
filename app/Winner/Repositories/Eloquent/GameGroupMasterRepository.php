<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\GameGroupMasterInterface;

class GameGroupMasterRepository extends BaseRepository implements GameGroupMasterInterface{

    public function model() {
        return 'App\Models\GameGroupMaster';
    }
}