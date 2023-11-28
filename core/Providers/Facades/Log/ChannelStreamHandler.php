<?php

namespace Core\Providers\Facades\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ChannelStreamHandler extends StreamHandler
{
    const LOG_FORMAT = "%message% %context% %extra%\n"; // "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";

    protected $channel;

    public function __construct($channel, $stream, $level = Logger::DEBUG, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false)
    {
        $this->channel = $channel;
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * @return FormatterInterface
     */
    public function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter(static::LOG_FORMAT, null, true, true);
    }

    /**
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        if (isset($record['channel'])) {
            return $record['level'] >= $this->level && $record['channel'] == $this->channel;
        }

        return $record['level'] >= $this->level;
    }
}
