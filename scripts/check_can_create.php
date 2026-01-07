<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = $argv[1] ?? 1;
$user = \App\Models\User::find($id);
if (! $user) {
    echo "no user with id={$id}\n";
    exit(0);
}
$roles = $user->roles()->pluck('name')->toArray();
echo "User id={$id} roles: " . implode(', ', $roles) . "\n";
$can = $user->can('create', \App\Models\Report::class);
echo "can create report? ";
var_export($can);
echo "\n";

// Also list Gate::before effect
// Try super-admin check
$hasSuper = method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('super_admin'));
echo "has super-admin role? " . ($hasSuper ? 'yes' : 'no') . "\n";

// Direct Gate check
use Illuminate\Support\Facades\Gate;
$gateAllows = Gate::forUser($user)->allows('create', \App\Models\Report::class);
echo "Gate::forUser allows create? "; var_export($gateAllows); echo "\n";
