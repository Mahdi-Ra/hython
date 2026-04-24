<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('letter_id');
            $table->unsignedBigInteger('requested_by_user_id');
            $table->unsignedBigInteger('approver_id');
            $table->string('status', 30)->default('pending');
            $table->text('request_note')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index('letter_id');
            $table->index('requested_by_user_id');
            $table->index('approver_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_approvals');
    }
};
