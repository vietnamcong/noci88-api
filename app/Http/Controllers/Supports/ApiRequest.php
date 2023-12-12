<?php

namespace App\Http\Controllers\Supports;

trait ApiRequest
{
    protected static $timeout = 30;

    public function requestWithAuth($url, $method = 'POST', $params = [], $header = [])
    {
        $request_header = request()->header();
        
        $header[] = 'Authorization: ' . $request_header['authorization'][0]; 
        // . session()->get(getConfig('noci88_api.login_session_key'))->access_token;
        return $this->request($url, $method, $params, $header);
    }

    public function request($url, $method = 'POST', $params = [], $header = [])
    {
        $handle = curl_init();
        $userAgent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'ChatworkAPI Connector';
        $setOption = [
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::$timeout,
            CURLOPT_TIMEOUT => self::$timeout,
            CURLOPT_HTTPHEADER => (count($header) > 0) ? $header : array('Except:'),
            CURLOPT_HEADER => false
        ];
        // dd($setOption);
        curl_setopt_array($handle, $setOption);

        if (!data_get($params, 'no_lang')) {
            $params = array_merge(['lang' => getCurrentLanguage()], $params);
        }
        unset($params['no_lang']);

        switch (strtoupper($method)) {
            case 'GET':
                if (!blank($params)) {
                    $url .= '?' . http_build_query($params);
                }
                break;
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                if ($params != '') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params));
                }
                break;
            case 'PUT':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($params != '') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params));
                }
                break;
            case 'DELETE':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($params != '') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params));
                }
                break;
        }
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        logInfo('req=>' . json_encode($params) . ',url=>' . $url);
        $resp = curl_exec($handle);
        logInfo('res=>' . json_encode($resp));

        if ($resp) {
            $resp = json_decode($resp);
        }

        return $resp;
    }
}
