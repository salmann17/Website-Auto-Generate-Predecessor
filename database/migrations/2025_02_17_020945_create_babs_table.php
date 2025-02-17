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
        Schema::create('babs', function (Blueprint $table) {
            $table->increments('idbab');
            $table->string('nama', 45);
            $table->string('activity', 45)->nullable();

            $table->unsignedInteger('project_idproject');
            $table->foreign('project_idproject')
                  ->references('idproject')
                  ->on('projects')
                  ->onDelete('cascade');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('babs');
    }
};
