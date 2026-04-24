<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('priority', 20)->nullable();
            $table->string('action');
            $table->string('target_role', 20)->nullable();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('task_title_template')->nullable();
            $table->text('task_description_template')->nullable();
            $table->string('task_priority', 20)->nullable();
            $table->unsignedSmallInteger('due_in_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
