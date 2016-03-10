<?php namespace App\Winner\Repositories\Eloquent;

use App\Winner\Repositories\Contracts\UserInterface;

class UserRepository extends BaseRepository implements UserInterface{

    public function model() {
        return 'App\Models\User';
    }
}