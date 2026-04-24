<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (! Schema::hasColumn('letters', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
                $table->unique('uuid');
            }
            if (! Schema::hasColumn('letters', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('subject');
                $table->index('department_id');
            }
            if (! Schema::hasColumn('letters', 'priority')) {
                $table->string('priority', 20)->default('normal')->after('content');
            }
            if (! Schema::hasColumn('letters', 'status')) {
                $table->string('status', 20)->default('pending')->after('priority');
            }
            if (! Schema::hasColumn('letters', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('from_user_id');
                $table->index('user_id');
            }
            if (! Schema::hasColumn('letters', 'body')) {
                $table->text('body')->nullable()->after('content');
            }
            if (! Schema::hasColumn('letters', 'type')) {
                $table->string('type', 20)->nullable()->after('department_id');
            }
            if (! Schema::hasColumn('letters', 'due_date')) {
                $table->date('due_date')->nullable()->after('status');
            }
            if (! Schema::hasColumn('letters', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('due_date');
            }
        });

        if (Schema::hasColumn('letters', 'uuid')) {
            $letters = DB::table('letters')->select('id')->whereNull('uuid')->get();
            foreach ($letters as $letter) {
                DB::table('letters')
                    ->where('id', $letter->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        }

        if (Schema::hasColumn('letters', 'user_id') && Schema::hasColumn('letters', 'from_user_id')) {
            DB::table('letters')
                ->whereNull('user_id')
                ->whereNotNull('from_user_id')
                ->update(['user_id' => DB::raw('from_user_id')]);
        }

        if (Schema::hasColumn('letters', 'body') && Schema::hasColumn('letters', 'content')) {
            DB::table('letters')
                ->whereNull('body')
                ->whereNotNull('content')
                ->update(['body' => DB::raw('content')]);
        }
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('letters', 'due_date')) {
                $table->dropColumn('due_date');
            }
            if (Schema::hasColumn('letters', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('letters', 'body')) {
                $table->dropColumn('body');
            }
            if (Schema::hasColumn('letters', 'user_id')) {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('letters', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('letters', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('letters', 'department_id')) {
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('letters', 'uuid')) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};
