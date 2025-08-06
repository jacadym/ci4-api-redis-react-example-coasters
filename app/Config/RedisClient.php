<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class RedisClient extends BaseConfig
{
    public string $uri = 'redis://localhost:6379';

    public string $prefixHash = '';
    public string $prefixKey = '';
}
