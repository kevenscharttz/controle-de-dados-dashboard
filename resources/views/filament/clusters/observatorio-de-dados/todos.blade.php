<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Observatório_de_Dados</h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ now()->translatedFormat('d \d\e F \d\e Y') }}
            </div>
        </div>

        @php
            /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Dashboard> $dashboards */
            $dashboards = $dashboards ?? collect();
        @endphp

        @if($dashboards->isEmpty())
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-gray-600 dark:text-gray-300">
                Nenhum dashboard disponível para você no momento.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ $record->title }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->organization->name ?? '-' }}</p>
                            </div>
                            <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('view', ['record' => $record->id]) }}" class="text-xs px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">
                                Abrir página
                            </a>
                        </div>

                        <div x-data="{ loaded: false }" class="relative">
                            <div x-show="!loaded" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900/50 z-10">
                                <div class="animate-pulse w-24 h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded"></div>
                            </div>
                            <iframe
                                src="{{ $iframeUrl }}"
                                class="w-full h-[45vh] sm:h-[50vh] border-0 block"
                                loading="lazy"
                                x-on:load="loaded = true"
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
    </div>
</x-filament-panels::page>
