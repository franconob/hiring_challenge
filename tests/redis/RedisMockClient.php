<?php
/**
 * Created by PhpStorm.
 * User: fherrero
 * Date: 6/6/16
 * Time: 7:24 PM
 */

namespace tests\redis;

use M6Web\Component\RedisMock\RedisMock;

class RedisMockClient extends RedisMock
{

    public function set($key, $value, $seconds = null)
    {
        parent::set($key, serialize($value), $seconds);
    }

    public function get($key)
    {
        $val = parent::get($key);
        return unserialize($val);
    }

    public function mget($fields)
    {
        $data = [];
        foreach ($fields as $field) {
            $data[] = $this->get($field);
        }

        return $data;
    }
}