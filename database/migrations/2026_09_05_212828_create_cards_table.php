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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->string('source_path')->nullable();
            $table->string('what_this_is');
            $table->text('does');
            $table->text('does_not');
            $table->text('steps_on');
            $table->string('speaker')->default('unknown');
            $table->boolean('private')->default(true);
            $table->text('voice_line')->nullable();
            $table->text('body');
            $table->text('search_text');
            $table->timestamps();

            $table->index('source_type');
            $table->index('what_this_is');
            $table->index('source_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
