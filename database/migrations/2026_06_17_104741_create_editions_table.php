<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->comment('organization this edition belongs to');
            $table->unsignedBigInteger('work_id')->comment('work this edition represents');
            $table->unsignedBigInteger('item_type_id')->comment('item type for physical or digital items of this edition');
            $table->string('title')->comment('display title of the edition');
            $table->string('isbn')->nullable()->comment('international standard book number for the edition');
            $table->string('publisher')->nullable()->comment('publisher of the edition');
            $table->unsignedSmallInteger('publication_year')->nullable()->comment('year the edition was published');
            $table->string('language')->nullable()->comment('language of the edition');
            $table->string('cover_image_path')->nullable()->comment('stored cover image path for the edition');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('item_type_id')->references('id')->on('item_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
