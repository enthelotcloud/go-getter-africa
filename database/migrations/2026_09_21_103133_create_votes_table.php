<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_phone')->nullable();

            $table->foreignId('nomination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nomination_category_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('tokens_spent');
            $table->decimal('commission_earned_kes', 10, 2)->default(0.00);
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
