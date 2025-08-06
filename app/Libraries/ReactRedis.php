<?php

namespace App\Libraries;

use App\Config\RedisClient as RedisClientConfig;
use Clue\React\Redis\RedisClient as ReactRedisClient;

class ReactRedis
{
    public const HASH_SEQUENCE  = 'hash_sequence';
    public const HASH_COASTER  = 'hash_coaster';
    public const HASH_WAGON  = 'hash_wagon';

    public const KEY_COASTER = 'coaster:%d';
    public const KEY_WAGON = 'wagon:%d:%d';

    /**
     * @var ReactRedisClient
     */
    private $client;

    /**
     * @var RedisClientConfig
     */
    private $config;

    public function __construct()
    {
        $this->config = config(RedisClientConfig::class);
        $this->client = new ReactRedisClient($this->config->uri);
    }

    /**
     * @return ReactRedisClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return RedisClientConfig|object|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getSequence(string $sequenceName): int
    {
        $value = 0;
        $hashName = $this->getHash(self::HASH_SEQUENCE);
        $keyName = $this->getKey($sequenceName);

        $this->client->hincrby($hashName, $keyName, 1)->then(
            static function (string $result) use (&$value): void {
                $value = (int) $result;
            }
        );

        return $value;
    }

    public function getData(string $hashName, string $fieldName): array
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $data = [];
        $this->client->hget($hashName, $keyName)->then(
            static function (string $result) use ($hashName, $keyName, &$data): void {
                $data = $this->unpack($result);
            }
        );

        return $data;
    }

    public function createData(string $hashName, string $fieldName, array $data = []): void
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $this->client->hset($hashName, $keyName, $this->pack($data));
    }

    public function updateData(string $hashName, string $fieldName, array $data = []): void
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $this->client->hget($hashName, $keyName)->then(
            static function (string $result) use ($hashName, $keyName, $data): void {
                $prevData = $this->unpack($result);
                $newData = array_merge((array) $prevData, $data);
                $this->client->hset($hashName, $keyName, $this->pack($newData));
            }
        );
    }

    public function deleteData(string $hashName, string $fieldName): void
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $this->client->hdel($hashName, $keyName);
    }

    public function getCollection(string $hashName): array
    {
        $hashName = $this->getHash($hashName);

        $collection = [];
        $this->client->hvals($hashName)->then(
            static function (array $result) use (&$collection): void {
                foreach ($result as $data) {
                    $collection[] = $this->unpack($data);
                }
            }
        );

        return $collection;
    }

    public function pack(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '';
        }
    }

    public function unpack(string $data): array
    {
        try {
            return json_decode($data, true);
        } catch (\Exception) {
            return [];
        }
    }

    private function getHash(string $hashName): string
    {
        return $this->config->prefixHash . $hashName;
    }

    private function getKey(string $keyName): string
    {
        return $this->config->prefixKey . $keyName;
    }
}
