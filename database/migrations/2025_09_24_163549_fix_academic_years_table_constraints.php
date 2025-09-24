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
        Schema::table('academic_years', function (Blueprint $table) {
            // Drop existing unique constraints that are wrong
            $table->dropUnique(['year_start']);
            $table->dropUnique(['year_end']);
            $table->dropUnique(['semester']);
            
            // Add composite unique constraint for year_start, year_end, and semester
            // This allows same year range but different semesters
            $table->unique(['year_start', 'year_end', 'semester'], 'academic_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('academic_year_unique');
            
            // Restore original unique constraints (if needed for rollback)
            $table->unique('year_start');
            $table->unique('year_end');
            $table->unique('semester');
        });
    }
};
