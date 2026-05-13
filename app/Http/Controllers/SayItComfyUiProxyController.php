<?php

namespace App\Http\Controllers;

use App\Services\SayItImageGeneration\SayItImageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forwards /comfyui/* to ComfyUI on Internal API URL (same pattern as /sdapi for Automatic1111).
 */
class SayItComfyUiProxyController extends Controller
{
    public function forward(Request $request, ?string $path = null): Response
    {
        $upstream = SayItImageSettings::comfyuiInternalBaseUrl();
        if ($upstream === '') {
            abort(503, 'ComfyUI internal upstream is not configured.');
        }

        $path = $path !== null && $path !== '' ? $path : '';
        $target = rtrim($upstream, '/').($path !== '' ? '/'.$path : '');
        if (($qs = $request->getQueryString()) !== null && $qs !== '') {
            $target .= '?'.$qs;
        }

        $forwardHeaders = [];
        foreach (['Content-Type', 'Accept', 'Accept-Encoding'] as $h) {
            $v = $request->header($h);
            if (is_string($v) && $v !== '') {
                $forwardHeaders[$h] = $v;
            }
        }

        $verify = str_starts_with($upstream, 'https://')
            ? SayItImageSettings::comfyuiVerifySsl()
            : false;

        $timeout = max(120, SayItImageSettings::httpTimeout());

        try {
            $pending = Http::withOptions(['verify' => $verify])
                ->timeout($timeout)
                ->withHeaders($forwardHeaders);

            $upstreamResponse = $pending->send($request->method(), $target, [
                'body' => $request->getContent(),
            ]);
        } catch (\Throwable $e) {
            abort(502, 'Upstream ComfyUI unreachable: '.$e->getMessage());
        }

        $response = response($upstreamResponse->body(), $upstreamResponse->status());

        $ct = $upstreamResponse->header('Content-Type');
        if (is_string($ct) && $ct !== '') {
            $response->headers->set('Content-Type', $ct);
        }

        return $response;
    }
}
