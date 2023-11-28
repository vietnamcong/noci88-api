<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Level;
use Log;
use Illuminate\Support\Collection;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Date;

class CalculateVip extends Command
{
	use ResponseTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:vip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate vip for users';

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
        $users = User::selectRaw('id, username, type_user, agent_id, money, fs_money, ml_money, total_money, old_money')
				->whereIn('type_user', [2, 3])
				->where('is_active', 1)
				->where('status', 1)
				->whereNull('deleted_at')
				->get();
		
		$levels = Level::selectRaw('level, level_name, withdrawal_today, deposit_money, level_bonus, day_bonus, week_bonus, month_bonus, year_bonus, credit_bonus, levelup_type')->get();

		// Calculate the sum of money for each user
		$usersLevels = [];
		$agents = [];
		$members = [];
		foreach ($users as $user) {
			$data = $this->getFinalLevel(collect($levels), $user->money);
			if ($data != null) {
				$usersLevels[$user->id][0] = $data[0];
				$usersLevels[$user->id][1] = $data[1];
				$usersLevels[$user->id][2] = $data[2];

				if ($user->type_user == 2) {
					$agents[$user->id][0] = $user->username;
					$agents[$user->id][1] = $data[1];
					$agents[$user->id][2] = $user->money;
				}

				if ($user->type_user == 3) {
					$members[$user->id][0] = $user->username;
					$members[$user->id][1] = $data[1];
					$members[$user->id][2] = $user->money;
				}
			}
		}

		$userIdsToUpdate = array_keys($usersLevels);
		if (!empty($userIdsToUpdate)) {
			// Update the user's level, level_name and is_withdraw in the database
			User::whereIn('id', $userIdsToUpdate)
					->update([
						'level' => \DB::raw('CASE id ' . implode(' ', array_map(function ($id) use ($usersLevels) {
							return "WHEN $id THEN {$usersLevels[$id][0]}";
						}, $userIdsToUpdate)) . ' END'),
						'level_name' => \DB::raw('CASE id ' . implode(' ', array_map(function ($id) use ($usersLevels) {
							return "WHEN $id THEN '{$usersLevels[$id][1]}'";
						}, $userIdsToUpdate)) . ' END'),
						// 'is_withdraw' => \DB::raw('CASE id ' . implode(' ', array_map(function ($id) use ($usersLevels) {
						// 	return "WHEN $id THEN {$usersLevels[$id][2]}";
						// }, $userIdsToUpdate)) . ' END'),
					]);
		}

		// Send notify to telegram
		$this->notifyTelegram($agents, $members);

        $this->info('Vip calculation completed.');
    }

	/**
	 * Hàm lấy giá trị cuối cùng từ collection dựa trên giá trị deposit_money.
	 *
	 * @param Collection $levelCollection
	 * @param int $deposit_money
	 * @return array|null
	 */
	private function getFinalLevel(Collection $levelCollection, int $totalMoney): ?array
	{
		$filteredLevels = $levelCollection->filter(function ($levelItem) use ($totalMoney) {
			return $totalMoney >= $levelItem->deposit_money;
		});

		$lastLevel = $filteredLevels->last();

		return $lastLevel ? [(int) $lastLevel->level, $lastLevel->level_name, (int) $lastLevel->withdrawal_today] : null;
	}
	

	public function notifyTelegram($agents, $members)
	{
		$currentDate = Date::now();
		$formattedDate = $currentDate->format('d-m-Y H:i');

		$messages = '⏰ '.$formattedDate.'
		
		VIP CỦA ĐẠI LÝ
		
		';

		$agentStt = 0;
		foreach ($agents as $agent_id => $agent) {
			if ($agentStt > 0) {
				$messages .= '
			
				';
			}

			$messages .= '➡️ Tài khoản: '.$agent[0].'
			user_id: '.$agent_id.'
			🏆 Tổng nạp: '.formatCurrencyVND($agent[2]).'
			Vip: '.$agent[1];

			$agentStt += 1;
		}

		$messages .= '
		
		VIP CỦA MEMBER
		
		';

		$memberStt = 0;
		foreach ($members as $member_id => $member) {
			if ($memberStt > 0) {
				$messages .= '
			
				';
			}

			$messages .= '➡️ Tài khoản: '.$member[0].'
			user_id: '.$member_id.'
			🏆 Tổng nạp: '.formatCurrencyVND($member[2]).'
			Vip: '.$member[1];

			$memberStt += 1;
		}

		$this->sendMessageTelegram($messages);
	}
}
