<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class HorizonGuard extends Command
{
    protected $signature = 'horizonGuard';
    protected $description = 'Ensure Horizon is running, start it if not.';

    public function handle()
    {
        // Проверим, запущен ли Horizon
        $result = Process::run('pgrep -f "artisan horizon"');

        if ($result->successful()) {
            $this->info('Horizon is already running.');
            return 0;
        }

        $this->warn('Horizon is not running. Starting it...');

        // Запускаем Horizon в фоне
        $process = Process::start('php artisan horizon');
        // Небольшая пауза для инициализации
        sleep(2);

        $this->info('Horizon started.');
        return 0;
    }
}
