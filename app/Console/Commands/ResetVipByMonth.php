<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Level;
use DB, Log;
use Illuminate\Support\Collection;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Date;

class ResetVipByMonth extends Command
{
	use ResponseTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:reset-vip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset vip for users by month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		// Update the user's money, ml_money, fs_money, old_money, is_withdraw, bonus_rate, level and level_name in the database
		User::whereIn('type_user', [2, 3])
			->where('is_active', 1)
			->where('status', 1)
			->whereNull('deleted_at')
			->update([
				'fs_money' => DB::raw('fs_money + ml_money'),
				'old_money' => DB::raw('old_money + money'),
				'is_withdraw' => 1,
				'bonus_rate' => 0,
				'level' => 0,
				'level_name' => 'Vip 0',
				'money' => 0,
				'ml_money' => 0,
			]);

		// Send notify to telegram
		$this->sendMessageTelegram('✅ Reset vip thành công!');

        $this->info('Vip reset completed.');
    }
}
