<?php

namespace Config;

use App\Config\RedisClient;
use Clue\React\Redis\RedisClient as ReactRedisClient;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    /**
     * @return ReactRedisClient|object
     */
    public static function reactredis(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('reactredis');
        }

        $config = config(RedisClient::class);

        return new ReactRedisClient($config->uri);
    }
}
