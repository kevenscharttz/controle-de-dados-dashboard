@php
    /** @var \Illuminate\Database\Eloquent\Model|null $record */
    $url = $record?->url ?? null;
    $platform = $record?->platform ?? 'N/A';
    $title = $record?->title ?? 'Dashboard';
@endphp

@if(filled($url))
    <div class="space-y-4">
        <!-- Header do Dashboard -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $platform }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">•</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Renderização embutida</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="toggleFullscreen()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                        Tela Cheia
                    </button>
                    <a href="{{ $url }}" target="_blank" class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Abrir Original
                    </a>
                </div>
            </div>
        </div>

        <!-- Container do Iframe -->
        <div id="dashboard-container" class="relative bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="relative" style="padding-top: 56.25%; min-height: 500px;">
                <iframe 
                    id="dashboard-iframe"
                    src="{{ $url }}" 
                    class="absolute top-0 left-0 w-full h-full border-0"
                    sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-presentation"
                    loading="lazy"
                    title="{{ $title }} - {{ $platform }}"
                    onload="handleIframeLoad()"
                    onerror="handleIframeError()">
                </iframe>
            </div>
            
            <!-- Loading State -->
            <div id="loading-overlay" class="absolute inset-0 bg-white dark:bg-gray-800 flex items-center justify-center">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-400">Carregando dashboard...</p>
                </div>
            </div>

            <!-- Error State -->
            <div id="error-overlay" class="absolute inset-0 bg-white dark:bg-gray-800 flex items-center justify-center hidden">
                <div class="text-center p-6">
                    <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Erro ao carregar dashboard</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Não foi possível carregar o conteúdo. Verifique se a URL está acessível.</p>
                    <button onclick="retryLoad()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Tentar Novamente
                    </button>
                </div>
            </div>
        </div>

        <!-- Informações de Segurança -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Informações de Segurança</h4>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Este dashboard está sendo renderizado em um ambiente seguro com sandbox. 
                        Algumas funcionalidades interativas podem estar limitadas por questões de segurança.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleIframeLoad() {
            document.getElementById('loading-overlay').style.display = 'none';
            document.getElementById('error-overlay').classList.add('hidden');
        }

        function handleIframeError() {
            document.getElementById('loading-overlay').style.display = 'none';
            document.getElementById('error-overlay').classList.remove('hidden');
        }

        function retryLoad() {
            document.getElementById('loading-overlay').style.display = 'flex';
            document.getElementById('error-overlay').classList.add('hidden');
            document.getElementById('dashboard-iframe').src = document.getElementById('dashboard-iframe').src;
        }

        function toggleFullscreen() {
            const container = document.getElementById('dashboard-container');
            if (!document.fullscreenElement) {
                container.requestFullscreen().catch(err => {
                    console.log('Erro ao entrar em tela cheia:', err);
                });
            } else {
                document.exitFullscreen();
            }
        }

        // Auto-hide loading after 10 seconds
        setTimeout(() => {
            const loading = document.getElementById('loading-overlay');
            if (loading && loading.style.display !== 'none') {
                handleIframeError();
            }
        }, 10000);
    </script>
@else
    <div class="text-center py-12">
        <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">URL não configurada</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Este dashboard não possui uma URL de visualização configurada.</p>
        <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('edit', ['record' => $record->id]) }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Configurar URL
        </a>
    </div>
@endif
