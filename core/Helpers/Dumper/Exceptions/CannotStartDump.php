<?php

namespace Core\Helpers\Dumper\Exceptions;

use Exception;

class CannotStartDump extends Exception
{
    public static function emptyParameter($name)
    {
        return new static("Parameter `{$name}` cannot be empty.");
    }
}
