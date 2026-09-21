<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomination_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Optional link if nominee logs in
            $table->string('name');
            $table->string('code')->unique(); // e.g. TV01 or NOM-102 for quick voting lookups
            $table->string('company_or_show')->nullable(); // e.g. Citizen TV / Morning Show
            $table->string('profile_image')->nullable();
            $table->text('bio')->nullable();

            // Social Profiles
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('website_url')->nullable();

            $table->unsignedBigInteger('total_votes')->default(0); // Cached total vote counter
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
