<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->string('notifiable_type');
    $table->unsignedBigInteger('notifiable_id');
    $table->text('data');
    $table->timestamps();

    // به جای
    // $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_type_notifiable_id_index');
    // از این استفاده کن:
    $table->index(['notifiable_type', 'notifiable_id']); 
});

    }
    public function down(): void {
        Schema::dropIfExists('notifications');
    }
};
