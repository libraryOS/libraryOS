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
        Schema::create('roles', function (Blueprint $table): void {
            $table->id()->comment('unique identifier of the role.');
            $table->unsignedBigInteger('organization_id')->comment('the organization this role belongs to.');
            $table->string('key')->comment('stable machine-readable role key, such as owner, administrator, librarian, or circulation_staff.');
            $table->string('name')->nullable()->comment('human-readable role name shown in the interface.');
            $table->string('name_translation_key')->nullable()->comment('translation key for the role name.');
            $table->text('description')->nullable()->comment('optional explanation of the purpose of this role.');
            $table->boolean('is_system')->default(true)->comment('indicates whether this role is managed by LibraryOS and should not be deleted or modified by organizations.');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
