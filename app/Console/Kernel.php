<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
      #  'App\Console\Commands\CoralData',
       'App\Console\Commands\OutcomeCoralData',
      #  'App\Console\Commands\SkyBetData',
        'App\Console\Commands\OutcomeSkyBetData',
      #  'App\Console\Commands\UnibetData',
        'App\Console\Commands\OutcomeUnibetData',
      #  'App\Console\Commands\WilliamHillsData',
        'App\Console\Commands\OutcomeWilliamHillsData',
        'App\Console\Commands\BetFredData',
        'App\Console\Commands\OutcomeBetFredData',
        'App\Console\Commands\BetFairData',
        'App\Console\Commands\OutcomeBetFairData',
        'App\Console\Commands\getGameEvent',
       # 'App\Console\Commands\getOutcomes',
        'App\Console\Commands\DeleteAPIData',
        'App\Console\Commands\DeleteEventData',
        #'App\Console\Commands\RemoveDuplicateAPIData',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{

          #  $schedule->command('winner:getSkyBetData')->everyFiveMinutes();
          #  $schedule->command('winner:getUniBetData')->everyFiveMinutes();
          #  $schedule->command('winner:getWilliamHillsData')->everyFiveMinutes();
          #  $schedule->command('winner:getBetFredData')->everyFiveMinutes();
          #  $schedule->command('winner:getBetFairData')->everyFiveMinutes();
          #  $schedule->command('winner:getCoralData')->everyFiveMinutes();
          #  $schedule->command('winner:getOutcomeCoralData')->everyFiveMinutes();
          #  $schedule->command('winner:getOutcomeSkyBetData')->everyTenMinutes();
          #  $schedule->command('winner:getOutcomeUniBetData')->everyFiveMinutes();
          #  $schedule->command('winner:getOutcomeWilliamHillsData')->everyTenMinutes();
          #  $schedule->command('winner:getOutcomeBetFredData')->everyFiveMinutes();

       # $schedule->command('winner:ClearEventData')->cron('30 * * * *')->then(function() {
        $schedule->command('winner:getEvent')->cron('10 * * * *');
        $schedule->command('winner:getBetFredData')->cron('10 * * * *');
        $schedule->command('winner:getBetFairData')->cron('10 * * * *');
       # });
        /*
        $schedule->command('winner:ClearAPIData')->cron('30 * * * *')->then(function() {
            $this->call('winner:getOutcomeSkyBetData');
            $this->call('winner:getOutcomeWilliamHillsData');
            $this->call('winner:getOutcomeBetFredData');
            $this->call('winner:getOutcomeCoralData');
            $this->call('winner:getOutcomeUniBetData');
            #$this->call('winner:getOutcome');
        });
*/

        $schedule->command('winner:getOutcomeSkyBetData')->cron('15 * * * *');
        $schedule->command('winner:getOutcomeUniBetData')->cron('15 * * * *');
        $schedule->command('winner:getOutcomeWilliamHillsData')->cron('15 * * * *');
        $schedule->command('winner:getOutcomeBetFredData')->cron('15 * * * *');
        #$schedule->command('winner:getOutcomeBetFairData')->cron('15 * * * *');
        $schedule->command('winner:getOutcomeCoralData')->cron('15 * * * *');

        #   $schedule->command('winner:ClearDuplicateAPIData')->everyTenMinutes();
        #  $schedule->command('winner:getOutcome')->cron('30 * * * *')->withoutOverlapping();

        $schedule->command('winner:getEvent')->hourly()->sendOutputTo('logs/events-data.txt');
        $schedule->command('winner:getBetFredData')->hourly()->sendOutputTo('logs/betfred-data.txt');
        #$schedule->command('winner:getOutcome')->hourly()->sendOutputTo('outcome-data.txt');
        $schedule->command('winner:getOutcomeSkyBetData')->hourly()->sendOutputTo('logs/outcome-Skybet-data.txt');
        $schedule->command('winner:getOutcomeUniBetData')->hourly()->sendOutputTo('logs/outcome-unibet-data.txt');
        $schedule->command('winner:getOutcomeWilliamHillsData')->hourly()->sendOutputTo('logs/outcome-williamhills-data.txt');
        $schedule->command('winner:getOutcomeBetFredData')->hourly()->sendOutputTo('logs/outcome-betfred-data.txt');
        #$schedule->command('winner:getOutcomeBetFairData')->hourly()->sendOutputTo('logs/outcome-betfair-data.txt');
        $schedule->command('winner:getOutcomeCoralData')->hourly()->sendOutputTo('logs/outcome-coral-data.txt');
	}

}
