<?php

namespace App\Http\Controllers\API\ClientPC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function language(Request $request)
    {
        $language = $request->get('lang', 'vi');
        $this->app->setLocale($language);
        session([getConfig('language_prefix') => $language]);

        return response()->json(['message' => 'success', 'data' => $language], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        // Get current language
        $lang = app()->getLocale() ?? 'vi';

        // Get list banner
        $banners = $this->getBanners('new1', $lang);

        // Get list category game
        $gameCategories = $this->getCategories();

        // Get list game by category
        $gameTypes = [];
        foreach ($gameCategories as $gameCategory) {
            $gameTypes[] = optional($gameCategory)['game_type'];
        }

        $listGames = [];
        $listGamesTmp = $this->getGames($gameTypes, $lang);
        foreach ($listGamesTmp as $item) {
            $listGames[optional($item)->game_type][] = $item;
        }

        $notices = $this->systemNotice
            ->select(['title', 'content', 'url'])
            ->where('lang', $lang)
            ->where('title', 'Wellcome')
            ->first();

        $contact = $this->systemConfig->whereIn('lang', [self::LANG_COMMON, $lang])->getConfigGroup('customer');

        $queryGame = $this->apiGame->select(['*'])
            ->where('is_open', 1)
            ->whereIn('lang', [$lang, self::LANG_COMMON])
            ->orderBy('weight', 'desc');

        // 5: Thể thao
        $sport_1 = clone $queryGame;
        $sport_1 = $sport_1->where('game_type', 5)->get();
        // 1: Live casino
        $sport_2 = clone $queryGame;
        $sport_2 = $sport_2->where('game_type', 1)->get();
        // 3: Slot game
        $sport_3 = clone $queryGame;
        $sport_3 = $sport_3->where('game_type', 3)->get();

        $download = [
            'ios' => '',
            'android' => '',
        ];

        $data = [
            'menus' => $gameCategories,
            'listGames' => $listGames,
            'showNotice' => true,
            'notices' => $notices,
            'banners' => $banners,
            'game_sport' => $sport_1,
            'game_live_casino' => $sport_2,
            'game_slot' => $sport_3,
            'download' => $download,
            'contact' => $contact,
            'lang' => $lang,
        ];

        return response()->json(['message' => 'success', 'data' => $data], Response::HTTP_OK);
    }

    protected function getBanners($group, $lang)
    {
        return $this->banner
            ->select(['title', 'url', 'groups', 'dimensions', 'weight', 'jump_link', 'is_new_window'])
            ->where('is_open', 1)
            ->when($group != null, function ($query) use ($group) {
                $query->where('groups', $group);
            })
            ->whereIn('lang', ['common', $lang])
            ->orderByDesc('weight')
            ->get();
    }

    protected function getCategories()
    {
        $config = $this->systemConfig
            // ->where('name', 'mobile_category_json')
            ->where('name', 'web_category_json')
            ->where('lang', self::LANG_COMMON)
            ->first();

        $categories = collect(json_decode($config->value, true))
            // ->where('is_open', true)
            ->sortByDesc('weight');

        return $categories;
    }

    protected function getGames($gameTypes, $lang)
    {
        $games = $this->apiGame
            ->select('api_games.*')
            ->join('apis', function ($join) {
                $join->on('apis.api_name', '=', 'api_games.api_name')
                    ->where('apis.is_open', 1);
            })
            ->whereIn('api_games.game_type', $gameTypes)
            ->whereIn('api_games.client_type', [0, 2])
            ->where('api_games.is_open', 1)
            ->whereIn('api_games.lang', [self::LANG_COMMON, $lang])
            ->orderBy('api_games.weight', 'desc')
            ->latest()
            ->get()
            ->transform(function ($item) use ($lang) {
                $item->title = Str::contains($item->lang_json, $lang) ? Arr::get(json_decode($item->lang_json, 1), $lang, $item->title) : $item->title;
                return $item;
            });

        return $games;
    }

    public function captcha(Request $request)
    {
        $data = self::createCaptcha();
        return response()->json(['message' => __('message.success'), 'data' => $data], Response::HTTP_OK);
    }

    public function activityType()
    {
        $data = __('message.activity_type');
        return response()->json(['message' => __('message.success'), 'data' => $data], Response::HTTP_OK);
    }

    public function activityList()
    {
        $lang = app()->getLocale() ?? 'vi';
        $data = $this->activity
            ->where('is_open', 1)
            ->isApp()
            // ->where('cover_image', '!=', '')
            ->whereIn('lang', [self::LANG_COMMON, $lang])
            ->orderByDesc('weight')
            ->latest()
            ->get(['id', 'title', 'subtitle', 'cover_image', 'type', 'weight']);

        return response()->json(['message' => __('message.success'), 'data' => $data], Response::HTTP_OK);
    }

    public function activityDetail($id)
    {
        $activity = $this->activity->find($id);
        if (!$activity) {
            return response()->json(['message' => __('message.activity.activity_exists')], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'activity' => $activity,
            'html' => $this->activityService->getActivityDetailHtml($activity),
        ];
        return response()->json(['message' => __('message.success'), 'data' => (object) $data], Response::HTTP_OK);
    }
}
