<?php

/**
 * Class Singleton
 *
 * Singleton pattern implementation.
 *
 * Use as parent for all Singleton classes.
 */

namespace App\Misc;

use Exception;

abstract class Singleton
{
    protected static ?array $instances = [];

    /**
     * Prevents cloning of existing instance.
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Prevents unserializing of existing instance. Cannot be left empty as __clone, therefore I used throw Exception.
     *
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }

    /**
     * Singleton constructor. Feel free to use when obtaining instance.
     *
     * @return static
     */
    public static function get(): object
    {
        $static = static::class;

        $class = explode('\\', $static);
        $class = end($class);

        if (empty(self::$instances[$class])) {
            self::$instances[$class] = new $static;
        }

        return self::$instances[$class];
    }
}
