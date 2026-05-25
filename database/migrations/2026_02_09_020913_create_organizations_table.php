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
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->string('name')->comment('organization name');
            $table->string('slug')->unique()->nullable()->index()->comment('URL-friendly organization identifier');
            $table->string('invitation_code', 64)->unique()->nullable()->comment('code used to invite members to the organization');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
