<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/6/16
 * Time: 11:13 AM
 */

namespace app\tests\redis;

use app\domain\redis\RedisCli;
use phpunit\framework\TestCase;


class RedisConnectionTest extends TestCase
{
    private $redisCli = null;

    public function setUp()
    {
        parent::setUp();

        $this->redisCli = $this->createMock('app\domain\redis\RedisCli');
    }

    /**
     */
    public function testRedisConnectionOk()
    {
        $cli = RedisCli::getClient('127.0.0.1', '6379');
        $this->assertInstanceOf(\Redis::class, $cli, 'Success!!');
        RedisCli::disconnect();
    }

    /**
     * @expectedException \RedisException
     */
    public function redisWrongHost()
    {
        RedisCli::getClient('127.0.0.2', 6379);
        RedisCli::disconnect();
    }

    /**
     * @expectedException \RedisException
     */
    public function testRedisWrongPort()
    {
        RedisCli::getClient('127.0.0.1', 'xxxx');
        RedisCli::disconnect();
    }


}