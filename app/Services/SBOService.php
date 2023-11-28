<?php

namespace App\Services;

use App\Models\SystemConfig;
use App\Models\TransactionHistory;
use App\Traits\SBORequest;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SBOService
{
    use SBORequest;

    protected $configs;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        $this->configs = app(SystemConfig::class)->whereIn('name', ['company_key', 'server_id'])
            ->get()
            ->pluck('value', 'name')
            ->toArray();
    }

    public function getBetDetail($data)
    {
        $history = TransactionHistory::where('id', data_get($data, 'id'))->first();

        $params = [
            'CompanyKey' => data_get($this->configs, 'company_key'),
            'ServerId' => data_get($this->configs, 'server_id'),
            'Portfolio' => $history->getPorfolioText(),
            'Refno' => $history->transfer_code,
            'Language' => data_get('Language', 'vi_vn'),
        ];

        $response = app(Client::class)->request('POST', getConfig('sbo_api.get_bet_payload'), ['json' => $params]);
        return $response->getStatusCode() == 200 ? json_decode($response->getBody()->getContents(), true) : null;
    }

    public function signupAccount($data)
    {
        return $this->sboRequest(getConfig('sbo_api.register_player'), 'POST', [
            'CompanyKey' => data_get($this->configs, 'company_key'),
            'ServerId' => data_get($this->configs, 'server_id'),
            'Username' => data_get($data, 'name'),
            'Agent' => getConfig('sbo_agent'),
            'UserGroup' => data_get($this->configs, 'server_id'),
            'DisplayName' => data_get($data, 'realname') ?? data_get($data, 'name'),
        ]);
    }

    public function updateBetSetting($data)
    {
        try {
            $params = [
                'CompanyKey' => data_get($this->configs, 'company_key'),
                'ServerId' => data_get($this->configs, 'server_id'),
                'Username' => getConfig('sbo_agent'),
                'min' => intval(data_get($data, 'min_bet_setting')),
                'max' => intval(data_get($data, 'max_bet_setting')),
                'MaxPerMatch' => intval(data_get($data, 'max_per_match')),
                'CasinoTableLimit' => intval(data_get($data, 'casino_table_limit')),
            ];

            $response = app(Client::class)->request('post', getConfig('sbo_api.agent_bet_setting'), ['json' => $params]);
            $response = $response->getStatusCode() == 200 ? json_decode($response->getBody()->getContents(), true) : null;

            if (empty($response) || data_get($response, 'error.id') != 0) {
                Log::error($response);
            }

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
        }

        return false;
    }
}
