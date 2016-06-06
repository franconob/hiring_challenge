<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/6/16
 * Time: 6:47 PM
 */
namespace app\tests\redis;

use app\domain\chat\FriendsList;
use tests\redis\RedisMockClient;

/**
 * @param $redis RedisMockClient redis cli
 */
function createMockData($redis)
{
    $redis->set('PHPREDIS_SESSION:hash', ['default' => ['id' => 1]]);
    $redis->set('chat:online:176733', true);
    $redis->set('chat:friends:1', new FriendsList([
        [
            'id' => 1,
            'name' => 'Project 1',
            'threads' => [
                [
                    'online' => false,
                    'other_party' => [
                        'user_id' => 176733,
                    ]
                ]
            ]
        ],
        [
            'id' => 2,
            'name' => 'Project 2',
            'threads' => [
                [
                    'online' => false,
                    'other_party' => [
                        'user_id' => 176733,
                    ]
                ]
            ]
        ]
    ]));
}