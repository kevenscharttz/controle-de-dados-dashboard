<x-filament-panels::page>
    @php /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Dashboard> $dashboards */ @endphp

    <div class="w-full px-0 sm:px-2">

    @if($dashboards->isEmpty())
        <div class="rounded-lg border border-gray-200 p-6 text-gray-600">
            Nenhum dashboard nesta pasta.
        </div>
    @else
    <div class="grid grid-cols-1 gap-6">
            @foreach($dashboards as $record)
                @php
                    $rawUrl = $record->url ?? '';
                    $iframeUrl = $rawUrl;
                    $isSecure = request()->isSecure() || strtolower(request()->header('x-forwarded-proto', '')) === 'https';
                    $isHttpLike = is_string($rawUrl) && (str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://'));
                    if ($isSecure && is_string($rawUrl) && str_starts_with($rawUrl, 'http://')) {
                        $p = parse_url($rawUrl);
                        $scheme = strtolower($p['scheme'] ?? 'http');
                        $host = ($p['host'] ?? '');
                        $port = isset($p['port']) ? (string) $p['port'] : null;
                        $path = ltrim($p['path'] ?? '', '/');
                        $query = isset($p['query']) ? ('?' . $p['query']) : '';
                        $params = ['scheme' => $scheme, 'host' => $host, 'path' => $path];
                        if ($port) { $params['port'] = $port; }
                        $iframeUrl = route('proxy.universal', $params) . $query;
                    }
                    $tags = $record->tags ?? [];
                    if (is_string($tags)) {
                        $decoded = json_decode($tags, true);
                        $tags = is_array($decoded) ? $decoded : [];
                    }
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <div>
                            @php($viewUrl = \App\Filament\Resources\Dashboards\DashboardResource::getUrl('view', ['record' => $record]))
                            <a href="{{ $viewUrl }}" class="text-base font-semibold text-gray-800 dark:text-gray-100 hover:underline">
                                {{ $record->title }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->organization->name ?? '-' }}</p>
                        </div>
                        <a href="{{ $viewUrl }}" class="fi-btn fi-btn-primary inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold">
                            <x-filament::icon icon="heroicon-m-eye" class="w-4 h-4" />
                            Focar
                        </a>
                    </div>

                    <div x-data="{ loaded: false }" class="relative">
                        <div x-show="!loaded" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900/50 z-10">
                            <div class="animate-pulse w-24 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded"></div>
                        </div>
                        <iframe
                            src="about:blank"
                            data-src="{{ $iframeUrl }}"
                            class="w-full h-[55vh] sm:h-[60vh] border-0 block"
                            loading="lazy"
                            x-on:load="loaded = true"
                            referrerpolicy="no-referrer"
                            sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
                            allow="fullscreen"
                        ></iframe>
                    </div>

                    @if(!empty($tags))
                        <div class="px-4 py-3 flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                                <span class="fi-badge bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between">
                        <span>Plataforma: <strong class="text-gray-700 dark:text-gray-300">{{ $record->platform ?? '-' }}</strong></span>
                        <span>Autor: <strong class="text-gray-700 dark:text-gray-300">{{ $record->creator->name ?? '-' }}</strong></span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <script>
    (() => {
        const MAX_CONCURRENCY = 2; // quantos iframes carregam ao mesmo tempo
        const rootMargin = '600px'; // começa a enfileirar antes de entrar no viewport

        const queue = [];
        let active = 0;

        const startLoad = (el) => {
            if (!el || el.dataset._started) return;
            el.dataset._started = '1';
            const src = el.getAttribute('data-src');
            if (!src) return;
            el.addEventListener('load', () => {
                active = Math.max(0, active - 1);
                dequeue();
            }, { once: true });
            el.src = src;
            el.removeAttribute('data-src');
        };

        const dequeue = () => {
            while (active < MAX_CONCURRENCY && queue.length) {
                const el = queue.shift();
                active++;
                startLoad(el);
            }
        };

        const io = new IntersectionObserver((entries) => {
            for (const e of entries) {
                const el = e.target;
                if (e.isIntersecting) {
                    io.unobserve(el);
                    queue.push(el);
                    dequeue();
                }
            }
        }, { root: null, rootMargin, threshold: 0 });

        document.querySelectorAll('iframe[data-src]').forEach((el) => io.observe(el));
    })();
    </script>
</x-filament-panels::page>
