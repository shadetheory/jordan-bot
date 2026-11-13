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
        Schema::create('asks', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('status');
            $table->text('question');
            $table->text('reply')->nullable();
            $table->timestamps();

            $table->index(['status', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asks');
    }
};
