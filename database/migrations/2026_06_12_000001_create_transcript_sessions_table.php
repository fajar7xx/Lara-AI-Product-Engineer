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
        Schema::create('transcript_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('ulid', 26)->unique();
            $table->string('source_type', 32)->default('paste');
            $table->longText('transcript_text');
            $table->string('status', 32)->default('draft');
            $table->json('extracted_context')->nullable();
            $table->json('layout_recommendations')->nullable();
            $table->json('design_system_recommendations')->nullable();
            $table->string('project_name')->nullable();
            $table->text('project_summary')->nullable();
            $table->text('target_users')->nullable();
            $table->json('goals')->nullable();
            $table->json('key_features')->nullable();
            $table->string('template_family', 32)->nullable();
            $table->string('design_system', 32)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_sessions');
    }
};
