<?php

namespace App\Commands;

use App\Entities\CoasterCollection;
use App\Entities\CoasterDTO;
use App\Entities\WagonDTO;
use App\Libraries\CoasterValidator;
use App\Libraries\ReactRedis;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;
use React\EventLoop\Loop;

class MonitorStatsCommand extends BaseCommand
{
    private const REPORT = '
    [Kolejka {coasterId}]
      1. Godziny działania: {startTime} - {endTime}
      2. Liczba wagonów: {wagonsCount}/{requiredWagons}
      3. Dostępny personel: {staffCount}/{requiredStaff}
      4. Klienci dziennie: {customerCount}
      5. {status}';

    protected $group = 'Monitoring';
    protected $name = 'monitor:stats';
    protected $description = 'Stats Console';
    protected $usage = 'monitor:stats';
    protected $arguments = [];
    protected $options = [];

    private static array $logged = [];

    public function run(array $params)
    {
        /** @var ReactRedis $redis */
        $redis = service('reactredis');
        /** @var CoasterValidator $validator */
        $validator = service('coastervalidator');
        $logger = $this->logger;

        Loop::addPeriodicTimer(0.5, static function () use ($redis, $validator, $logger) {
            $collection = CoasterCollection::create();

            $redis->getClient()->hvals($redis->getHash(ReactRedis::HASH_COASTER))->then(
                static function (array $result) use ($collection): void {
                    foreach ($result as $data) {
                        $collection->add(CoasterDTO::create(ReactRedis::unpack($data)));
                    }
                }
            );
            $redis->getClient()->hvals($redis->getHash(ReactRedis::HASH_WAGON))->then(
                static function (array $result) use ($collection, $validator, $logger): void {
                    foreach ($result as $data) {
                        $collection->addWagon(WagonDTO::create(ReactRedis::unpack($data)));
                    }
                    self::displayCollection($collection, $validator, $logger);
                }
            );
        });
        Loop::run();

        return EXIT_SUCCESS;
    }

    public static function displayCollection(CoasterCollection $collection, $validator, $logger): void
    {
        CLI::write("\033[H\033[J"); // Clear screen
        CLI::write('Naciśnij Ctrl-C aby zakończyć', 'yellow');
        CLI::write('=============================', 'yellow');
        CLI::write(sprintf('[Godzina: %s]', date('H:i:s')), 'green');
        /** @var CoasterDTO $coaster */
        foreach ($collection->get() as $coaster) {
            $validator->setCoaster($coaster);
            $status = $validator->getStatusString();
            $infoText = str_replace([
                '{coasterId}',
                '{startTime}',
                '{endTime}',
                '{wagonsCount}',
                '{requiredWagons}',
                '{staffCount}',
                '{requiredStaff}',
                '{customerCount}',
                '{status}'
            ], [
                $coaster->getCoasterId(),
                $coaster->getStartTime(),
                $coaster->getEndTime(),
                $coaster->getWagonsCount(),
                $validator->getRequiredWagons(),
                $coaster->getStaffCount(),
                $validator->getRequiredStaff(),
                $coaster->getCustomerCount(),
                $status
            ], self::REPORT);
            CLI::write($infoText);

            // Log only if the status is a problem
            if (str_starts_with($status, 'Problem')) {
                $coasterId = $coaster->getCoasterId();
                $hash = md5($status);
                if (!isset(self::$logged[$coasterId]) || self::$logged[$coasterId] !== $hash) {
                    self::$logged[$coasterId] = $hash;
                    $logger->warning(sprintf('Kolejka %d - %s', $coasterId, $status));
                }
            }
        }
    }
}
