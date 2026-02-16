<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->string('commentable_type');
    $table->unsignedBigInteger('commentable_id');
    $table->text('body');
    $table->timestamps();

    // این خط برای SQLite مطمئن‌تر است:
    $table->index(['commentable_type', 'commentable_id'], 'comments_commentable_type_commentable_id_index');
});

    }
    public function down(): void {
        Schema::dropIfExists('comments');
    }
};
