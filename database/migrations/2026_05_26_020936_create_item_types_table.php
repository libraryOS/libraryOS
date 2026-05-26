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
        Schema::create('item_types', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('organization_id')->comment('organization this item type belongs to');
            $table->string('key', 100)->comment('unique machine-readable identifier for the item type within the organization');
            $table->string('name', 100)->nullable()->comment('human-readable name of the item type');
            $table->string('name_translation_key', 100)->nullable()->comment('translation key for the item type name');
            $table->string('description', 255)->nullable()->comment('optional description of the item type');
            $table->boolean('is_loanable')->default(true)->comment('whether items of this type can be loaned out');
            $table->boolean('is_holdable')->default(true)->comment('whether items of this type can be placed on hold');
            $table->boolean('is_visible_in_catalog')->default(true)->comment('whether items of this type appear in the public catalog');
            $table->unsignedSmallInteger('default_loan_days')->nullable()->comment('default number of days for a loan of this item type');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_types');
    }
};
