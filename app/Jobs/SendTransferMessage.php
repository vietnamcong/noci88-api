<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\ResponseTrait;
use App\Models\Transfer;

class SendTransferMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResponseTrait;
	
	protected $data;
	protected $type_transfer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $type_transfer)
    {
		$this->data = $data;
		$this->type_transfer = $type_transfer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$result = $this->data;
		$type_transfer = $this->type_transfer;

		$last_transfer = $this->lastTransfer($result->user_id, $result->transfer_type);
		$total_recharge = $this->totalTransfer($result->user_id, 0);
		$total_withdrawal = $this->totalTransfer($result->user_id, 1);
		$total_profit = $this->totalProfit($result->user_id);

		$message = '';

		if ($type_transfer == 1) {
			if ($result->transfer_type == 0) {
				$text_last_transfer = '';
				if ($last_transfer) {
					$text_last_transfer = 'LẦN NẠP GẦN NHẤT:
					🥇 BANK: '.formatCurrencyVND($last_transfer->money).' => Ngày: '.dateFormats($last_transfer->created_at);
				}

				$message = '➡ [NẠP] Bank: Tài khoản '.optional($result->user)->username.'
				Yêu cầu nạp: '.formatCurrencyVND($result->money).'
				Số điểm: '.(int)$result->point.' điểm
				Đến game: '.optional($result->game)->title.'
				Đến '.strtoupper(optional($result->bank_admin)->bank_type).': '.strtoupper(optional($result->bank_admin)->owner_name).', STK: '.optional($result->bank_admin)->card_no.'
				➕Tổng Nạp BANK*MOMO = '.formatCurrencyVND($total_recharge).'
				➖Tổng Rút BANK*MOMO = '.formatCurrencyVND($total_withdrawal).'
				'.$text_last_transfer.'
				🏆 Lỗ/Lãi: '.formatCurrencyVND($total_profit);
			} else {
				$text_last_transfer = '';
				if ($last_transfer) {
					$text_last_transfer = 'LẦN NẠP GẦN NHẤT:
					🥇 BANK: '.formatCurrencyVND($last_transfer->money).' => Ngày: '.dateFormats($last_transfer->created_at);
				}

				$message = '⬅️ [RÚT] Bank: Tài khoản '.optional($result->user)->username.'
				Yêu cầu rút: '.formatCurrencyVND($result->money).'
				Số điểm: '.(int)$result->point.' điểm
				Từ game: '.optional($result->game)->title.'
				Về '.strtoupper(optional($result->bank)->bank_type).': '.strtoupper(optional($result->bank)->owner_name).', STK: '.optional($result->bank)->card_no.'
				➕Tổng Nạp BANK*MOMO = '.formatCurrencyVND($total_recharge).'
				➖Tổng Rút BANK*MOMO = '.formatCurrencyVND($total_withdrawal).'
				'.$text_last_transfer.'
				🏆 Lỗ/Lãi: '.formatCurrencyVND($total_profit);
			}
		}

		if ($type_transfer == 2) {
			if ($result->transfer_type == 0) {
				$text_last_transfer = '';
				if ($last_transfer) {
					$text_last_transfer = 'LẦN NẠP GẦN NHẤT:
					🥇 BANK: '.formatCurrencyVND($last_transfer->money).' => Ngày: '.dateFormats($last_transfer->created_at);
				}

				$message = '❌ [NẠP] Bank: Tài khoản '.optional($result->user)->username.'
				Yêu cầu nạp: '.formatCurrencyVND($result->money).'
				Số điểm: '.(int)$result->point.' điểm
				Đến game: '.optional($result->game)->title.'
				Đến '.strtoupper(optional($result->bank_admin)->bank_type).': '.strtoupper(optional($result->bank_admin)->owner_name).', STK: '.optional($result->bank_admin)->card_no.'
				➕Tổng Nạp BANK*MOMO = '.formatCurrencyVND($total_recharge).'
				➖Tổng Rút BANK*MOMO = '.formatCurrencyVND($total_withdrawal).'
				'.$text_last_transfer.'
				🏆 Lỗ/Lãi: '.formatCurrencyVND($total_profit);
			} else {
				$text_last_transfer = '';
				if ($last_transfer) {
					$text_last_transfer = 'LẦN RÚT GẦN NHẤT:
					🥇 BANK: '.formatCurrencyVND($last_transfer->money).' => Ngày: '.dateFormats($last_transfer->created_at);
				}

				$message = '❌ [RÚT] Bank: Tài khoản '.optional($result->user)->username.'
				Yêu cầu rút: '.formatCurrencyVND($result->money).'
				Số điểm: '.(int)$result->point.' điểm
				Từ game: '.optional($result->game)->title.'
				Về '.strtoupper(optional($result->bank)->bank_type).': '.strtoupper(optional($result->bank)->owner_name).', STK: '.optional($result->bank)->card_no.'
				➕Tổng Nạp BANK*MOMO = '.formatCurrencyVND($total_recharge).'
				➖Tổng Rút BANK*MOMO = '.formatCurrencyVND($total_withdrawal).'
				'.$text_last_transfer.'
				🏆 Lỗ/Lãi: '.formatCurrencyVND($total_profit);
			}
		}
		
		$this->sendMessageTelegram($message);
    }

	public function lastTransfer($user_id, $transfer_type)
	{
		$transfer = Transfer::where('user_id', $user_id)->where('transfer_type', $transfer_type)->where('status', 1)->latest('created_at')->first();
		return $transfer;
	}

	public function totalTransfer($user_id, $transfer_type)
	{
		$transfer = Transfer::where('user_id', $user_id)->where('transfer_type', $transfer_type)->where('status', 1)->sum('money');
		return $transfer;
	}

	public function totalProfit($user_id)
	{
		$transfer = Transfer::where('user_id', $user_id)
					->where('status', 1)
					->selectRaw('SUM(CASE WHEN transfer_type = 0 THEN money ELSE -money END) as net_profit')
					->value('net_profit');

		return $transfer ?? 0;
	}
}
