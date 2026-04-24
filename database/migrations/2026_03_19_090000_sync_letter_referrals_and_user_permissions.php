<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('letter_referrals', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
                $table->index('uuid');
            }
            if (! Schema::hasColumn('letter_referrals', 'from_user_id')) {
                $table->unsignedBigInteger('from_user_id')->nullable()->after('letter_id');
                $table->index('from_user_id');
            }
            if (! Schema::hasColumn('letter_referrals', 'assigned_by_user_id')) {
                $table->unsignedBigInteger('assigned_by_user_id')->nullable()->after('to_user_id');
                $table->index('assigned_by_user_id');
            }
            if (! Schema::hasColumn('letter_referrals', 'status')) {
                $table->string('status', 20)->default('pending')->after('assigned_by_user_id');
            }
            if (! Schema::hasColumn('letter_referrals', 'response_note')) {
                $table->text('response_note')->nullable()->after('note');
            }
            if (! Schema::hasColumn('letter_referrals', 'referred_at')) {
                $table->dateTime('referred_at')->nullable()->after('response_note');
            }
            if (! Schema::hasColumn('letter_referrals', 'responded_at')) {
                $table->dateTime('responded_at')->nullable()->after('referred_at');
            }
        });

        $referrals = DB::table('letter_referrals')
            ->leftJoin('letters', 'letters.id', '=', 'letter_referrals.letter_id')
            ->select([
                'letter_referrals.id',
                'letter_referrals.created_at',
                'letters.user_id',
                'letters.from_user_id',
            ])
            ->get();

        foreach ($referrals as $referral) {
            $updates = [];

            if (Schema::hasColumn('letter_referrals', 'uuid')) {
                $currentUuid = DB::table('letter_referrals')->where('id', $referral->id)->value('uuid');
                if (! $currentUuid) {
                    $updates['uuid'] = (string) Str::uuid();
                }
            }

            if (Schema::hasColumn('letter_referrals', 'from_user_id')) {
                $fromUserId = DB::table('letter_referrals')->where('id', $referral->id)->value('from_user_id');
                if (! $fromUserId) {
                    $updates['from_user_id'] = $referral->user_id ?: $referral->from_user_id;
                }
            }

            if (Schema::hasColumn('letter_referrals', 'assigned_by_user_id')) {
                $assignedBy = DB::table('letter_referrals')->where('id', $referral->id)->value('assigned_by_user_id');
                if (! $assignedBy) {
                    $updates['assigned_by_user_id'] = $referral->user_id ?: $referral->from_user_id;
                }
            }

            if (Schema::hasColumn('letter_referrals', 'status')) {
                $status = DB::table('letter_referrals')->where('id', $referral->id)->value('status');
                if (! $status) {
                    $updates['status'] = 'pending';
                }
            }

            if (Schema::hasColumn('letter_referrals', 'referred_at')) {
                $referredAt = DB::table('letter_referrals')->where('id', $referral->id)->value('referred_at');
                if (! $referredAt) {
                    $updates['referred_at'] = $referral->created_at;
                }
            }

            if ($updates !== []) {
                DB::table('letter_referrals')->where('id', $referral->id)->update($updates);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('department_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });

        Schema::table('letter_referrals', function (Blueprint $table) {
            if (Schema::hasColumn('letter_referrals', 'responded_at')) {
                $table->dropColumn('responded_at');
            }
            if (Schema::hasColumn('letter_referrals', 'referred_at')) {
                $table->dropColumn('referred_at');
            }
            if (Schema::hasColumn('letter_referrals', 'response_note')) {
                $table->dropColumn('response_note');
            }
            if (Schema::hasColumn('letter_referrals', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('letter_referrals', 'assigned_by_user_id')) {
                $table->dropIndex(['assigned_by_user_id']);
                $table->dropColumn('assigned_by_user_id');
            }
            if (Schema::hasColumn('letter_referrals', 'from_user_id')) {
                $table->dropIndex(['from_user_id']);
                $table->dropColumn('from_user_id');
            }
            if (Schema::hasColumn('letter_referrals', 'uuid')) {
                $table->dropIndex(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};
