<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Custom extends BaseConfig
{
    public string $redisUri = 'redis://localhost:6379';

    public string $redisPrefixHash = '';

    public string $redisPrefixKey = '';

    public string $allowIps = '';
}
