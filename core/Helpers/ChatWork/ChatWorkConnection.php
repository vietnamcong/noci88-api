<?php

namespace Core\Helpers\ChatWork;

class ChatWorkConnection
{
    protected $uri;
    protected $method = 'GET';
    protected $header = [];
    protected $postBody = '';
    public $connectTimeout = 30;
    public $timeout = 30;
    protected $apiKey;

    /**
     * ChatworkConnection constructor.
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param bool $method
     * @param string $uri
     * @param array $header
     * @param string $postBody
     * @return \Illuminate\Support\Collection
     */
    public function request($method = false, $uri = '', $header = [], $postBody = '')
    {
        $method = $method ? strtoupper($method) : $this->method;
        $uri = !empty($uri) ? $uri : $this->uri;
        $header = count($header) > 0 ? $header : $this->header;
        $postBody = !empty($postBody) ? $postBody : $this->postBody;

        return $this->_curlRequest($method, $uri, $header, $postBody);
    }

    /**
     * @param $method
     * @param $uri
     * @param $header
     * @param $postBody
     * @return \Illuminate\Support\Collection
     */
    protected function _curlRequest($method, $uri, $header, $postBody)
    {
        $handle = curl_init();
        $userAgent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : 'ChatworkAPI Connector';
        $header[] = 'X-ChatWorkToken: ' . $this->apiKey;
        $setOption = [
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => (count($header) > 0) ? $header : array('Except:'),
            CURLOPT_HEADER => false
        ];

        curl_setopt_array($handle, $setOption);

        switch ($method) {
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                if ($postBody != '') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $postBody);
                }
                break;
            case 'PUT':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($postBody != '') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $postBody);
                }
                break;
            case 'DELETE':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($postBody != '') {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $postBody);
                }
                break;
        }
        curl_setopt($handle, CURLOPT_URL, $uri);
        // curl_setopt($handle, CURLINFO_HEADER_OUT, 1);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($handle);
        if (!$resp) {
            $resp = false;
        }
        $response = collect();
        $response->status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $response->body = $resp;
        curl_close($handle);

        if (preg_match('/30[1237]/', (string)$response->status)) {
            $movedURI = preg_replace('|.+href="([^"]+)".+|is', '$1', $response->body);
            return $this->request($method, $movedURI, $header, $postBody);
        }

        return $response;
    }
}
