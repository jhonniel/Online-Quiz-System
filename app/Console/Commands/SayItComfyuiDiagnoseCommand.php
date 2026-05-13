<?php

namespace App\Console\Commands;

use App\Services\SayItImageGeneration\SayItImageSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SayItComfyuiDiagnoseCommand extends Command
{
    protected $signature = 'sayit:comfyui-diagnose';

    protected $description = 'Show resolved Say-it ComfyUI URL and test TCP/HTTP from this PHP process (same as Generate image)';

    public function handle(): int
    {
        $driver = SayItImageSettings::driver();
        $internal = SayItImageSettings::comfyuiInternalBaseUrl();
        $base = SayItImageSettings::comfyuiBaseUrl();
        $requestBase = SayItImageSettings::comfyuiRequestBaseUrl();
        $proxy = SayItImageSettings::comfyuiUsesPublicProxy();

        $this->line('Say-it image driver: <fg=cyan>'.$driver.'</>');
        $this->line('ComfyUI Internal API URL (DB / .env): <fg=cyan>'.($internal !== '' ? $internal : '(empty)').'</>');
        $this->line('ComfyUI Base URL: <fg=cyan>'.($base !== '' ? $base : '(empty)').'</>');
        $this->line('URL used for outbound HTTP: <fg=cyan>'.($requestBase !== '' ? $requestBase : '(empty)').'</>');
        $this->line('Uses /comfyui app proxy: <fg=cyan>'.($proxy ? 'yes' : 'no').'</>');
        $this->newLine();

        if ($driver !== 'comfyui') {
            $this->warn('Driver is not "comfyui". Switch it in Admin → Settings → Say-it → Backend, then run this command again.');

            return self::SUCCESS;
        }

        if ($requestBase === '') {
            $this->error('No ComfyUI URL is configured. Set Internal API URL (and Base URL if required) in Admin → Settings → Say-it.');

            return self::FAILURE;
        }

        $root = $proxy ? rtrim($requestBase, '/').'/comfyui' : rtrim($requestBase, '/');
        $url = $root.'/';
        $this->info('Testing GET '.$url.' (same process as php artisan / your web server)...');

        try {
            $response = Http::withOptions(['verify' => SayItImageSettings::comfyuiVerifySsl()])
                ->timeout(5)
                ->get($url);
            $code = $response->status();
            if ($response->successful() || $code === 405 || $code === 404) {
                $this->info('HTTP '.$code.' — ComfyUI responded. Generation from the app should work if the workflow file is valid.');

                return self::SUCCESS;
            }
            $this->warn('HTTP '.$code.' — host is reachable but returned an unexpected status.');
            $this->line(substr((string) $response->body(), 0, 200));

            return self::FAILURE;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $this->error('Request failed: '.$msg);
            $this->newLine();
            $this->line('This is the same failure as “Generate image” in the browser.');
            $this->line('• <fg=yellow>Connection refused</> means nothing is listening on that host:port from <options=bold>this machine</>. Starting ComfyUI (<fg=cyan>python main.py</> in the ComfyUI folder) fixes it for local dev.');
            $this->line('• If PHP runs inside <options=bold>Docker</> but ComfyUI runs on your Mac/Windows host, <fg=cyan>127.0.0.1</> points at the container, not the host. Set Internal API URL to <fg=cyan>http://host.docker.internal:8188</> in Admin, Save, then run <fg=cyan>php artisan sayit:comfyui-diagnose</> again <options=bold>inside the container</>.');
            $this->line('• Admin → Settings values are stored in the <options=bold>database</> and override <fg=cyan>.env</>. After editing .env only, run <fg=cyan>php artisan config:clear</> and ensure Admin does not still show the old URL.');
            $this->newLine();

            return self::FAILURE;
        }
    }
}
