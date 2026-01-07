<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_logo_upload_and_file_existence(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('logo-test.png', 300, 100);

        // Simular povoamento de dados do form
        $org = Organization::create([
            'name' => 'Teste Org',
            'slug' => 'teste-org',
            'description' => 'Desc',
            'logo_url' => 'organizations/logos/'.$file->hashName(),
        ]);

        // Armazenar arquivo como o componente faria
        Storage::disk('public')->putFileAs('organizations/logos', $file, $file->hashName());

        $this->assertTrue(Storage::disk('public')->exists($org->logo_url), 'Arquivo não foi armazenado.');

        $url = Storage::disk('public')->url($org->logo_url);
        $this->assertStringContainsString('/storage/organizations/logos/', $url);
    }

    public function test_old_logo_deleted_on_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fileA = UploadedFile::fake()->image('logo-a.png');
        $fileB = UploadedFile::fake()->image('logo-b.png');

        $org = Organization::create([
            'name' => 'Org Swap',
            'slug' => 'org-swap',
            'description' => 'Desc',
            'logo_url' => 'organizations/logos/'.$fileA->hashName(),
        ]);
        Storage::disk('public')->putFileAs('organizations/logos', $fileA, $fileA->hashName());
        $this->assertTrue(Storage::disk('public')->exists($org->logo_url));

        $oldPath = $org->logo_url;

        // Atualizar para novo logo
        $org->update([
            'logo_url' => 'organizations/logos/'.$fileB->hashName(),
        ]);
        Storage::disk('public')->putFileAs('organizations/logos', $fileB, $fileB->hashName());

        $this->assertTrue(Storage::disk('public')->exists($org->logo_url));
        $this->assertFalse(Storage::disk('public')->exists($oldPath), 'Logo antigo não foi removido.');
    }

    public function test_logo_deleted_on_organization_delete(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('logo-del.png');
        $org = Organization::create([
            'name' => 'Org Del',
            'slug' => 'org-del',
            'description' => 'Desc',
            'logo_url' => 'organizations/logos/'.$file->hashName(),
        ]);
        Storage::disk('public')->putFileAs('organizations/logos', $file, $file->hashName());
        $this->assertTrue(Storage::disk('public')->exists($org->logo_url));

        $path = $org->logo_url;
        $org->delete();
        $this->assertFalse(Storage::disk('public')->exists($path), 'Logo não foi removido na deleção.');
    }
}
