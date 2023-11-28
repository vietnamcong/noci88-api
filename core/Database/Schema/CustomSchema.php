<?php

namespace Core\Database\Schema;

use Illuminate\Support\Facades\Schema;

class CustomSchema extends Schema
{
    /**
     * @param string|null $name
     * @return \Illuminate\Database\Schema\Builder|mixed
     */
    public static function connection($name)
    {
        $schema = static::$app['db']->connection($name)->getSchemaBuilder();
        return self::_changeBlueprint($schema);
    }

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db.custom.schema';
    }

    /**
     * @param $schema
     * @return mixed
     */
    protected static function _changeBlueprint($schema)
    {
        $schema->blueprintResolver(function ($table, $callback) {
            return new CustomBlueprint($table, $callback);
        });
        return $schema;
    }
}
