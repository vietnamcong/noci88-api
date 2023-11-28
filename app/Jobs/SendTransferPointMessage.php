<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\ResponseTrait;
use App\Models\TransferPoint;

class SendTransferPointMessage implements ShouldQueue
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

		$last_transfer = $this->lastTransfer($result->user_id, $result->game_id, $result->game_transfer_id);
		$text_last_transfer = '';
		if ($last_transfer) {
			$text_last_transfer = 'LẦN CHUYỂN GẦN NHẤT:
			🥇 ĐIỂM: '.(int)$result->point.' => Ngày: '.dateFormats($last_transfer->created_at);
		}
		// $total_transfer = $this->totalTransfer($result->user_id, $result->game_id, $result->game_transfer_id);

		$message = '';

		if ($type_transfer == 1) {
			$message = '♻️ [CHUYỂN] Điểm: Tài khoản '.optional($result->user)->username.'
			Yêu cầu chuyển: '.(int)$result->point.' điểm
			Từ game: '.optional($result->game)->title.'
			Đến game: '.optional($result->game_transfer)->title.'
			'.$text_last_transfer;
		}

		if ($type_transfer == 2) {
			$message = '❌ [CHUYỂN] Điểm: Tài khoản '.optional($result->user)->username.'
			Yêu cầu chuyển: '.(int)$result->point.' điểm
			Từ game: '.optional($result->game)->title.'
			Đến game: '.optional($result->game_transfer)->title.'
			'.$text_last_transfer;
		}
		
		$this->sendMessageTelegram($message);
    }

	public function lastTransfer($user_id, $game_id, $game_transfer_id)
	{
		$transfer = TransferPoint::where('user_id', $user_id)->where('game_id', $game_id)->where('game_transfer_id', $game_transfer_id)->where('status', 1)->latest('created_at')->first();
		return $transfer;
	}

	public function totalTransfer($user_id, $game_id, $game_transfer_id)
	{
		$transfer = TransferPoint::where('user_id', $user_id)->where('game_id', $game_id)->where('game_transfer_id', $game_transfer_id)->where('status', 1)->sum('point');
		return $transfer;
	}
}
