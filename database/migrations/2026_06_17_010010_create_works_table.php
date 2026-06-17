<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->comment('organization this work belongs to');
            $table->string('title')->comment('primary title of the work');
            $table->string('subtitle')->nullable()->comment('secondary title of the work');
            $table->text('description')->nullable()->comment('description or summary of the work');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
