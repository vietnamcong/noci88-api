<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CalculateBonus::class,
        Commands\CalculateVip::class,
        Commands\ResetVipByMonth::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('calculate:bonus')
        //         ->daily('1:00')
        //         ->timezone('Asia/Ho_Chi_Minh');

        // $schedule->command('calculate:vip')
        //         ->daily('1:30')
        //         ->timezone('Asia/Ho_Chi_Minh');

        // $schedule->command('calculate:reset-vip')
        // 		->monthlyOn(1, '00:30')
        //         ->timezone('Asia/Ho_Chi_Minh');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}