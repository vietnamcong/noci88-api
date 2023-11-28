<?php

namespace Core\Providers\Facades\Log;

use Core\Helpers\ChatWork\ChatWork;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Monolog\Logger;
use Exception;

class ChannelWriter
{
    const LOG_WALL = '"';
    const LOG_DELIMITER = ',';
    const LOG_EOL = "\n";

    protected $channels = [];

    protected $levels = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'alert' => Logger::ALERT,
        'emergency' => Logger::EMERGENCY,
    ];

    public function __construct()
    {
        $this->channels = $this->_initChannel();
    }

    /**
     * @return array
     */
    protected function _initChannel(): array
    {
        return [
            'debug' => [
                'path' => 'debug',
                'level' => Logger::DEBUG,
            ],
            'info' => [
                'path' => 'info',
                'level' => Logger::INFO,
            ],
            'notice' => [
                'path' => 'notice',
                'level' => Logger::NOTICE,
            ],
            'warning' => [
                'path' => 'warning',
                'level' => Logger::WARNING,
            ],
            'error' => [
                'path' => 'error',
                'level' => Logger::ERROR,
            ],
            'critical' => [
                'path' => 'critical',
                'level' => Logger::CRITICAL,
            ],
            'alert' => [
                'path' => 'alert',
                'level' => Logger::ALERT,
            ],
            'emergency' => [
                'path' => 'emergency',
                'level' => Logger::EMERGENCY,
            ],
        ];
    }

    /**
     * @param $channel
     * @param $level
     * @param string $filename
     * @return ChannelStreamHandler
     */
    protected function _getHandler($channel, $level, string $filename = 'app'): ChannelStreamHandler
    {
        $logFilePath = storage_path('logs') . '/' . date('Y-m-d') . '/' . $filename . '.log';
        return new ChannelStreamHandler($channel, $logFilePath, $this->levels[$level]);
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param array $context
     * @throws Exception
     */
    public function writeLog($channel, $level, $message, array $context = [])
    {
        if (empty($level)) {
            $level = 'debug';
        }
        $log = '';
        $log .= self::LOG_WALL . '[' . date('Y-m-d H:i:s') . ']' . self::LOG_WALL . self::LOG_DELIMITER;
        $log .= self::LOG_WALL;
        switch (true) {
            case $this->levels[$level] == Logger::DEBUG:
                $log .= '[-DEBUG-] ';
                break;
            case $this->levels[$level] == Logger::INFO:
                $log .= '[*INFO *] ';
                break;
            case $this->levels[$level] == Logger::WARNING:
                $log .= '[*WARN *] ';
                break;
            case $this->levels[$level] == Logger::ERROR:
                $log .= '[#ERROR#] ';
                break;
            case $this->levels[$level] == Logger::CRITICAL:
                $log .= '[#CRIT #] ';
                break;
        }

        if (is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $log .= $message;

        $mode = '';
        if (isset($context['mode'])) {
            $mode = $context['mode'];
            unset($context['mode']);
        }

        $serverInfo = '';
        $isCmd = App::runningInConsole();

        // Log output item (server name)
        if (strpos($mode, 'N') !== false) {
            $serverInfo .= self::LOG_WALL . '[N]' . ($isCmd ? env('APP_URL', '') : @$_SERVER['SERVER_NAME']) . self::LOG_WALL . self::LOG_DELIMITER;
        }

        // Log output item (server address)
        if (strpos($mode, 'A') !== false) {
            $serverInfo .= self::LOG_WALL . '[A]' . ($isCmd ? @gethostbyname(php_uname('n')) : @$_SERVER['REMOTE_ADDR']) . self::LOG_WALL . self::LOG_DELIMITER;
        }

        // Log output item (execution script name)
        if (strpos($mode, 'S') !== false) {
            $serverInfo .= self::LOG_WALL . '[S]' . ($isCmd ? base_path() . '/' . 'artisan' : @$_SERVER['SCRIPT_NAME']) . self::LOG_WALL . self::LOG_DELIMITER;
        }

        // Log output item (controller name)
        if (strpos($mode, 'C') !== false) {
            $serverInfo .= self::LOG_WALL . '[C]' . ($isCmd ? @$_SERVER['argv'][1] : (!empty(Route::currentRouteAction()) ? Route::currentRouteAction() : '')) . self::LOG_WALL . self::LOG_DELIMITER;
        }

        // log output item (action name)
        if (strpos($mode, 'T') !== false) {
            $serverInfo .= self::LOG_WALL . '[T]' . ($isCmd ? @$_SERVER['argv'][2] : (!empty(Route::currentRouteAction()) ? Route::currentRouteAction() : '')) . self::LOG_WALL . self::LOG_DELIMITER;
        }

        // Log output items (Request URI..WEB only)
        if (!$isCmd && strpos($mode, 'R') !== false) {
            $serverInfo .= self::LOG_WALL . '[R]' . @$_SERVER['REQUEST_URI'] . self::LOG_WALL . self::LOG_DELIMITER;
        }

        // Log output items (Access USER AGENT..WEB only)
        if (strpos($mode, 'U') !== false) {
            $serverInfo .= self::LOG_WALL . '[U]' . ($isCmd ? @php_uname() : @$_SERVER['HTTP_USER_AGENT']) . self::LOG_WALL . self::LOG_DELIMITER;
        }

        if (strpos($mode, 'H') !== false && isset($_SERVER['HTTP_REFERER'])) {
            $serverInfo .= self::LOG_WALL . '[H]' . @$_SERVER['HTTP_REFERER'] . self::LOG_WALL . self::LOG_DELIMITER;
        }

        $serverInfo .= self::LOG_EOL;

        // check channel exist
        if (!in_array($channel, array_keys($this->channels))) {
            throw new \InvalidArgumentException('Invalid channel used.');
        }

        // override log path
        $filename = getArea();

        if (!empty($context['path'])) {
            $filename = $context['path'];
            $this->channels[$channel]['path'] = $context['path'];
            unset($context['path']);
        }

        if (empty($context['path'])) {
            unset($context['path']);
        }

        // push message to ChatWork
        if (isEnableChatWork()) {
            $msgToChatWork = $log;
            $msgLength = mb_strlen($msgToChatWork);
            $msgMaxLength = config('services.chat_work.message_max_length');
            if ($msgLength > $msgMaxLength) {
                $msgToChatWork = mb_substr($msgToChatWork, 0, $msgMaxLength) . '...';
            }

            $msgChatWork = '';
            $msgChatWork .= '[code]ログファイル[' . getArea() . '.log]' . "\n";
            $msgChatWork .= $msgToChatWork . '"' . "\n";
            $msgChatWork .= $serverInfo;
            $msgChatWork .= '[/code]';

            switch (true) {
                case (isset($this->levels[$channel]) && $this->levels[$channel] == Logger::ERROR):
                case $this->levels[$level] == Logger::ERROR:
                    $chatWork = new ChatWork();
                    $roomId = config('services.chatwork.room_id_error');
                    $chatWork->writeMessage($msgChatWork, $roomId);
                    break;
            }
        }

        // create instance
        $this->channels[$channel]['_instance'] = new Logger($channel);
        // add custom handler
        $this->channels[$channel]['_instance']->pushHandler($this->_getHandler($channel, $level, $filename));
        $this->channels[$channel]['_instance']->{$level}($log . "\n" . $serverInfo, $context);
    }

    /**
     * @param $channel
     * @param $message
     * @param array $context
     * @throws Exception
     */
    public function write($channel, $message, array $context = [])
    {
        $level = array_flip($this->levels)[$this->channels[$channel]['level']];
        $this->writeLog($channel, $level, $message, $context);
    }

    /**
     * @param $function
     * @param $params
     * @throws Exception
     */
    public function __call($function, $params)
    {
        if (in_array($function, array_keys($this->channels))) {
            $this->writeLog($function, $params[0], $params[1], $params[2] ?? []);
        }
    }
}
