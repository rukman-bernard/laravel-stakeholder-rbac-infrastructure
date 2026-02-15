<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->unique(); // e.g., BSC_CS, MSC_CHEM
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->integer('starting_level_id')->unsigned();
            $table->integer('offered_level_id')->unsigned();
            $table->timestamps();
        });


         // Add check constraint manually(Haven't tested yet)
        DB::statement("
            ALTER TABLE programmes
            ADD CONSTRAINT chk_valid_levels
            CHECK (
                starting_level_id BETWEEN 3 AND 8 AND
                offered_level_id BETWEEN 3 AND 8 AND
                starting_level_id <= offered_level_id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
