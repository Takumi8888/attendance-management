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
        Schema::create('admins', function (Blueprint $table) {
			$table->id();
			$table->string('name', 50)->comment('管理者名');
			$table->string('email', 100)->unique()->comment('メールアドレス');
			$table->timestamp('email_verified_at')->nullable()->comment('メール認証実施日');
			$table->string('password')->comment('パスワード');
			$table->rememberToken();
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
