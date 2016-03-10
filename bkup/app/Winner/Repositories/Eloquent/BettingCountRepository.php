<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\BettingCountInterface;

class BettingCountRepository extends BaseRepository implements BettingCountInterface{

    public function model() {
        return 'App\Models\BettingCount';
    }
}