<?php

namespace Core\Providers\Facades\Storages;

use Illuminate\Support\Facades\Facade;

/**
 * Class CustomStorage
 * @package App\Helpers\Facades
 */
class BaseStorage extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'basestorage';
    }
}
