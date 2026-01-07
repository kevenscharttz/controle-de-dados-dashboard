<x-filament-panels::page>
    <div class="space-y-8 px-4 xl:px-6"> <!-- removed max-w constraint for full-width layout -->
        <!-- Header de boas-vindas + CTA -->
        <div class="rounded-2xl bg-gradient-to-r from-slate-900 to-slate-700 text-white p-6 md:p-8 shadow-sm dark:from-slate-800 dark:to-slate-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">Bem-vindo(a) ao Observatório de Dados</h1>
                    <p class="mt-1 text-slate-300">Centralize dashboards, relatórios e organizações em um só lugar.</p>
                </div>
                <div class="flex gap-3">
                    @if($canCreateDashboard)
                    <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white text-slate-900 font-medium hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H7a1 1 0 110-2h4V7a1 1 0 011-1z"/></svg>
                        Novo Dashboard
                    </a>
                    @endif
                    @if($canCreateReport)
                    <a href="{{ \App\Filament\Resources\Reports\ReportResource::getUrl('create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-white/30 text-white hover:bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/><path d="M14 3v4a1 1 0 001 1h4"/></svg>
                        Novo Relatório
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Métricas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php($metricClasses = 'rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm')
            <div class="{{ $metricClasses }}">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Dashboards</div>
                    <div class="h-9 w-9 rounded-lg bg-blue-600/10 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zM13 21h8v-10h-8v10zM13 3v6h8V3h-8z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold">{{ $dashboardsCount }}</div>
            </div>
            <div class="{{ $metricClasses }}">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Relatórios</div>
                    <div class="h-9 w-9 rounded-lg bg-emerald-600/10 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zM7 7h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold">{{ $reportsCount ?? 0 }}</div>
            </div>
            <div class="{{ $metricClasses }}">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Usuários</div>
                    <div class="h-9 w-9 rounded-lg bg-violet-600/10 text-violet-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path d="M2 20a10 10 0 1120 0v1H2v-1z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold">{{ $usersCount }}</div>
            </div>
            <div class="{{ $metricClasses }}">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-500">Organizações</div>
                    <div class="h-9 w-9 rounded-lg bg-amber-600/10 text-amber-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21V3h8v6h10v12H3zm10-2h6V11h-6v8zM5 19h4V5H5v14z"/></svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold">{{ $organizationsCount }}</div>
            </div>
        </div>

        <!-- Conteúdo em 2 colunas: recentes e ações rápidas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Dashboards recentes</h2>
                    <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('index') }}" class="text-sm text-blue-600 hover:underline">Ver todos</a>
                </div>
                @if($recentDashboards->isEmpty())
                    <div class="text-sm text-slate-500">Nenhum dashboard recente.</div>
                @else
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($recentDashboards as $d)
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $d->title }}</div>
                                    <div class="text-xs text-slate-500">{{ $d->organization?->name }} • {{ $d->created_at->diffForHumans() }}</div>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">{{ $d->views ?? '0' }} visualizações</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <h2 class="text-lg font-semibold">Ações rápidas</h2>
                <div class="mt-4 grid grid-cols-1 gap-3">
                    @if($canCreateDashboard)
                    <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('create') }}" class="inline-flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="h-8 w-8 rounded-md bg-blue-600/10 text-blue-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 6a1 1 0 011 1v4h4a1 1 0 110 2h-4v4a1 1 0 11-2 0v-4H7a1 1 0 110-2h4V7a1 1 0 011-1z"/></svg>
                        </span>
                        <span class="font-medium">Criar dashboard</span>
                    </a>
                    @endif
                    @if($canCreateReport)
                    <a href="{{ \App\Filament\Resources\Reports\ReportResource::getUrl('create') }}" class="inline-flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="h-8 w-8 rounded-md bg-emerald-600/10 text-emerald-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zM7 7h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/></svg>
                        </span>
                        <span class="font-medium">Criar relatório</span>
                    </a>
                    @endif
                    @if($canCreateOrganization)
                    <a href="{{ \App\Filament\Resources\Organizations\OrganizationResource::getUrl('create') }}" class="inline-flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="h-8 w-8 rounded-md bg-amber-600/10 text-amber-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21V3h8v6h10v12H3zm10-2h6V11h-6v8zM5 19h4V5H5v14z"/></svg>
                        </span>
                        <span class="font-medium">Criar organização</span>
                    </a>
                    @endif
                    @if($canManageUsers)
                    <a href="{{ \App\Filament\Resources\Users\UserResource::getUrl('index') }}" class="inline-flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <span class="h-8 w-8 rounded-md bg-violet-600/10 text-violet-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path d="M2 20a10 10 0 1120 0v1H2v-1z"/></svg>
                        </span>
                        <span class="font-medium">Gerenciar usuários</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
