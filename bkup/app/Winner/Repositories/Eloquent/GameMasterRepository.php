<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\GameMasterInterface;

class GameMasterRepository extends BaseRepository implements GameMasterInterface{

    public function model() {
        return 'App\Models\GameMaster';
    }
}