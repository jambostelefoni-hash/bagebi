<?php

namespace App\Console\Commands;

use App\Services\DataQualityScanner;
use Illuminate\Console\Command;

class ScanDataQuality extends Command
{
    protected $signature = 'data-quality:scan';
    protected $description = 'Scan platform records for data quality problems';

    public function handle(DataQualityScanner $scanner): int
    {
        $scan = $scanner->run('scheduled');
        $this->info('Data quality scan completed: '.$scan->issues_found.' issue(s).');
        return 0;
    }
}
