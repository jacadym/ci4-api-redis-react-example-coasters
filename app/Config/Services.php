<?php

namespace Config;

use App\Libraries\ReactRedis;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    /**
     * @return ReactRedis|object
     */
    public static function reactredis(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('reactredis');
        }

        return new ReactRedis();
    }
}
