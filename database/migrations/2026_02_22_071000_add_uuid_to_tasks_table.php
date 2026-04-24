<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
                $table->unique('uuid');
            }
        });

        $rows = DB::table('tasks')->select('id')->whereNull('uuid')->get();
        foreach ($rows as $row) {
            DB::table('tasks')->where('id', $row->id)->update([
                'uuid' => (string) Str::uuid(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'uuid')) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};
