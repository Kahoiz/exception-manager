<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('spike_rules', function (Blueprint $table) {
            $table->id();
            $table->string('application')->unique();
            $table->float('alpha');
            $table->integer('threshold');
            $table->float('last_ema')->nullable();
            $table->timestamps();
        });

        Schema::create('ema_history', function (Blueprint $table) {
            $table->id();
            $table->float('EMA');
            $table->integer('count');
            $table->string('application');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spike_rules');
        Schema::dropIfExists('ema_history');
    }
};
