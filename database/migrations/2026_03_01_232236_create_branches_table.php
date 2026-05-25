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
        Schema::create('branches', function (Blueprint $table): void {
            $table->id()->comment('primary key');
            $table->unsignedBigInteger('organization_id')->comment('organization this branch belongs to');
            $table->unsignedBigInteger('country_id')->nullable()->comment('country this branch is located in');
            $table->string('name', 100)->comment('branch name');
            $table->string('slug')->nullable()->index()->comment('branch slug');
            $table->string('code', 100)->nullable()->index()->comment('branch code');
            $table->string('description', 255)->nullable()->index()->comment('branch description');
            $table->string('address_line_1', 100)->comment('first line of the branch address');
            $table->string('address_line_2', 100)->nullable()->comment('second line of the branch address');
            $table->string('city', 100)->comment('city the branch is located in');
            $table->string('state_province', 100)->nullable()->comment('state or province the branch is located in');
            $table->string('postal_code', 20)->nullable()->comment('postal code of the branch');
            $table->string('timezone', 50)->nullable()->comment('timezone of the branch');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
