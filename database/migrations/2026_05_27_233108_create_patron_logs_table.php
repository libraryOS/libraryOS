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
        Schema::create('patron_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->comment('organization this patron log belongs to');
            $table->unsignedBigInteger('patron_id')->comment('patron this log entry is about');
            $table->string('actor_type')->nullable()->comment('model class of the actor who performed this action');
            $table->unsignedBigInteger('actor_id')->nullable()->comment('model identifier of the actor who performed this action');
            $table->string('action')->comment('action performed for this patron log event');
            $table->text('description')->nullable()->comment('optional human-readable description for this log event');
            $table->json('metadata')->nullable()->comment('optional structured metadata for this log event');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_type', 'actor_id']);
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('patron_id')->references('id')->on('patrons')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patron_logs');
    }
};
