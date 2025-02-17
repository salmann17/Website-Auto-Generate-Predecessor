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
        Schema::create('predecessors', function (Blueprint $table) {
            $table->unsignedInteger('node_core');
            $table->unsignedInteger('node_cabang');

            $table->foreign('node_core')
                ->references('idnode')
                ->on('nodes')
                ->onDelete('cascade');

            $table->foreign('node_cabang')
                ->references('idnode')
                ->on('nodes')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predecessors');
    }
};
