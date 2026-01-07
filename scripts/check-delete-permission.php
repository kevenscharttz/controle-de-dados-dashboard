<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput(),
    new Symfony\Component\Console\Output\BufferedOutput()
);

// Boot the app for Eloquent
$app->boot();

use App\Models\User;
use Illuminate\Support\Facades\Gate;

$manager = User::whereHas('roles', function($q){ $q->where('name','organization-manager'); })->first();

if (!$manager) {
    echo "NO_MANAGER_FOUND\n";
    exit;
}

$managerOrgIds = $manager->organizations()->pluck('organizations.id')->toArray();

$target = User::where('id','!=',$manager->id)
    ->whereDoesntHave('organizations', function($q) use ($managerOrgIds) { if(empty($managerOrgIds)) { $q->whereRaw('1=1'); } else { $q->whereNotIn('organizations.id', $managerOrgIds); } })
    ->first();

if (!$target) {
    echo "NO_TARGET_FOUND\n";
    exit;
}

echo "manager: {$manager->id} target: {$target->id}\n";
echo Gate::forUser($manager)->allows('delete', $target) ? "ALLOWS\n" : "DENIES\n";

$kernel->terminate($input, $status);
