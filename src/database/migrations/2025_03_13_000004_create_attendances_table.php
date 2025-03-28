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
        Schema::create('attendances', function (Blueprint $table) {
			$table->id();
			$table->foreignId('work_time_id')->constrained('work_times')->cascadeOnDelete()->comment('労働時間ID');
			$table->date('work_day')->comment('日付');
			$table->time('total_break_time')->nullable()->comment('総休憩時間');
			$table->time('actual_working_hours')->nullable()->comment('実労働時間');
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
