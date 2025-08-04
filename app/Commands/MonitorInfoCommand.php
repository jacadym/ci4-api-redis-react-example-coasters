<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MonitorInfoCommand extends BaseCommand
{
    protected $group = 'Monitoring';
    protected $name = 'monitor:info';
    protected $description = 'Display information about coasters and wagons';
    protected $usage = 'monitor:info [arguments] [options]';
    protected $arguments = [];
    protected $options = [];

    public function run(array $params): int
    {
        CLI::write('   PHP Version: ' . CLI::color(PHP_VERSION, 'light_blue'));
        CLI::write('    CI Version: ' . CLI::color(\CodeIgniter\CodeIgniter::CI_VERSION, 'green'));
        CLI::write('       APPPATH: ' . CLI::color(APPPATH, 'yellow'));
        CLI::write('    SYSTEMPATH: ' . CLI::color(SYSTEMPATH, 'yellow'));
        CLI::write('      ROOTPATH: ' . CLI::color(ROOTPATH, 'yellow'));
        CLI::write('Included files: ' . CLI::color(count(get_included_files()), 'light_purple'));

        return EXIT_SUCCESS;
    }
}
