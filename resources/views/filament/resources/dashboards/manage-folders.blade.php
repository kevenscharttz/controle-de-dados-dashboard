<x-filament-panels::page>
    @php /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\DashboardFolder> $folders */ @endphp

    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold">Pastas</h3>
            <div class="text-xs text-gray-500">Gerencie suas pastas. Abra uma para ver e criar dashboards.</div>
        </div>

        @if($folders->isEmpty())
            <div class="rounded-lg border border-gray-200 p-6 text-gray-600">
                Nenhuma pasta encontrada. Use o botão "Criar Pasta" acima.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($folders as $folder)
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-100">
                        <div class="p-4 flex items-start gap-3">
                            <x-filament::icon icon="heroicon-o-folder" class="w-6 h-6 text-primary-600" />
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('folder', ['folder' => $folder->id]) }}" class="text-sm font-semibold text-gray-800">
                                        {{ $folder->name }}
                                    </a>
                                    <div class="flex items-center gap-2">
                                        <button x-on:click="const name = prompt('Novo nome da pasta', '{{ $folder->name }}'); if (name) { $wire.renameFolder({{ $folder->id }}, name) }" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Renomear</button>
                                        <button x-on:click="if (confirm('Excluir esta pasta? Os dashboards permanecerão associados, mas sem pasta.')) { $wire.deleteFolder({{ $folder->id }}) }" class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700">Excluir</button>
                                    </div>
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
        @endif
    </div>
</x-filament-panels::page>
