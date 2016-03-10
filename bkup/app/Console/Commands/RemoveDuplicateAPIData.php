<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Winner\Repositories\Contracts\VenueMasterInterface as VenueMaster;
use App\Winner\Repositories\Contracts\EventMasterInterface as EventMaster;
use App\Winner\Repositories\Contracts\GameGroupMasterInterface as GameGroupMaster;
use App\Winner\Repositories\Contracts\ApiMasterInterface as ApiMaster;
use App\Winner\Repositories\Contracts\OutcomeMasterInterface as OutcomeMaster;
use DB;

class RemoveDuplicateAPIData extends Command {

    var $api_id;
    var $base_api_url;
    var $response_formate;
    var $api_auth;
    var $call_url;
    var $response;
    var $json_data;
    var $xml_data;
    var $outcome_array;
    var $sql_array;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'winner:ClearDuplicateAPIData';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear Duplicate Data';

    private $venueMaster;
    private $eventMaster;
    private $gameGroupMaster;
    private $apiMaster;
    private $outcomeMaster;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(VenueMaster $venueMaster, EventMaster $eventMaster, GameGroupMaster $gameGroupMaster, ApiMaster $apiMaster, OutcomeMaster $outcomeMaster)
	{
		parent::__construct();
        $this->eventMaster = $eventMaster;
        $this->venueMaster = $venueMaster;
        $this->gameGroupMaster = $gameGroupMaster;
        $this->apiMaster = $apiMaster;
        $this->outcomeMaster = $outcomeMaster;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $today_stamp = strtotime("0:00:00");
        $results = DB::table('outcomemaster')
            ->select(DB::raw('id, count(*) as dup_entry, bet_type'))
            ->where('date_stamp','=',$today_stamp)
            ->groupBy('bet_type')
            ->having('dup_entry', '>', 1)
            ->get();

        foreach($results as $result){
           // dd($result);
            $this->outcomeMaster->where('bet_type',$result->bet_type,'=')->where('id',$result->id,'<>')->delete();
        }
        $this->info('Removed Duplicate Entry');
	}



	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
