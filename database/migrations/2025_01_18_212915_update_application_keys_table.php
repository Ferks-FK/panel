<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ApplicationApi;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // delete all api keys
        ApplicationApi::query()->delete();

        Schema::table('application_apis', function (Blueprint $table) {
            $table->dropPrimary();
            $table->id()->first();
            $table->renameColumn('memo', 'description');
            $table->renameColumn('last_used', 'last_used_at');
            $table->string('description')->nullable(false)->change();
            $table->json('allowed_ips')->nullable()->after('description');
            $table->json('abilities')->after('allowed_ips');
            $table->foreignId('user_id')->after('allowed_ips')->constrained('users')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_apis', function (Blueprint $table) {
            $table->dropPrimary();
            DB::statement('ALTER TABLE application_apis MODIFY id BIGINT UNSIGNED NOT NULL');
            $table->dropColumn('id');
            $table->primary('token');
            $table->renameColumn('description', 'memo');
            $table->renameColumn('last_used_at', 'last_used');
            $table->string('memo')->nullable()->change();
            $table->dropColumn(['allowed_ips', 'abilities']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
