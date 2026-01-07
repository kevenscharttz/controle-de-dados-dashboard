<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('report'); // dashboard ou report
            $table->string('platform');
            $table->string('platform_custom')->nullable();
            $table->text('url');
            $table->string('visibility')->default('public'); // public ou private
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('scope_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('scope_profile_id')->nullable();
            $table->foreignId('scope_organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
