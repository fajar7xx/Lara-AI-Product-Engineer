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
        Schema::create('generation_outputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transcript_session_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->string('status', 32)->default('pending');
            $table->longText('content')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('revision_number')->default(1);
            $table->json('clarification_snapshot')->nullable();
            $table->timestamps();

            $table->index(['transcript_session_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_outputs');
    }
};
