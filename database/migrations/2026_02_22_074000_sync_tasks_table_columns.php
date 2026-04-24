<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'assigned_to_user_id')) {
                $table->unsignedBigInteger('assigned_to_user_id')->nullable()->after('assigned_to');
                $table->index('assigned_to_user_id');
            }
            if (! Schema::hasColumn('tasks', 'created_by_user_id')) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->after('assigned_to_user_id');
                $table->index('created_by_user_id');
            }
            if (! Schema::hasColumn('tasks', 'letter_id')) {
                $table->unsignedBigInteger('letter_id')->nullable()->after('created_by_user_id');
                $table->index('letter_id');
            }
            if (! Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority', 20)->default('normal')->after('description');
            }
            if (! Schema::hasColumn('tasks', 'due_date')) {
                $table->date('due_date')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('due_date');
            }
        });

        if (Schema::hasColumn('tasks', 'assigned_to') && Schema::hasColumn('tasks', 'assigned_to_user_id')) {
            DB::table('tasks')
                ->whereNull('assigned_to_user_id')
                ->whereNotNull('assigned_to')
                ->update(['assigned_to_user_id' => DB::raw('assigned_to')]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }
            if (Schema::hasColumn('tasks', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('tasks', 'letter_id')) {
                $table->dropIndex(['letter_id']);
                $table->dropColumn('letter_id');
            }
            if (Schema::hasColumn('tasks', 'created_by_user_id')) {
                $table->dropIndex(['created_by_user_id']);
                $table->dropColumn('created_by_user_id');
            }
            if (Schema::hasColumn('tasks', 'assigned_to_user_id')) {
                $table->dropIndex(['assigned_to_user_id']);
                $table->dropColumn('assigned_to_user_id');
            }
        });
    }
};
