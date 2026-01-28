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
       Schema::create('cv_reviews', function (Blueprint $table) {
    $table->id();
    $table->string('file_name');
    $table->string('file_path');
    $table->json('issues');
    $table->text('candidate_email');
    $table->text('internal_email');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_reviews');
    }
};
