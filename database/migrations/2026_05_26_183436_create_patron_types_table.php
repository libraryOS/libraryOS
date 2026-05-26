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
        Schema::create('patron_types', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('organization_id')->comment('organization this patron type belongs to');
            $table->string('key', 100)->comment('unique machine-readable identifier for the patron type within the organization');
            $table->string('name', 100)->comment('human-readable name of the patron type');
            $table->string('description', 255)->nullable()->comment('optional description of the patron type');
            $table->boolean('is_active')->default(true)->comment('whether this patron type is currently active');
            $table->unsignedSmallInteger('membership_duration_days')->nullable()->comment('how long a membership of this type lasts, in days');
            $table->unsignedSmallInteger('max_loans')->nullable()->comment('maximum number of simultaneous loans allowed for this patron type');
            $table->boolean('keep_loan_history')->default(false)->comment('whether loan history is retained for patrons of this type');
            $table->boolean('can_receive_notifications')->default(true)->comment('whether patrons of this type can receive notifications');
            $table->unsignedTinyInteger('minimum_age')->nullable()->comment('minimum age in years required to hold this patron type');
            $table->unsignedTinyInteger('maximum_age')->nullable()->comment('maximum age in years allowed for this patron type');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patron_types');
    }
};
