<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('followable_type');
            $table->unsignedBigInteger('followable_id');
            $table->timestamps();

            $table->index('user_id');
            $table->index(['followable_type', 'followable_id']);
            $table->unique(['user_id', 'followable_type', 'followable_id'], 'follows_unique_user_record');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
