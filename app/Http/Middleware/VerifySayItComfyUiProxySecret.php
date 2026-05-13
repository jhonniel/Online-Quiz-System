<?php

namespace App\Http\Middleware;

use App\Services\SayItImageGeneration\SayItImageSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySayItComfyUiProxySecret
{
    public function handle(Request $request, Closure $next): Response
    {
        if (SayItImageSettings::comfyuiInternalBaseUrl() === '') {
            abort(404);
        }

        $expected = SayItImageSettings::sayItImageProxySecret();
        if ($expected === '') {
            abort(404);
        }

        $provided = (string) $request->header('X-SayIt-Image-Proxy-Secret', '');
        if ($provided === '') {
            $provided = (string) $request->header('X-SayIt-Sd-Proxy-Secret', '');
        }
        if ($provided === '' || ! hash_equals($expected, $provided)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
