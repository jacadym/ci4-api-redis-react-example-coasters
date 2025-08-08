<?php

namespace App\Libraries;

use Config\Custom as CustomConfig;
use Clue\React\Redis\RedisClient as ReactRedisClient;
use React\EventLoop\Loop;

class ReactRedis
{
    public const HASH_SEQUENCE  = 'hash_sequence';
    public const HASH_COASTER  = 'hash_coaster';
    public const HASH_WAGON  = 'hash_wagon';

    public const KEY_COASTER = 'coaster_%d';
    public const KEY_WAGON = 'wagon_%d_%d';

    /**
     * @var ReactRedisClient
     */
    private $client;

    /**
     * @var CustomConfig
     */
    private $config;

    public function __construct()
    {
        $this->config = config(CustomConfig::class);
        $this->client = new ReactRedisClient($this->config->redisUri);
    }

    /**
     * @return ReactRedisClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return CustomConfig|object|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getSequence(string $sequenceName): int
    {
        $hashName = $this->getHash(self::HASH_SEQUENCE);
        $keyName = $this->getKey($sequenceName);

        $value = 0;
        $client = $this->getClient();
        Loop::futureTick(function () use ($hashName, $keyName, &$value, $client): void {
            $client->hincrby($hashName, $keyName, 1)->then(
                static function (string $result) use (&$value): void {
                    $value = (int) $result;
                }
            );
        });
        Loop::run();

        return $value;
    }

    public function getData(string $hashName, string $fieldName): array
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $data = [];
        $client = $this->getClient();
        Loop::futureTick(function () use ($hashName, $keyName, &$data, $client): void {
            $client->hget($hashName, $keyName)->then(
                static function (string $result) use ($hashName, $keyName, &$data): void {
                    $data = self::unpack($result);
                }
            );
        });
        Loop::run();

        return $data;
    }

    public function createData(string $hashName, string $fieldName, array $data = []): void
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $client = $this->getClient();
        Loop::futureTick(function () use ($hashName, $keyName, &$data, $client): void {
            $client->hset($hashName, $keyName, self::pack($data))->then(
                static function (string $result): void {
                }
            );
        });
        Loop::run();
    }

    public function updateData(string $hashName, string $fieldName, array $data = []): void
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $client = $this->getClient();
        $client->hget($hashName, $keyName)->then(
            static function (string $result) use ($hashName, $keyName, $data, $client): void {
                $prevData = self::unpack($result);
                $newData = array_merge((array) $prevData, $data);
                $client->hset($hashName, $keyName, self::pack($newData))->then(
                    static function (string $result): void {
                    }
                );
            }
        );
    }

    public function deleteData(string $hashName, string $fieldName): void
    {
        $hashName = $this->getHash($hashName);
        $keyName = $this->getKey($fieldName);

        $client = $this->getClient();
        $client->hdel($hashName, $keyName);
    }

    public function getCollection(string $hashName): array
    {
        $hashName = $this->getHash($hashName);

        $collection = [];
        $client = $this->getClient();
        Loop::futureTick(function () use ($hashName, &$collection, $client): void {
            $client->hvals($hashName)->then(
                static function (array $result) use (&$collection): void {
                    foreach ($result as $data) {
                        $collection[] = self::unpack($data);
                    }
                }
            );
        });
        Loop::run();

        return $collection;
    }

    public static function pack(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '';
        }
    }

    public static function unpack(string $data): array
    {
        try {
            return json_decode($data, true);
        } catch (\Exception) {
            return [];
        }
    }

    public static function getKeyCoaster(int $coasterId): string
    {
        return sprintf(self::KEY_COASTER, $coasterId);
    }

    public static function getKeyWagon(int $coasterId, int $wagonId): string
    {
        return sprintf(self::KEY_WAGON, $coasterId, $wagonId);
    }

    private function getHash(string $hashName): string
    {
        return $this->config->redisPrefixHash . $hashName;
    }

    private function getKey(string $keyName): string
    {
        return $this->config->redisPrefixKey . $keyName;
    }
}
