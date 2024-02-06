<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Get client ip
 * @return array|false|null|string
 */
if (!function_exists('get_client_ip')) {
	function get_client_ip()
	{
		static $realip = NULL;
		if ($realip !== NULL) {
			return $realip;
		}
		// Determine whether the server allows $_SERVER
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$realip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			// Use getenv to obtain if not allowed
			if (getenv("HTTP_X_FORWARDED_FOR")) {
				$realip = getenv("HTTP_X_FORWARDED_FOR");
			} elseif (getenv("HTTP_CLIENT_IP")) {
				$realip = getenv("HTTP_CLIENT_IP");
			} else {
				$realip = getenv("REMOTE_ADDR");
			}
		}

		return strpos($realip, ",") ? substr($realip, 0, strpos($realip, ",")) : $realip;
	}
}

if (!function_exists('is_Mobile')) {
	function is_Mobile()
	{
		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		}
		if (isset($_SERVER['HTTP_VIA'])) {
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array(
				'nokia',
				'sony',
				'ericsson',
				'mot',
				'samsung',
				'htc',
				'sgh',
				'lg',
				'sharp',
				'sie-',
				'philips',
				'panasonic',
				'alcatel',
				'lenovo',
				'iphone',
				'ipod',
				'blackberry',
				'meizu',
				'android',
				'netfront',
				'symbian',
				'ucweb',
				'windowsce',
				'palm',
				'operamini',
				'operamobi',
				'openwave',
				'nexusone',
				'cldc',
				'midp',
				'wap',
				'mobile'
			);
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}
		}
		if (isset($_SERVER['HTTP_ACCEPT'])) {
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}
}

/**
 * Determine whether the key of the array exists, and it is not empty, if it is empty, take the given value as the default value
 * @param $arr
 * @param $column
 * @return null
 */
if (!function_exists('isset_and_not_empty')) {
	function isset_and_not_empty($arr, $column, $defaultValue = '')
	{
		if ((isset($arr[$column]) && $arr[$column])) {
			return $arr[$column];
		} else {
			return $defaultValue;
		}
	}
}

if (!function_exists('formatCurrencyVND')) {
	function formatCurrencyVND($amount)
	{
		return number_format($amount, 0, '.', ',') . ' ₫';
	}
}

if (!function_exists('dateFormat')) {
	function dateFormat($date)
	{
		date_default_timezone_set("Asia/Ho_Chi_Minh");
		return date('Y-m-d H:i:s', strtotime(date_create($date)));
	}
}

if (!function_exists('dateFormatVN')) {
	function dateFormatVN($date)
	{
		date_default_timezone_set("Asia/Ho_Chi_Minh");
		return $date->format('d-m-Y H:i');
	}
}

if (!function_exists('getRequestLang')) {
	function getRequestLang()
	{
		return request('lang', \App\Models\Base::LANG_CN);
	}
}

if (!function_exists('getConfig')) {
	function getConfig($key, $default = null)
	{
		return config('config.' . $key, $default);
	}
}

if (!function_exists('array_filter_null')) {
	function array_filter_null($data)
	{
		return array_filter($data, function ($temp) {
			return $temp !== null;
		});
	}
}

if (!function_exists('quicklink')) {
	function quicklink($name)
	{
		return app(\App\Models\QuickUrl::class)->getLink($name);
	}
}

if (!function_exists('systemconfig')) {
	function systemconfig($name, $lang = '')
	{
		return \App\Models\SystemConfig::getConfigValue($name, $lang);
	}
}

if (!function_exists('getBillNo')) {
	function getBillNo()
	{
		return date('YmdHis') . Str::random(5);
	}
}

if (!function_exists('isApp')) {
	function isApp()
	{
		return \request('isApp') || \request('is_app');
	}
}

if (!function_exists('checkIsBetweenTime')) {
	/**
	 * Xác định xem thời gian hiện tại có nằm trong phạm vi thời gian không
	 * @param $start bắt đầu 00:00:00
	 * @param $end kết thúc 01:00:00
	 * @return int 1 có nghĩa là trong phạm vi thời gian, 0 có nghĩa là không nằm trong phạm vi thời gian
	 */
	function checkIsBetweenTime($start, $end)
	{
		$date = date('H:i');
		$curTime = strtotime($date); // Giờ hiện tại
		$assignTime1 = strtotime($start);
		$assignTime2 = strtotime($end);
		$result = 0;

		if ($assignTime1 > $assignTime2) $assignTime1 -= 60 * 60 * 24;
		if ($curTime > $assignTime1 && $curTime < $assignTime2) {
			$result = 1;
		}
		return $result;
	}
}

if (!function_exists('convertDateToArray')) {
	function convertDateToArray($date, $field)
	{
		$condition = [];
		if (is_string($date) && strpos($date, '~'))
			list($start_at, $ends_at) = explode('~', $date);
		else {
			$start_at = current($date);
			$ends_at = end($date);
		}
		array_push($condition, [$field, '>', $start_at]);
		array_push($condition, [$field, '<', $ends_at]);
		return $condition;
	}
}

function get_language_fields_array()
{
    $fields = \systemconfig('vip1_lang_fields');

    $fields = isJson($fields) ? json_decode($fields, 1) : [];

    return $fields;
}

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function getUrl($url)
{
    return \Str::contains($url, '//') ? substr(strstr($url, '//'), 2) : $url;
}

if (!function_exists('getConst')) {
    function getConst($key, $default = null)
    {
        return config('const.' . $key, $default);
    }
}


function get_unique_array($arr)
{
    $array = array_flip($arr);
    return array_keys($array);
}


function curls($url, $params = false, $ispost = 1, $https = 0)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($https || \Str::startsWith($url, 'https:')) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        // 如果是多维数组需要进行处理
        curl_setopt($ch, CURLOPT_POSTFIELDS, count($params) == count($params, 1) ? $params : http_build_query($params));
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === FALSE) {
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}

function randomFloat($min = 0, $max = 10)
{
    $num = $min + mt_rand() / mt_getrandmax() * ($max - $min);
    return sprintf("%.2f", $num);
}

function writelog($str)
{
    /**
     * $file = fopen(public_path() . "/log.txt", "a");
     * fwrite($file, date('Y-m-d H:i:s') . "   " . $str . "\r\n");
     * fclose($file);
     * //print_r($str.'<br/><br/>');
     * */
    \Illuminate\Support\Facades\Log::info($str);
}