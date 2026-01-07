@vite(['resources/css/filament/admin/theme.css'])
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- TAGS/BADGES -->
        <div class="flex flex-wrap gap-2">
            @foreach(($record->tags ?? []) as $tag)
                <span class="fi-badge bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ $tag }}</span>
            @endforeach
            @if($record->platform)
                <span class="fi-badge bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ $record->platform }}</span>
            @endif
        </div>

        <!-- CONTAINER DO RELATÓRIO -->
        <div x-data="{ loaded: false }" class="relative w-full bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Botões de ação -->
            <div class="flex justify-end p-4">
                <div class="flex gap-4">
                    <button type="button"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        @click="const iframe = document.getElementById('reportFrame'); if (iframe.requestFullscreen) { iframe.requestFullscreen(); } else if (iframe.webkitRequestFullscreen) { iframe.webkitRequestFullscreen(); } else if (iframe.msRequestFullscreen) { iframe.msRequestFullscreen(); }"
                    >
                        Tela Cheia
                    </button>
                </div>
            </div>
            <!-- LOADER COM ANIMAÇÃO -->
            <div x-show="!loaded" class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 z-10"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="flex flex-col items-center space-y-4 max-w-xs text-center">
                    <div class="relative">
                        <svg class="w-20 h-20 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-1 12H5V8h14v10z"/>
                            <path d="M7 10h2v5H7zm3 2h2v3h-2zm3-1h2v4h-2z" fill="currentColor" opacity="0.7"/>
                        </svg>
                        <div class="absolute -top-1 -right-1">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Carregando Relatório...</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Conectando-se à fonte</p>
                    </div>
                    <div class="w-48 h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                    </div>
                </div>
            </div>
            <!-- IFRAME RESPONSIVO -->
            <iframe
                id="reportFrame"
                src="{{ $record->url }}"
                class="w-full h-[70vh] sm:h-[75vh] lg:h-[80vh] border-0 block"
                loading="lazy"
                x-on:load="loaded = true"
                allow="fullscreen"
            ></iframe>
        </div>

        <!-- INFORMAÇÕES RODAPÉ -->
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Organização: <strong class="text-gray-700 dark:text-gray-300">{{ $record->organization->name ?? '-' }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Criado por: <strong class="text-gray-700 dark:text-gray-300">{{ $record->creator->name ?? '-' }}</strong></span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
