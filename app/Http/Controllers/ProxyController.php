<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProxyController extends Controller
{
    /**
     * Generic, zero-config HTTPS proxy. Takes any http/https URL via query string
     * and returns its content, rewriting asset URLs so the page works inside an iframe
     * on HTTPS panels without mixed-content issues. Includes SSRF protections.
     */
    public function fetch(Request $request)
    {
        $raw = (string) $request->query('url', '');
        if ($raw === '') {
            abort(400, 'Missing url');
        }

        $parts = parse_url($raw);
        $scheme = $parts['scheme'] ?? '';
        $host = $parts['host'] ?? '';
        if (!in_array($scheme, ['http', 'https'], true) || empty($host)) {
            abort(400, 'Invalid url');
        }

        // Basic SSRF protections: block localhost and private/reserved IPs
        $blockedHosts = ['localhost', '127.0.0.1', '::1'];
        if (in_array(strtolower($host), $blockedHosts, true)) {
            abort(403);
        }
        $ips = @gethostbynamel($host) ?: [];
        foreach ($ips as $ip) {
            if ($this->isPrivateOrReservedIp($ip)) {
                abort(403);
            }
        }

        // Forward request
        $upstream = $raw;
        $resp = Http::withHeaders([
            'Accept' => '*/*',
            'User-Agent' => 'DashboardProxy/1.0',
        ])->get($upstream);

        $status = $resp->status();
        $contentType = $resp->header('Content-Type', 'text/html; charset=UTF-8');
        $body = $resp->body();

        // Only rewrite for text responses
        if (is_string($body) && (str_contains($contentType, 'text/html') || str_contains($contentType, 'text/css') || str_contains($contentType, 'application/javascript'))) {
            $origin = ($scheme . '://' . $host);
            $originWithPort = $origin;
            if (!empty($parts['port'])) {
                $originWithPort .= ':' . $parts['port'];
            }
            $originPath = $parts['path'] ?? '/';

            $proxyFetch = function (string $url) {
                $encoded = urlencode($url);
                return route('proxy.fetch') . '?url=' . $encoded;
            };

            // Build absolute URLs from relative paths using the origin path as base
            $absolutize = function (string $relative) use ($originWithPort, $originPath) {
                $relative = trim($relative);
                if ($relative === '' || $relative[0] === '#') return $relative;
                if (preg_match('#^(?:data:|mailto:|javascript:)#i', $relative)) return $relative;
                if (str_starts_with($relative, '//')) {
                    // Protocol-relative: default to https
                    return 'https:' . $relative;
                }
                if (preg_match('#^https?://#i', $relative)) return $relative;
                if ($relative[0] === '/') {
                    return $originWithPort . $relative;
                }
                // Base directory of origin path
                $baseDir = rtrim(str_replace('\\', '/', dirname($originPath)), '/');
                if ($baseDir === '') $baseDir = '/';
                $combined = $baseDir . '/' . $relative;
                // Normalize ./ and ../ segments
                $segments = [];
                foreach (explode('/', $combined) as $seg) {
                    if ($seg === '' || $seg === '.') continue;
                    if ($seg === '..') { array_pop($segments); continue; }
                    $segments[] = $seg;
                }
                $normalized = '/' . implode('/', $segments);
                return $originWithPort . $normalized;
            };

            // 1) Remove meta CSP that blocks iframe inline (header CSP is already dropped)
            if (str_contains($contentType, 'text/html')) {
                $body = preg_replace('#<meta\s+http-equiv=[\"\']Content-Security-Policy[\"\'][^>]*>#i', '', $body);
            }

            // 2) Root-relative URLs: href="/x" or src="/x" => route proxy(origin + path)
            $body = preg_replace_callback('#(href|src)=([\"\'])(/[^\"\']*)\2#i', function ($m) use ($originWithPort, $proxyFetch) {
                $absolute = $originWithPort . $m[3];
                return $m[1] . '=' . $m[2] . $proxyFetch($absolute) . $m[2];
            }, $body);

            // 3) Absolute http(s) links: rewrite http://... and https://... to proxy (helps mixed content)
            $body = preg_replace_callback('#(href|src)=([\"\'])(https?:\/\/[^\"\']*)\2#i', function ($m) use ($proxyFetch) {
                return $m[1] . '=' . $m[2] . $proxyFetch($m[3]) . $m[2];
            }, $body);

            // 4) CSS url(/x) => url(proxy(origin + path))
            $body = preg_replace_callback('#url\((\s*)(/[^)\s\"\']+)(\s*)\)#i', function ($m) use ($originWithPort, $proxyFetch) {
                $absolute = $originWithPort . $m[2];
                return 'url(' . $proxyFetch($absolute) . ')';
            }, $body);

            // 5) CSS url(http...) => url(proxy(http...))
            $body = preg_replace_callback('#url\((\s*)(https?:[^)\s\"\']+)(\s*)\)#i', function ($m) use ($proxyFetch) {
                return 'url(' . $proxyFetch($m[2]) . ')';
            }, $body);
            // 5.1) CSS url(//host/...) => url(proxy(https://host/...))
            $body = preg_replace_callback('#url\((\s*)(//[^)\s\"\']+)(\s*)\)#i', function ($m) use ($proxyFetch) {
                return 'url(' . $proxyFetch('https:' . $m[2]) . ')';
            }, $body);
            // 5.2) CSS url(relative) => absolutize then proxy
            $body = preg_replace_callback('#url\((\s*)(?!data:|https?://|//|/)([^)\s\"\']+)(\s*)\)#i', function ($m) use ($absolutize, $proxyFetch) {
                $absolute = $absolutize($m[2]);
                return 'url(' . $proxyFetch($absolute) . ')';
            }, $body);
            // 3.1) Protocol-relative //host/... => proxy with https
            $body = preg_replace_callback('#(href|src)=([\"\'])(\/\/[^\"\']*)\2#i', function ($m) use ($proxyFetch) {
                $absolute = 'https:' . $m[3];
                return $m[1] . '=' . $m[2] . $proxyFetch($absolute) . $m[2];
            }, $body);

            // 3.2) Relative links: href="file.js" or src="assets/app.css" => absolutize to origin then proxy
            $body = preg_replace_callback('#(href|src)=([\"\'])(?!data:|mailto:|javascript:|https?://|//|/)([^\"\']+)\2#i', function ($m) use ($absolutize, $proxyFetch) {
                $absolute = $absolutize($m[3]);
                return $m[1] . '=' . $m[2] . $proxyFetch($absolute) . $m[2];
            }, $body);
        }

        return response($body, $status)->withHeaders([
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * Path-preserving universal proxy. Keeps scheme/host/path in URL so browsers
     * resolve relative module imports and assets naturally. Example:
     *   /proxy/u/http/example.com/app.js -> upstream http://example.com/app.js
     */
    public function universal(Request $request, string $scheme, string $host, string $path = '')
    {
        $scheme = strtolower($scheme);
        if (!in_array($scheme, ['http', 'https'], true)) {
            abort(400, 'Invalid scheme');
        }

        $hostParam = $host;
        // Extract host and optional port
        $port = null;
        if (str_contains($hostParam, ':')) {
            [$hostParam, $port] = explode(':', $hostParam, 2);
        }
        $hostParam = strtolower($hostParam);
        if ($hostParam === '' || preg_match('#\s#', $hostParam)) {
            abort(400, 'Invalid host');
        }

        // SSRF protections: block localhost and private/reserved IPs
        $blockedHosts = ['localhost', '127.0.0.1', '::1'];
        if (in_array($hostParam, $blockedHosts, true)) {
            abort(403);
        }
        $ips = @gethostbynamel($hostParam) ?: [];
        foreach ($ips as $ip) {
            if ($this->isPrivateOrReservedIp($ip)) {
                abort(403);
            }
        }

        $upstream = $scheme . '://' . $hostParam . ($port ? (':' . $port) : '') . '/' . ltrim($path, '/');
        $qs = $request->getQueryString();
        if ($qs) {
            $upstream .= '?' . $qs;
        }

        $resp = Http::withHeaders([
            'Accept' => '*/*',
            'User-Agent' => 'DashboardProxy/1.0',
        ])->get($upstream);

        $status = $resp->status();
        $contentType = $resp->header('Content-Type', 'text/html; charset=UTF-8');
        $body = $resp->body();

        $proxyBase = url('/proxy/u/' . $scheme . '/' . $host);
        $originWithPort = $scheme . '://' . $host;
        $originPath = '/' . ltrim($path, '/');

        if (is_string($body) && (str_contains($contentType, 'text/html') || str_contains($contentType, 'text/css') || str_contains($contentType, 'application/javascript'))) {
            // Strip inline meta CSP to reduce iframe blocking
            if (str_contains($contentType, 'text/html')) {
                $body = preg_replace('#<meta\s+http-equiv=[\"\']Content-Security-Policy[\"\'][^>]*>#i', '', $body);
            }

            // Helper to absolutize relative paths against originPath
            $absolutize = function (string $relative) use ($originWithPort, $originPath) {
                $relative = trim($relative);
                if ($relative === '' || $relative[0] === '#') return $relative;
                if (preg_match('#^(?:data:|mailto:|javascript:)#i', $relative)) return $relative;
                if (str_starts_with($relative, '//')) {
                    return 'https:' . $relative;
                }
                if (preg_match('#^https?://#i', $relative)) return $relative;
                if ($relative[0] === '/') {
                    return $originWithPort . $relative;
                }
                $baseDir = rtrim(str_replace('\\', '/', dirname($originPath)), '/');
                if ($baseDir === '') $baseDir = '/';
                $combined = $baseDir . '/' . $relative;
                $segments = [];
                foreach (explode('/', $combined) as $seg) {
                    if ($seg === '' || $seg === '.') continue;
                    if ($seg === '..') { array_pop($segments); continue; }
                    $segments[] = $seg;
                }
                $normalized = '/' . implode('/', $segments);
                return $originWithPort . $normalized;
            };

            $rewriteToUniversal = function (string $url) {
                $p = parse_url($url);
                if (!$p || empty($p['scheme']) || empty($p['host'])) return $url;
                $scheme2 = strtolower($p['scheme']);
                $host2 = $p['host'] . (isset($p['port']) ? (':' . $p['port']) : '');
                $path2 = '/' . ltrim($p['path'] ?? '/', '/');
                $query2 = isset($p['query']) ? ('?' . $p['query']) : '';
                return url('/proxy/u/' . $scheme2 . '/' . $host2 . $path2) . $query2;
            };

            // href/src with root-relative
            $body = preg_replace('#(href|src)=["\'](\/[^"\']*)["\']#i', '$1="' . $proxyBase . '$2"', $body);

            // href/src with protocol-relative //host/... => https
            $body = preg_replace_callback('#(href|src)=(["\'])(\/\/[^"\']*)\2#i', function ($m) use ($rewriteToUniversal) {
                return $m[1] . '=' . $m[2] . $rewriteToUniversal('https:' . $m[3]) . $m[2];
            }, $body);

            // href/src with absolute http(s)
            $body = preg_replace_callback('#(href|src)=(["\'])(https?:\/\/[^"\']*)\2#i', function ($m) use ($rewriteToUniversal) {
                return $m[1] . '=' . $m[2] . $rewriteToUniversal($m[3]) . $m[2];
            }, $body);

            // href/src with relative (no leading /)
            $body = preg_replace_callback('#(href|src)=(["\'])(?!data:|mailto:|javascript:|https?://|//|/)([^"\']+)\2#i', function ($m) use ($absolutize, $rewriteToUniversal) {
                return $m[1] . '=' . $m[2] . $rewriteToUniversal($absolutize($m[3])) . $m[2];
            }, $body);

            // CSS url(/x)
            $body = preg_replace('#url\((\s*)(/[^)\s"\']+)(\s*)\)#i', 'url(' . $proxyBase . '$2)', $body);

            // CSS url(//host/...) => https
            $body = preg_replace_callback('#url\((\s*)(//[^)\s"\']+)(\s*)\)#i', function ($m) use ($rewriteToUniversal) {
                return 'url(' . $rewriteToUniversal('https:' . $m[2]) . ')';
            }, $body);

            // CSS url(http...)
            $body = preg_replace_callback('#url\((\s*)(https?:[^)\s"\']+)(\s*)\)#i', function ($m) use ($rewriteToUniversal) {
                return 'url(' . $rewriteToUniversal($m[2]) . ')';
            }, $body);

            // CSS url(relative)
            $body = preg_replace_callback('#url\((\s*)(?!data:|https?://|//|/)([^)\s"\']+)(\s*)\)#i', function ($m) use ($absolutize, $rewriteToUniversal) {
                return 'url(' . $rewriteToUniversal($absolutize($m[2])) . ')';
            }, $body);
        }

        return response($body, $status)->withHeaders([
            'Content-Type' => $contentType,
        ]);
    }

    private function isPrivateOrReservedIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            $ranges = [
                ['10.0.0.0', '10.255.255.255'],
                ['172.16.0.0', '172.31.255.255'],
                ['192.168.0.0', '192.168.255.255'],
                ['127.0.0.0', '127.255.255.255'],
            ];
            foreach ($ranges as [$start, $end]) {
                if ($long >= ip2long($start) && $long <= ip2long($end)) return true;
            }
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Block localhost & unique local addresses
            if ($ip === '::1' || str_starts_with(strtolower($ip), 'fc') || str_starts_with(strtolower($ip), 'fd')) {
                return true;
            }
        }
        return false;
    }
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
