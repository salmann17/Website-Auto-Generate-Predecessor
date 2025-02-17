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
            $table->increments('idnode');
            $table->string('activity', 255)->nullable();
            $table->integer('durasi')->nullable();
            $table->integer('prioritas')->nullable();
            $table->integer('total_price')->nullable();
            $table->string('bobot_rencana', 45)->nullable();
            $table->string('bobot_realisasi', 45)->nullable();

            $table->unsignedInteger('id_sub_activity');
            $table->foreign('id_sub_activity')
                ->references('idsub_activity')
                ->on('sub_activity')
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
