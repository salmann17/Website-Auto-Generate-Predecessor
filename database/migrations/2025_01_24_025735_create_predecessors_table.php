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
            $table->foreignId('node_core')->constrained('nodes')->onDelete('cascade');
            $table->foreignId('node_cabang')->constrained('nodes')->onDelete('cascade');
            $table->primary(['node_core', 'node_cabang']);
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
