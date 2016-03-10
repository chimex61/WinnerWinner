<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Winner\Repositories\Contracts\GameMasterInterface as GameMaster;

class HomeController extends Controller {


    private $gameMaster;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(GameMaster $gameMaster)
	{
		//$this->middleware('auth');
        $this->gameMaster = $gameMaster;
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
        $gameMasters=$this->gameMaster->where('active',1,'=')->get();
		return view('frontend.home', compact('gameMasters'));
	}

}
