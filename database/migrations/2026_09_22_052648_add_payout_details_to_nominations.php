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
        Schema::table('nominations', function (Blueprint $table) {
            $table->decimal('kes_balance', 12, 2)->default(0.00)->after('total_votes');
            $table->string('last_payout_phone')->nullable()->after('kes_balance');
            $table->string('access_pin', 10)->nullable()->after('last_payout_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nominations', function (Blueprint $table) {
            //
        });
    }
};
