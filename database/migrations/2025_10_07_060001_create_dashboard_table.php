<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('url');
            $table->enum('visibility', ['public', 'private']);
            $table->unsignedBigInteger('scope_user_id')->nullable();
            $table->unsignedBigInteger('scope_profile_id')->nullable();
            $table->unsignedBigInteger('scope_organization_id')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'scope_profile_id', 'scope_user_id'], 'unique_dashboard_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};