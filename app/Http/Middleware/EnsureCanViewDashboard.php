<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanViewDashboard
{
    /**
     * If the incoming request targets the Filament dashboard view route,
     * ensure the authenticated user is authorized to view that dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pattern used by Filament resource view routes: admin/resources/{resource}/view/{record}
        $path = $request->path();

        // Quick check: proceed when not targeting dashboards view route
        if (! str_starts_with($path, 'admin/resources/dashboards/view')) {
            return $next($request);
        }

        // Extract record id from the path (last segment)
        $segments = $request->segments();
        $recordId = end($segments);

        if (! $recordId) {
            abort(404);
        }

        // Resolve model and authorize via Gate / policy
        $dashboard = app()->make('\App\Models\Dashboard')::find($recordId);

        if (! $dashboard) {
            abort(404);
        }

        if (! Gate::allows('view', $dashboard)) {
            abort(403);
        }

        return $next($request);
    }
}
