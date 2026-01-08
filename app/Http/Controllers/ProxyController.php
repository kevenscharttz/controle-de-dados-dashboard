<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProxyController extends Controller
{
    public function metabase(Request $request, string $path = '')
    {
        if (!config('services.metabase.proxy_enabled')) {
            abort(404);
        }

        $base = rtrim((string) config('services.metabase.proxy_base'), '/');
        if ($base === '') {
            abort(404);
        }

        $allowedHosts = (array) config('services.metabase.allowed_hosts', []);
        $baseHost = parse_url($base, PHP_URL_HOST);
        if (!empty($allowedHosts) && $baseHost && !in_array($baseHost, $allowedHosts, true)) {
            abort(403, 'Upstream host not allowed');
        }

        $upstreamUrl = $base . '/' . ltrim($path, '/');
        $qs = $request->getQueryString();
        if ($qs) {
            $upstreamUrl .= '?' . $qs;
        }

        $response = Http::withOptions([
            // Some internal CAs may not be trusted; allow disabling verification via upstream certs
            'verify' => true,
        ])->withHeaders([
            'Accept' => '*/*',
            'User-Agent' => 'DashboardProxy/1.0',
        ])->get($upstreamUrl);

        $status = $response->status();
        $contentType = $response->header('Content-Type', 'text/html; charset=UTF-8');
        $body = $response->body();

        // Rewrite relative absolute-path urls in HTML and CSS so assets are fetched through the proxy
        $proxyBasePath = url('/proxy/metabase');
        if (is_string($body) && (str_contains($contentType, 'text/html') || str_contains($contentType, 'text/css'))) {
            // href="/path" or src="/path"
            $body = preg_replace('#(href|src)=["\'](\/[^"\']*)["\']#i', '$1="' . $proxyBasePath . '$2"', $body);
            // url(/path) in CSS
            $body = preg_replace('#url\((\/[\)\s]+[^\)]*)\)#i', 'url(' . $proxyBasePath . '$1)', $body);
        }

        return response($body, $status)->withHeaders([
            'Content-Type' => $contentType,
            // Intentionally do not pass through X-Frame-Options to allow embedding in panel
        ]);
    }
}
