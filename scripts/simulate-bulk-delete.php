<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput(),
    new Symfony\Component\Console\Output\BufferedOutput()
);
$app->boot();

use App\Models\User;
use Illuminate\Support\Facades\Gate;

$manager = User::whereHas('roles', fn($q)=> $q->where('name','organization-manager'))->first();
if(!$manager) { echo "NO_MANAGER\n"; exit; }

$managerOrgIds = $manager->organizations()->pluck('organizations.id')->toArray();

// Build a collection with one user from manager's org and one from other org
$inOrg = User::whereHas('organizations', fn($q)=> $q->whereIn('organizations.id', $managerOrgIds))->first();
$outOrg = User::whereDoesntHave('organizations', fn($q)=> $q->whereIn('organizations.id', $managerOrgIds))->first();
if(!$outOrg) { echo "NO_OUT_ORG\n"; exit; }

$records = collect([$inOrg, $outOrg]);
echo "manager: {$manager->id}\n";
foreach($records as $r) {
    echo "record: {$r->id} allows_delete? ".(Gate::forUser($manager)->allows('delete', $r) ? 'YES' : 'NO')."\n";
}

// Simulate action closure behavior
foreach($records as $record) {
    if(!Gate::forUser($manager)->allows('delete', $record)) {
        echo "ACTION: would throw AuthorizationException for record {$record->id}\n";
        exit(0);
    }
}

echo "ACTION: would proceed to delete all records\n";

$kernel->terminate($input, $status);
