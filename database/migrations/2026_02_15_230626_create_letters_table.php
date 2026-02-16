<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subject'); // اضافه کردن ستون subject
            $table->text('content')->nullable();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('letters');
    }
};
