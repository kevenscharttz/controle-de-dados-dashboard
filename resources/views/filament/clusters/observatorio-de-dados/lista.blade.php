<x-filament-panels::page>
    @php
        /** @var \Illuminate\Database\Eloquent\Collection $dashboards */
        $dashboards = $dashboards ?? collect();
    @endphp

    @include('filament.resources.dashboards.list-cards', ['dashboards' => $dashboards])
</x-filament-panels::page>
