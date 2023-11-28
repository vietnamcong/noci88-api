<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\Setting;
use Illuminate\Support\Collection;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Date;
use Log;

class CalculateBonus extends Command
{
	use ResponseTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate bonus points for users';

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
		$users = User::selectRaw('id, username, type_user, agent_id, money, fs_money, ml_money, total_money')
				->with([
					'invites' => function ($query) {
						$query->with([
							'member' => function ($subQuery) {
								$subQuery->selectRaw('id, username, agent_id, money, fs_money, ml_money, total_money');
							},
							'member.invites' => function ($subQuery) {
								$subQuery->with([
									'member' => function ($nestedSubQuery) {
										$nestedSubQuery->selectRaw('id, username, agent_id, money, fs_money, ml_money, total_money')
										->withCount('invites');
									},
									'member.invites' => function ($subQuery) {
										$subQuery->with(['member' => function ($nestedSubQuery) {
											$nestedSubQuery->selectRaw('id, username, agent_id, money, fs_money, ml_money, total_money')
											->withCount('invites');
										}]);
									},
								]);
							},
						])
						->whereHas('member', function ($subQuery) {
							$subQuery->whereIn('type_user', [2, 3])->where('is_active', 1)->where('status', 1);
						})
						->whereMonth('created_at', date('Y-m-d'))
						->where('status', 1)
						->selectRaw('id, user_id, invite_id');
					}
				])
				->withCount('invites')
				// ->having('invites_count', '>', 0)
				->whereIn('type_user', [2, 3])
				->where('is_active', 1)
				->where('status', 1)
				->whereNull('deleted_at')
				->get();

		$rangeBonusAgent = UserBonus::where('status', 1)->where('type_user', 2)->whereNull('deleted_at')->get(['price', 'bonus']);
		$rangeBonusMember = UserBonus::where('status', 1)->where('type_user', 3)->whereNull('deleted_at')->get(['price', 'bonus']);

		// Calculate the sum of total_money for each user
		$usersTotalMoneySums = [];
		$agentTotalMoneySums = [];
		$memberTotalMoneySums = [];
		foreach ($users as $user) {
			$totalMoney = $this->calculateTotalMoney($user);

			if ($totalMoney > 0) {
				$usersTotalMoneySums[$user->id][0] = $totalMoney;

				if ($user->type_user == 2 && $totalMoney > 0) {
					$agentTotalMoneySums[$user->id][0] = $totalMoney;

					$usersTotalMoneySums[$user->id][1] = $this->getFinalBonus(collect($rangeBonusAgent), $totalMoney);
					$agentTotalMoneySums[$user->id][1] = $this->getFinalBonus(collect($rangeBonusAgent), $totalMoney);

					$agentTotalMoneySums[$user->id][2] = $user->username;
					$agentTotalMoneySums[$user->id][3] = $user->type_user;
				}
			
				if ($user->type_user == 3 && $totalMoney > 0) {
					$memberTotalMoneySums[$user->id][0] = $totalMoney;

					$usersTotalMoneySums[$user->id][1] = $this->getFinalBonus(collect($rangeBonusMember), $totalMoney);
					$memberTotalMoneySums[$user->id][1] = $this->getFinalBonus(collect($rangeBonusMember), $totalMoney);

					$memberTotalMoneySums[$user->id][2] = $user->username;
					$memberTotalMoneySums[$user->id][3] = $user->type_user;
				}

				$usersTotalMoneySums[$user->id][2] = $user->username;
				$usersTotalMoneySums[$user->id][3] = $user->type_user;
			}
		}

		$userIdsToUpdate = array_keys($usersTotalMoneySums);
		if (!empty($userIdsToUpdate)) {
			// Update the user's bonus_rate and ml_money in the database
			User::whereIn('id', $userIdsToUpdate)
				->update([
					'ml_money' => \DB::raw('CASE id ' . implode(' ', array_map(function ($id) use ($usersTotalMoneySums) {
						return "WHEN $id THEN {$usersTotalMoneySums[$id][0]}";
					}, $userIdsToUpdate)) . ' END'),
					'bonus_rate' => \DB::raw('CASE id ' . implode(' ', array_map(function ($id) use ($usersTotalMoneySums) {
						return "WHEN $id THEN {$usersTotalMoneySums[$id][1]}";
					}, $userIdsToUpdate)) . ' END'),
				]);
		}

		// Send notify to telegram
		$this->notifyTelegram($agentTotalMoneySums, $memberTotalMoneySums);

        $this->info('Bonus calculation completed.');
    }

	function calculateTotalMoney($user)
	{
		$totalMoneySum = 0;

		// Đại lý
		if ($user->type_user == 2) {
			$totalMoneySum = $this->calculateTotalMoneyAgent($user);
		}

		// Member
		if ($user->type_user == 3) {
			$totalMoneySum = $this->calculateTotalMoneyMember($user);
		}

		return $totalMoneySum;
	}

	function calculateTotalMoneyAgent($user)
	{
		$rateBonus1 = Setting::where('key', 'agent_bonus_1')->value('value');
		$rateBonus1 = isset($rateBonus1) ? (int) $rateBonus1 : 10;
		$rateBonus2 = Setting::where('key', 'agent_bonus_2')->value('value');
		$rateBonus2 = isset($rateBonus2) ? (int) $rateBonus2 : 10;
		$rateBonus3 = Setting::where('key', 'agent_bonus_3')->value('value');
		$rateBonus3 = isset($rateBonus3) ? (int) $rateBonus3 : 10;
		$rateBonus4 = Setting::where('key', 'agent_bonus_4')->value('value');
		$rateBonus4 = isset($rateBonus4) ? (int) $rateBonus4 : 10;
		$totalMoney = 0;

		$totalInvites = 0;
		foreach ($user->invites as $invite) {
			$totalMoneyUser = (int) $invite->member->money;

			$totalMoneyInvite = 0;
			foreach ($invite->member->invites as $nestedInvites) {
				$totalMoneyInviteUser = (int) $nestedInvites->member->money;

				$totalMoneyNested = 0;
				foreach ($nestedInvites->member->invites as $nestedInvite) {
					$totalMoneyNested += (int) $nestedInvite->member->money;
				}

				$totalMoneyInvite += ($totalMoneyInviteUser + ($totalMoneyNested * $rateBonus4 / 100)) * $rateBonus3 / 100;
			}

			$totalInvites += ($totalMoneyUser + $totalMoneyInvite ) * $rateBonus2 / 100;
		}

		$totalMoney = ((int) $user->money + $totalInvites) * $rateBonus1 / 100;
		return round($totalMoney, 2);
	}

	function calculateTotalMoneyMember($user)
	{
		$rateBonus = Setting::where('key', 'member_bonus')->value('value');
		$rateBonus = isset($rateBonus) ? (int) $rateBonus : 10;
		$totalMoney = 0;

		$totalInvites = 0;
		foreach ($user->invites as $invite) {
			$totalInvites += (int) $invite->member->money;
		}

		$totalMoney = ((int) $user->money + $totalInvites) * $rateBonus / 100;
		return $totalMoney;
	}

	/**
	 * Hàm lấy giá trị bonus cuối cùng từ collection dựa trên giá trị totalMoney.
	 *
	 * @param Collection $bonusCollection
	 * @param int $totalMoney
	 * @return int|null
	 */
	private function getFinalBonus(Collection $bonusCollection, int $totalMoney): ?int
	{
		$filteredBonuses = $bonusCollection->filter(function ($bonusItem) use ($totalMoney) {
			return $totalMoney >= $bonusItem->price;
		});

		$lastBonus = $filteredBonuses->last();

		return $lastBonus ? $lastBonus->bonus : 0;
	}

	public function notifyTelegram($agents, $members)
	{
		$currentDate = Date::now();
		$formattedDate = $currentDate->format('d-m-Y H:i');

		$messages = '⏰ '.$formattedDate.'
		
		TỶ LỆ BONUS GIỚI THIỆU CỦA ĐẠI LÝ
		
		';

		$agentStt = 0;
		foreach ($agents as $agent_id => $agent) {
			if ($agentStt > 0) {
				$messages .= '
			
				';
			}

			$messages .= '➡️ Tài khoản: '.$agent[2].'
			user_id: '.$agent_id.'
			🏆 Tổng lợi nhuận: '.formatCurrencyVND($agent[0]).'
			Tỷ lệ: '.$agent[1].'%';

			$agentStt += 1;
		}

		$messages .= '
		
		TỶ LỆ BONUS GIỚI THIỆU CỦA MEMBER
		
		';

		$memberStt = 0;
		foreach ($members as $member_id => $member) {
			if ($memberStt > 0) {
				$messages .= '
			
				';
			}

			$messages .= '➡️ Tài khoản: '.$member[2].'
			user_id: '.$member_id.'
			🏆 Tổng lợi nhuận: '.formatCurrencyVND($member[0]).'
			Tỷ lệ: '.$member[1].'%';

			$memberStt += 1;
		}

		$this->sendMessageTelegram($messages);
	}
}
