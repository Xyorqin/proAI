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
        Schema::create('subsection_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsection_id')
                ->nullable()
                ->constrained('subsections')
                ->onDelete('cascade');
            $table->string('path')->nullable();
            $table->text('content')->nullable();
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsection_files');
    }
};
