<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrons', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->comment('organization this patron belongs to');
            $table->unsignedBigInteger('user_id')->nullable()->comment('linked user account for this patron, if any');
            $table->unsignedBigInteger('patron_type_id')->comment('patron type assigned to this patron');
            $table->unsignedBigInteger('home_branch_id')->nullable()->comment('default home branch for this patron');
            $table->string('card_number')->comment('library card or membership number used to identify the patron');
            $table->string('first_name')->comment('first name of the patron');
            $table->string('last_name')->comment('last name of the patron');
            $table->string('email')->nullable()->comment('email address of the patron');
            $table->string('phone')->nullable()->comment('phone number of the patron');
            $table->string('status')->comment('current account status stored as a string enum value');
            $table->timestamp('membership_expires_at')->nullable()->comment('date and time when the membership expires');
            $table->text('notes')->nullable()->comment('internal notes about the patron account');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('patron_type_id')->references('id')->on('patron_types')->cascadeOnDelete();
            $table->foreign('home_branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrons');
    }
};
