<?php

namespace Core\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL as FacadesURL;

/**
 * Class Url
 * @package App\Helpers
 */
class Url
{
    protected static $currentControllerName = null;

    /**
     * @var Url|null
     */
    protected static $instance = null;


    /**
     * @var int
     */
    protected $old = 0;

    /**
     *
     */
    const URl_KEY = 'url_key';

    /**
     *
     */
    const QUERY = '_o';

    /**
     *
     */
    const OLD_QUERY = '_o_';

    const BACK_URL_LIMIT = 200;

    /**
     * @return Url|null
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return null
     */
    public static function getCurrentControllerName()
    {
        return self::$currentControllerName;
    }

    /**
     * @param null $currentControllerName
     */
    public static function setCurrentControllerName($currentControllerName)
    {
        self::$currentControllerName = $currentControllerName;
    }


    /**
     * @param Url|null $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * @param $default |null
     * @param $params |null
     * @return int
     */
    public static function genUrlKey($default = '', $params = [])
    {
        $url = static::getFullUrl($default, $params);
        $urlKeys = session(self::URl_KEY, []);
        global $urlIdx;
        $urlIdx++;
        $time = time() . $urlIdx;
        krsort($urlKeys, SORT_STRING);

        if (!empty($urlKeys)) {
            $limit = self::BACK_URL_LIMIT;
            $urlKeys = array_chunk($urlKeys, $limit - 1, true);
            $urlKeys = $urlKeys[0];
        }

        $urlKeys[$time] = $url;
        session([self::URl_KEY => $urlKeys]);

        return $time;
    }

    protected static function getFullUrl($default = '', $params = [])
    {
        if ($default) {
            $url = strpos($default, '.') !== false ? route($default, $params) : $default;
            $url = parse_url($url);
            $r = isset($url['path']) ? $url['path'] : '';
            $r = isset($url['query']) && $r ? $r . '?' . $url['query'] : $r;

            return $r;
        }

        $router = app()->make('router');
        $inputs = static::buildParamString((array) Request::all());
        $uri = $router->getCurrentRoute()->uri;

        foreach ($router->getCurrentRoute()->parameters as $parameter => $value) {
            $uri = str_replace('{' . $parameter . '}', $value, $uri);
        }

        return $uri . $inputs;
    }

    protected static function buildParamString($params, $params1 = [])
    {
        $params = array_merge($params1, $params);
        $params = http_build_query($params);
        $params = $params ? '?' . $params : '';

        return $params;
    }

    /**
     * @param string $full
     * @param string $defaultUrl
     * @return string
     */
    public static function getBackUrl($full = true, $defaultUrl = '')
    {
        $old = Request::get(self::QUERY, false);

        if (!$old) {
            return $defaultUrl ? $defaultUrl : url()->previous();
        }

        $urlKeys = session(self::URl_KEY, []);
        $url = isset($urlKeys[$old]) ? $urlKeys[$old] : $defaultUrl;

        return $full ? url($url) : $url;
    }

    /**
     * @param $url
     * @param $default
     * @param array $params
     * @param array $paramsDefault
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public static function backUrl($url, $params = [], $default = '', $paramsDefault = [])
    {
        $old = self::genUrlKey($default, $paramsDefault);
        $params = array_merge((array) $params, [self::QUERY => $old]);

        if (strpos($url, '/') !== false) {
            return url($url, $params);
        }

        return route($url, $params);
    }

    protected static function getOldKey()
    {
        return static::getCurrentControllerName() . self::OLD_QUERY;
    }

    /**
     * @return mixed
     */
    public static function getOldUrl()
    {
        return session(static::getOldKey(), '');
    }

    /**
     *
     */
    public static function collectOldUrl()
    {
        session([static::getOldKey() => FacadesURL::previous()]);
    }

    /**
     * @return string
     */
    public static function keepBackUrl($value = null)
    {
        $value = $value ? $value : Request::get(self::QUERY, '');

        return '<input type="hidden" name="' . self::QUERY . '" value="' . $value . '">';
    }
}
