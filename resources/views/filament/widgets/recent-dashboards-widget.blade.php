<div class="fi-widget-fi-card dashboard-card">
    <div class="fi-widget-fi-card-body">
        <h3 class="dashboard-card-title">Recentes</h3>
        <ul class="fi-mt-2 fi-space-y-3">
            @foreach($dashboards as $d)
                <li class="fi-flex fi-justify-between fi-items-start">
                    <div>
                        <div class="dashboard-card-section">
                            <div class="fi-font-medium">{{ $d->title }}</div>
                            <div class="dashboard-card-desc">{{ $d->organization?->name }}</div>
                        </div>
                    </div>
                    <a href="{{ \App\Filament\Resources\Dashboards\DashboardResource::getUrl('view', ['record' => $d->id]) }}" class="fi-text-primary">Ver</a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
