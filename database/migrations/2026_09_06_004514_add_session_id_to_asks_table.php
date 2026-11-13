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
        Schema::table('asks', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('source');
            $table->index(['session_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asks', function (Blueprint $table) {
            $table->dropIndex(['session_id', 'id']);
            $table->dropColumn('session_id');
        });
    }
};
