@php
    /** @var \Illuminate\Database\Eloquent\Collection $dashboards */
    $dashboards = $dashboards ?? \App\Models\Dashboard::orderBy('id', 'asc')->get();
    use \App\Models\User;
@endphp

<div class="fi-p-6">
    <div class="fi-flex fi-items-center fi-justify-between fi-mb-4">
        <h2 class="fi-text-xl fi-font-semibold">Dashboards</h2>
    </div>

    <div class="fi-grid fi-grid-cols-1 sm:fi-grid-cols-2 lg:fi-grid-cols-2 fi-gap-4">
        @foreach($dashboards as $d)
            @php
                $tags = $d->tags ?? [];
                if (is_string($tags)) {
                    // in case tags are stored as JSON string
                    $decoded = json_decode($tags, true);
                    $tags = is_array($decoded) ? $decoded : [];
                }
                $author = null;
                if (! empty($d->scope_user_id)) {
                    $author = User::find($d->scope_user_id);
                }
            @endphp

            <div class="fi-bg-white fi-shadow-sm fi-rounded-lg fi-overflow-hidden fi-border fi-border-gray-100">
                <div class="fi-p-4 fi-flex fi-flex-col fi-gap-4">
                    <div class="fi-flex fi-justify-between fi-items-start">
                        <div class="fi-pr-2">
                            <div class="fi-flex fi-items-start fi-gap-2">
                                <h3 class="fi-text-lg fi-font-semibold fi-leading-6">{{ $d->title }}</h3>
                                <button class="fi-ml-auto fi-text-gray-400 hover:fi-text-gray-600 fi-rounded-full" title="Mais opções" aria-label="Mais opções">
                                    <!-- three dots -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="fi-h-5 fi-w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="fi-text-sm fi-text-muted fi-mt-2">{{ $d->type ?? ($d->description ?? '') }}</p>
                        </div>

                        <div class="fi-text-right fi-text-xs fi-text-muted fi-min-w-[90px]">
                            <div class="fi-flex fi-items-center fi-gap-2 fi-justify-end">
                                @if($d->platform)
                                    <a href="#" class="fi-text-muted fi-flex fi-items-center fi-gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fi-h-4 fi-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/></svg>
                                        <span>{{ $d->platform }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="fi-flex fi-flex-wrap fi-gap-2">
                        @foreach($tags as $t)
                            <span class="fi-text-xs fi-bg-gray-100 fi-text-gray-700 fi-px-2 fi-py-1 fi-rounded-full">{{ $t }}</span>
                        @endforeach
                    </div>

                    <div class="fi-flex fi-items-center fi-justify-between fi-gap-4">
                        <div class="fi-flex fi-items-center fi-gap-3">
                            <span class="fi-text-xs @if($d->visibility === 'public') fi-bg-blue-100 fi-text-blue-700 @else fi-bg-gray-100 fi-text-gray-700 @endif fi-px-2 fi-py-1 fi-rounded">{{ ucfirst($d->visibility ?? 'public') }}</span>
                            <div class="fi-text-sm fi-text-muted">{{ $d->organization?->name ?? '-' }}</div>
                        </div>

                        <div class="fi-text-sm fi-text-muted">
                            {{ $author?->name ?? '' }}
                        </div>
                    </div>

                    <div>
                        <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('view', ['record' => $d->id]) }}" class="fi-inline-block fi-w-full fi-text-center fi-bg-gradient-to-r fi-from-blue-500 fi-to-blue-400 fi-text-white fi-py-3 fi-rounded-md hover:fi-opacity-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="fi-inline fi-mr-2 fi-h-4 fi-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 10l4 4-4 4"/><path d="M21 14H9"/><path d="M3 6h12a2 2 0 012 2v8a2 2 0 01-2 2H3V6z"/></svg>
                            Visualizar Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
