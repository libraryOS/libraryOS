<?php

declare(strict_types=1);

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
        Schema::create('members', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('organization_id')->comment('organization the member belongs to');
            $table->unsignedBigInteger('user_id')->nullable()->comment('user account of the member');
            $table->unsignedBigInteger('role_id')->nullable()->comment('role assigned to the member');
            $table->timestamp('joined_at')->comment('timestamp when the member joined the organization');
            $table->string('timezone', 50)->nullable()->comment('member\'s preferred timezone');
            $table->date('birthdate')->nullable()->comment('member\'s date of birth');
            $table->timestamps();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();

            $table->unique(['organization_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
