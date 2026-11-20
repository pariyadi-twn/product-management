<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa metrik performa dan aktivitas, lalu buat peringatan bila ambang batas terlampaui';

    /**
     * Execute the console command.
     */
    public function handle(AlertService $alerts): int
    {
        $triggered = $alerts->evaluate();

        if ($triggered === []) {
            $this->info('Tidak ada peringatan baru. Semua metrik berada di bawah ambang batas.');

            return self::SUCCESS;
        }

        $this->warn(count($triggered).' peringatan aktif setelah pemeriksaan:');

        foreach ($triggered as $alert) {
            $this->line(sprintf(
                ' [%s] %s — %s',
                strtoupper($alert->severity),
                $alert->typeLabel(),
                $alert->message,
            ));
        }

        return self::SUCCESS;
    }
}
