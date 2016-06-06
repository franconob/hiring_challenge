<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/3/16
 * Time: 6:10 PM
 */

namespace app\domain\redis;


use Redis;

class RedisCli
{
    /**
     * @var null|\Redis
     */
    private static $instance = null;

    /**
     * @param string $host
     * @param int $port
     * @return \Redis
     * @throws \Exception
     */
    public static function getClient($host, $port)
    {
        if (!self::$instance) {
            self::$instance = new \Redis();

            $connected = self::$instance->connect($host, $port);
            if (!$connected) {
                throw new \RedisException('Server error, can\'t connect.');
            }
        }

        self::$instance->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        return self::$instance;
    }

    public static function disconnect()
    {
        if (self::$instance) {
            self::$instance->close();
            self::$instance = null;
        }

        return true;
    }
}