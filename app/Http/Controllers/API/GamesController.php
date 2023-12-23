<?php

namespace App\Http\Controllers\Api;

use Illuminate\Auth\Access\AuthorizationException;
use App\Exceptions\InternalException;
use App\Http\Controllers\Supports\SBORequest;
use App\Http\Controllers\Supports\ApiRequest;
use App\Repositories\SystemConfigRepository;
use App\Http\Controllers\Controller;
use App\Models\ApiGame;
use App\Models\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\GameList;
use App\Models\Banner;
use GuzzleHttp\Client;

class GamesController extends Controller
{
    use SBORequest, ApiRequest;

    protected $systemConfigRepository;

    public function __construct()
    {
        $this->systemConfigRepository = app(SystemConfigRepository::class);
    }

    public function index(Request $request)
    {
        $games = DB::table('api_games')->select('api_games.*')
            ->join('apis', function ($join) {
                $join->on('apis.api_name', '=', 'api_games.api_name')
                    ->where('apis.is_open', 1);
            })
            ->where('api_games.game_type', $request->get('game_type'))
            ->whereIn('api_games.client_type', [0, 1])
            ->where('api_games.is_open', 1)
            ->whereIn('api_games.lang', [ApiGame::LANG_COMMON, $request->get('lang')])
            ->orderBy('api_games.weight', 'desc')
            ->latest()
            ->get()
            ->transform(function ($item) use ($request) {
                $item->title = Str::contains($item->lang_json, $request->get('lang')) ? Arr::get(json_decode($item->lang_json, 1), $request->get('lang'), $item->title) : $item->title;
                return $item;
            });

        return $this->success(['data' => $games]);
    }

    public function categories(Request $request)
    {
        $config = app(SystemConfig::class)
            ->where('name', request()->get('category_name', request('is_mobile') ? 'mobile_category_json' : 'web_category_json'))
            ->whereIn('lang', [ApiGame::LANG_COMMON, $request->get('lang')])
            ->first();

        $categories = collect(json_decode($config->value, true))
            ->where('is_open', true)
            ->sortByDesc('weight');

        return $this->success(['data' => $categories]);
    }
 
    public function play()
    {
        // run SBO game
        if (request()->has('portfolio')) {
            return $this->runSBOGame(request()->get('portfolio'));
        }

        // check member is_trans_on
        if (getGuard()->user()->is_trans_on) {
            throw new AuthorizationException();
        }
    }

    protected function runSBOGame($portfolio)
    {
        // check login
        if (blank(getGuard()->user())) {
            throw new AuthorizationException();
        }

        // get system config: company_key, server_id
        $configs = $this->systemConfigRepository
            ->where('config_group', 'remote_api')
            ->where('lang', 'common')
            ->get()
            ->pluck('value', 'name')
            ->toArray();
        // login SBO
        $response = $this->loginSBO($portfolio, $configs);
        if (!empty($response)) {
            if ($response->error->id == 0) {
                return $this->success(['data' => [
                    'redirect' => $this->getSBOGameUrl($portfolio, $response->url, $configs)
                ]]);
                
            }

            if ($response->error->id == 3303) {
                // regist SBO account
                $this->registerSBOPlayer(getGuard()->user(), $configs);

                // login SBO
                $response = $this->loginSBO($portfolio, $configs);
                if ($response->error->id == 0) {
                    $this->success(['data' => [
                        'redirect' => $this->getSBOGameUrl($portfolio, $response->url, $configs)
                    ]]);
                }
            }
        }

        return redirect()->back();
    }

    protected function loginSBO($portfolio, $configs)
    {
        return $this->sboRequest(getConfig('sbo_api.login'), 'POST', [
            'CompanyKey' => data_get($configs, 'company_key'),
            'ServerId' => data_get($configs, 'server_id'),
            'Username' => getGuard()->user()->name,
            'Portfolio' => $portfolio
        ]);
    }

    protected function getSBOGameUrl($portfolio, $responseUrl, $configs)
    {
        // update bet setting
        $response = $this->updateBetSetting($configs);
        if (!$response) {
            return $this->failed(trans('messages.system_error'));
        }

        $language = request()->get('lang');
        $currentLanguage = session()->get(getConfig('language_prefix'), 'vi');
        $language = $language == 'common' ? $currentLanguage : $language;
        $language = getConfig('sbo_language.' . $language);

        $url = "https:{$responseUrl}";
        switch ($portfolio) {
            case 'SportsBook':
                $params = [
                    'lang' => $language,
                    'oddstyle' => 'MY',
                    'theme' => 'sbo',
                    'oddsmode' => 'double',
                    'device' => request()->get('mode', 'm'),
                ];
                break;
            case 'Casino':
                $params = [
                    'locale' => $language,
                    'loginMode' => '3',
                    'productId' => request()->get('productId', 0),
                    'device' => request()->get('mode', 'm'),
                ];
                break;
            case 'Game':
                $params = [
                    'gameId' => request()->get('gameId', '6101'),
                ];
                break;
            case 'VirtualSports':
                $params = [
                    'lang' => $language,
                ];
                break;
            case 'SeamlessGame':
                $params = [
                    'lang' => $language,
                    'gpid' => request()->get('gpId', '10000'),
                    'gameid' => request()->get('gameId', '0'),
                    'betCode' => request()->get('betCode'),
                    'device' => request()->get('mode', 'm'),
                ];
                break;
            case 'ThirdPartySportsBook':
                $params = [
                    'lang' => $language,
                    'gpid' => request()->get('gpId', '44'),
                    'gameid' => request()->get('gameId', '0'),
                    'device' => request()->get('mode', 'm'),
                ];
                break;
            case '568WinSportsbook':
                $params = [
                    'lang' => $language,
                    'oddstyle' => 'MY',
                    'oddsmode' => 'double',
                    'device' => request()->get('mode', 'm'),
                ];
                break;
            default:
                $params = [];
                break;
        }
        return $url . '&' . http_build_query($params);
    }

    protected function updateBetSetting($configs)
    {
        try {
            $params = [
                'CompanyKey' => data_get($configs, 'company_key'),
                'ServerId' => data_get($configs, 'server_id'),
                'Username' => getGuard()->user()->name,
                'min' => intval(data_get($configs, 'user_min_bet_setting')),
                'max' => intval(data_get($configs, 'user_max_bet_setting')),
                'MaxPerMatch' => intval(data_get($configs, 'user_max_per_match')),
                'CasinoTableLimit' => intval(data_get($configs, 'casino_table_limit')),
            ];
            $response = app(Client::class)->request('post', getConfig('sbo_api.update_bet_setting'), ['json' => $params]);
           
            $response = $response->getStatusCode() == 200 ? json_decode($response->getBody()->getContents(), true) : null;
            if (empty($response) || data_get($response, 'error.id') != 0) {
                throw new \Exception($response);
            }
            return true;
        } catch (\Exception $exception) {
            logError($exception);
        }

        return false;
    }

    protected function registerSBOPlayer($user, $configs)
    {
        return $this->sboRequest(getConfig('sbo_api.register_player'), 'POST', [
            'CompanyKey' => data_get($configs, 'company_key'),
            // 'CompanyKey' => 'EA112BEB7C4944D1BA2376267D733672',
            'ServerId' => data_get($configs, 'server_id'),
            'Username' => $user->name,
            'Agent' => getConfig('sbo_agent'),
            // 'Agent' => 'noci88agent',
            'DisplayName' => $user->name,
        ]);
    }
}
