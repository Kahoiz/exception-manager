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
        Schema::create('exception_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('code');
            $table->text('message');
            $table->string('file');
            $table->integer('line');
            $table->text('trace');
            $table->string('uuid')->nullable();
            $table->string('application');
            $table->integer('user_id')->nullable();
            $table->unsignedBigInteger('previous_log_id')->nullable();
            $table->foreign('previous_log_id')->references('id')->on('exception_logs')->cascadeOnDelete();
            $table->timestamp('thrown_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exception_logs');
    }
};
