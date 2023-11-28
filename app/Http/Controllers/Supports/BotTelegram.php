<?php

namespace App\Http\Controllers\Supports;

use App\Repositories\SystemConfigRepository;
use Illuminate\Support\Facades\Http;

trait BotTelegram
{
    public function sendMessageToGroup($message)
    {
        $telegramConfigs = app(SystemConfigRepository::class)
            ->where('config_group', 'telegram')
            ->get()
            ->pluck('value', 'name')
            ->toArray();

        $data = [
            'chat_id' => data_get($telegramConfigs, 'telegram_chat_id'),
            'text' => $message,
            'parse_mode' => 'markdown',
        ];

        $apiURL = "https://api.telegram.org/bot" . data_get($telegramConfigs, 'telegram_bot_id') . "/sendMessage?" . http_build_query($data);
        return Http::get($apiURL);
    }
}
