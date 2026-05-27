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
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id()->comment('unique identifier of the permission.');
            $table->unsignedBigInteger('organization_id')->comment('the organization this permission belongs to.');
            $table->string('key')->comment('stable machine-readable permission key, such as loan.create or patron.view_history.');
            $table->string('name_translation_key')->comment('translation key for the human-readable permission name shown in the interface.');
            $table->text('description_translation_key')->nullable()->comment('optional explanation of what this permission allows.');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
