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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            // Link to the Project
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            // Link to the current Column
            $table->foreignId('column_id')->constrained('board_columns')->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order');
            $table->dateTime('due_date')->nullable();
            $table->unsignedTinyInteger('priority')->default(1); // e.g., 1=Low, 3=High

            // Unique constraint: Ensures task order is unique within its column
            $table->unique(['column_id', 'order']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
