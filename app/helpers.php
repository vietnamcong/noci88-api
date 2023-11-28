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