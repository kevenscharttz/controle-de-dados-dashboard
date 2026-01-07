@php($appName = trim(config('app.name', 'NewUrl')))
<x-layouts.marketing :title="'Início'" :metaDescription="'Tenha dashboards e relatórios organizados por organização, com controle de acesso e uploads simples.'">
    <header class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/default-organization-logo.svg') }}" class="h-8 w-8" alt="Logo">
            <span class="font-semibold text-lg">{{ $appName }}</span>
        </div>
        <nav class="flex items-center gap-2">
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-md border border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-900">Entrar</a>
            @endif
        </nav>
    </header>

    <main>
        <section class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 py-16 lg:py-24 grid lg:grid-cols-2 gap-10 items-center">
                <div>
                    <h1 class="text-4xl lg:text-5xl font-semibold tracking-tight">Centralize seus dashboards e relatórios por organização</h1>
                    <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">Gerencie usuários, permissões e arquivos com segurança. Upload de logos, visualizações otimizadas e um painel moderno com Filament.</p>
                    <div class="mt-8 flex items-center gap-3">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="px-5 py-3 rounded-md bg-slate-900 text-white hover:bg-black dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200 font-medium">Entrar no painel</a>
                        @endif
                        <a href="#features" class="px-5 py-3 rounded-md border border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-900">Ver recursos</a>
                    </div>
                    <div class="mt-6 text-sm text-slate-500 dark:text-slate-400">Acesso restrito. Solicite convite ao administrador.</div>
                </div>
                <div class="relative">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-900/60 backdrop-blur p-4 shadow-sm">
                        <img src="/images/default-organization-logo.svg" alt="Preview" class="mx-auto h-32 w-auto opacity-90">
                        <div class="mt-4 grid grid-cols-3 gap-3 text-center text-sm">
                            <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800">Dashboards</div>
                            <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800">Relatórios</div>
                            <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800">Organizações</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-3 gap-6">
                <div class="p-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                    <h3 class="font-semibold text-lg">Multi-organização</h3>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">Controle de acesso por organização com Policies e relacionamentos nativos.</p>
                </div>
                <div class="p-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                    <h3 class="font-semibold text-lg">Uploads simples</h3>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">Uploads públicos no disco <code>public</code> com preview e editor de imagem do Filament.</p>
                </div>
                <div class="p-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                    <h3 class="font-semibold text-lg">Performance</h3>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">Consultas otimizadas com eager loading e contagens agregadas.</p>
                </div>
            </div>
        </section>

        <section class="border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-6 py-14 grid lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h3 class="text-2xl font-semibold">Pronto para começar?</h3>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">Acesse o painel e gerencie suas organizações e dashboards.</p>
                </div>
                <div class="flex lg:justify-end">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="px-5 py-3 rounded-md bg-slate-900 text-white hover:bg-black dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200 font-medium">Entrar</a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-6 py-8 text-sm text-slate-500 dark:text-slate-400 flex items-center justify-between">
            <span>© {{ date('Y') }} {{ $appName }}</span>
            <span>Feito com Laravel + Filament</span>
        </div>
    </footer>
</x-layouts.marketing>
