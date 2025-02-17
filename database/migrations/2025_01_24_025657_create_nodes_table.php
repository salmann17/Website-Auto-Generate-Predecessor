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
        Schema::create('nodes', function (Blueprint $table) {
            $table->increments('inode');
            $table->string('activity', 45)->nullable();
            $table->integer('durasi')->nullable();
            $table->integer('prioritas')->nullable();
            $table->integer('total_price')->nullable();
            $table->string('bobot_rencana', 45)->nullable();
            $table->string('bobot_realisasi', 45)->nullable();

            $table->unsignedInteger('bab_idbab');
            $table->foreign('bab_idbab')
                ->references('idbab')
                ->on('babs')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
