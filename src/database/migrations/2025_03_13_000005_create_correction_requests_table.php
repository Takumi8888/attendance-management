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
        Schema::create('correction_requests', function (Blueprint $table) {
			$table->id();
			$table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete()->comment('出勤ID');
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('スタッフID');
			$table->foreignId('admin_id')->nullable()->constrained('admins')->cascadeOnDelete()->comment('管理者ID');
			$table->date('application_date')->comment('申請日');
			$table->tinyInteger('status')->unsigned()->comment('申請状態');
			$table->text('note')->comment('備考欄');
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
