<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add google_id if it doesn't exist
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email_verified_at');
            }

            // Drop the old 'roles' string column if you already migrated it previously
            if (Schema::hasColumn('users', 'roles')) {
                $table->dropColumn('roles');
            }

            // Add the strict enum 'role' column, defaulting existing users to 'voter'
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'staff', 'nominee', 'voter'])
                      ->default('voter')
                      ->after('password');
            }

            // Ensure password is nullable for Google OAuth users
            $table->string('password')->nullable()->change();
        });

        // Optional: Force a default password for any existing users who might have a blank password
        DB::table('users')->whereNull('password')->update([
            'password' => Hash::make('password123')
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropColumn('google_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            // Revert back to the old string roles column if rolled back
            if (!Schema::hasColumn('users', 'roles')) {
                $table->string('roles')->default('voter');
            }
            $table->string('password')->nullable(false)->change();
        });
    }
};
