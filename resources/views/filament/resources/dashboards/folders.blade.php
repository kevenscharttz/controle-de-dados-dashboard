@php
    /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\DashboardFolder> $folders */
    $folders = $folders ?? collect();
    $current = $currentFolderId ?? null;
@endphp

@if($folders->isNotEmpty())
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold">Pastas</h3>
            <div class="text-xs text-gray-500">Selecione uma pasta para filtrar os dashboards.</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($folders as $folder)
                @php $active = (string)$current === (string)$folder->id; @endphp
                <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-100">
                    <div class="p-4 flex items-start gap-3">
                        <x-filament::icon
                            icon="heroicon-o-folder"
                            class="w-6 h-6 {{ $active ? 'text-primary-600' : 'text-gray-500' }}"
                        />
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('index', ['folder_id' => $folder->id]) }}"
                                   class="text-sm font-semibold {{ $active ? 'text-primary-700' : 'text-gray-800' }}">
                                    {{ $folder->name }}
                                </a>
                                <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('create', ['folder_id' => $folder->id, 'organization_id' => $folder->organization_id]) }}"
                                   class="text-xs bg-primary-600 text-white rounded-md px-2 py-1 hover:bg-primary-700">
                                    Criar dashboard
                                </a>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ $folder->dashboards()->count() }} dashboards</div>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 px-4 py-2 text-xs text-gray-500">
                        Organização: <span class="font-medium text-gray-700">{{ $folder->organization->name ?? '-' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
