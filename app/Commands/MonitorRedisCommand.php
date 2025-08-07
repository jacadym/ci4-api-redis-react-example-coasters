<?php

namespace App\Commands;

use Clue\React\Redis\RedisClient;
use CodeIgniter\CLI\BaseCommand;
use React\EventLoop\Loop;

class MonitorRedisCommand extends BaseCommand
{
    protected $group = 'Monitoring';
    protected $name = 'monitor:redis';
    protected $description = 'Interactive Redis Console';
    protected $usage = 'monitor:redis';
    protected $arguments = [];
    protected $options = [];

    public function run(array $params)
    {
        /** @var RedisClient $client */
        $client = service('reactredis')->getClient();

        Loop::addReadStream(STDIN, static function () use ($client) {
            $line = fgets(STDIN);
            if ($line === false || $line === '') {
                echo '# CTRL-D -> Ending connection...' . PHP_EOL;
                Loop::removeReadStream(STDIN);
                $client->end();

                return;
            }

            $line = rtrim($line);
            if ($line === '') {
                return;
            }

            $args = explode(' ', $line);
            $command = strtolower(array_shift($args));

            // special method such as end() / close() called
            if (in_array($command, ['end', 'close'])) {
                $client->{$command}();

                return;
            }

            $promise = $client->callAsync($command, ...$args);
            $promise->then(
                static function ($data): void {
                    echo '# Reply: ' . json_encode($data) . PHP_EOL;
                },
                static function (\Throwable $e): void {
                    echo '# Error Reply: ' . $e->getMessage() . PHP_EOL;
                }
            );
        });
        $client->on('close', static function(): void {
            echo '## DISCONNECTED' . PHP_EOL;
            Loop::removeReadStream(STDIN);
        });

        echo '## Entering interactive mode ready, hit CTRL-D to quit' . PHP_EOL;

        return EXIT_SUCCESS;
    }
}
