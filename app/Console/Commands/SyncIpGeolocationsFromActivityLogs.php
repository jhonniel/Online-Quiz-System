<?php

namespace App\Console\Commands;

use App\Support\IpGeolocationService;
use Illuminate\Console\Command;

class SyncIpGeolocationsFromActivityLogs extends Command
{
    protected $signature = 'ip-geolocations:sync-from-activity-logs {--limit=500 : Maximum distinct public IPs to resolve}';

    protected $description = 'Translate activity log IP addresses into stored map coordinates';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $missing = IpGeolocationService::distinctActivityIpsMissingCoordinates($limit);

        if ($missing->isEmpty()) {
            $backfilled = IpGeolocationService::backfillActivityCoordinatesFromStored();
            if ($backfilled > 0) {
                $this->info("Updated {$backfilled} activity log row(s) from stored IP coordinates.");
            } else {
                $this->info('All recent public activity log IPs already have stored coordinates.');
            }

            return self::SUCCESS;
        }

        $this->info('Resolving '.$missing->count().' IP address(es)...');

        $resolved = 0;
        foreach ($missing as $ip) {
            if (IpGeolocationService::resolve($ip) !== null) {
                $resolved++;
            }
        }

        $this->info("Stored coordinates for {$resolved} IP address(es).");

        $backfilled = IpGeolocationService::backfillActivityCoordinatesFromStored();
        $this->info("Updated {$backfilled} activity log row(s) with stored coordinates.");

        return self::SUCCESS;
    }
}
