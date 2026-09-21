<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomination_id')->constrained()->cascadeOnDelete();
            $table->string('original_image');
            $table->string('processed_image')->nullable();
            $table->string('final_poster_path')->nullable();
            $table->string('vote_url');
            $table->dateTime('voting_start_date')->nullable();
            $table->dateTime('voting_end_date')->nullable();
            $table->json('template_settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posters');
    }
};
