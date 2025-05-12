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
        Schema::create('break_times', function (Blueprint $table) {
			$table->id();
			$table->foreignId('work_time_id')->constrained('work_times')->cascadeOnDelete()->comment('労働時間ID');
			$table->dateTime('start_time')->comment('休憩開始時間');
			$table->dateTime('end_time')->nullable()->comment('休憩終了時間');
			$table->time('break_time')->nullable()->comment('休憩時間');
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
};
