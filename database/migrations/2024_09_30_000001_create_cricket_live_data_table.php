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
        Schema::create('cricket_live_data', function (Blueprint $table) {
            $table->id();
            $table->string('match_id')->nullable()->index();
            $table->string('ball');
            $table->string('batsman');
            $table->string('bowler');
            $table->string('runs');
            $table->text('commentary');
            $table->string('team1')->nullable();
            $table->string('team2')->nullable();
            $table->string('team1_score')->nullable();
            $table->string('team2_score')->nullable();
            $table->string('source')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('data_timestamp')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('match_id');
            $table->index('data_timestamp');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cricket_live_data');
    }
};
