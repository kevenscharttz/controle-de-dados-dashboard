<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCommonUserToDashboards
{
    /**
     * If the authenticated user is a common user, redirect panel root
     * requests to the dashboards list page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'hasRole')) {
            return $next($request);
        }

        $has = fn(string $r) => $user->hasRole($r);
        $isCommon = $has('user') || $has('usuario') || $has(config('filament-shield.panel_user.name', 'panel_user'));
        $isElevated = $has('super-admin') || $has('super_admin') || $has('organization-manager');

        if ($isCommon && ! $isElevated) {
            $path = $request->path();
            $panelBase = 'painel';
            // Match panel root or default landing pages
            $targets = [
                $panelBase,
                $panelBase . '/',
                $panelBase . '/dashboard',
                $panelBase . '/home',
            ];
            if (in_array($path, $targets, true)) {
                $url = \App\Filament\Resources\Dashboards\DashboardResource::getUrl('index');
                return redirect($url);
            }
        }

        return $next($request);
    }
}
