<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('organization_id')->comment('organization this location belongs to');
            $table->unsignedBigInteger('branch_id')->comment('branch this location belongs to');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('parent location for nested shelving structures');
            $table->string('name', 100)->comment('human-readable name of the location');
            $table->string('code', 50)->nullable()->comment('short code or call number prefix identifying this location');
            $table->string('description', 255)->nullable()->comment('optional description of the location');
            $table->boolean('is_active')->default(true)->comment('whether this location is currently active');
            $table->boolean('is_public')->default(true)->comment('whether this location is visible to patrons in the catalog');
            $table->boolean('supports_pickups')->default(false)->comment('whether holds can be picked up at this location');
            $table->boolean('supports_returns')->default(false)->comment('whether items can be returned at this location');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
