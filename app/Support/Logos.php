<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use App\Models\Organization;

class Logos
{
    /**
     * Retorna a URL pública do logo ou fallback.
     */
    public static function url(?string $path, ?Organization $organization = null): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            $url = Storage::disk('public')->url($path);
            // Cache busting simples (updated_at em timestamp se disponível)
            $version = $organization?->updated_at?->timestamp ?? time();
            return $url.'?v='.$version;
        }
        return asset('images/default-organization-logo.svg');
    }

    /**
     * Remove o arquivo antigo com segurança.
     */
    public static function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                \Log::warning('Falha ao deletar logo antigo: '.$e->getMessage());
            }
        }
    }
}
