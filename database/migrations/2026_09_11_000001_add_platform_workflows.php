<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPlatformWorkflows extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 32)->default('director')->after('password')->index();
            }
            if (!Schema::hasColumn('users', 'kindergarten_id')) {
                $table->unsignedBigInteger('kindergarten_id')->nullable()->after('role')->index();
            }
        });

        Schema::table('kindergarteners', function (Blueprint $table) {
            if (!Schema::hasColumn('kindergarteners', 'application_status')) {
                $table->string('application_status', 32)->default('registered')->index();
            }
            if (!Schema::hasColumn('kindergarteners', 'status_changed_at')) {
                $table->timestamp('status_changed_at')->nullable();
            }
            if (!Schema::hasColumn('kindergarteners', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable();
            }
        });

        Schema::table('kindergarten_group_age_range', function (Blueprint $table) {
            if (!Schema::hasColumn('kindergarten_group_age_range', 'space_reserved')) {
                $table->unsignedInteger('space_reserved')->default(0)->after('space_free');
            }
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kindergartener_id')->index();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamps();
            $table->foreign('kindergartener_id')->references('id')->on('kindergarteners')->cascadeOnDelete();
        });

        Schema::create('waiting_list_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kindergartener_id')->unique();
            $table->unsignedBigInteger('kindergarten_id')->index();
            $table->unsignedBigInteger('group_id')->index();
            $table->integer('priority_rank')->default(999);
            $table->timestamp('queued_at');
            $table->string('state', 24)->default('waiting')->index();
            $table->timestamps();
            $table->foreign('kindergartener_id')->references('id')->on('kindergarteners')->cascadeOnDelete();
            $table->index(['kindergarten_id', 'group_id', 'state', 'priority_rank', 'queued_at'], 'waiting_queue_order');
        });

        Schema::create('placement_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('waiting_list_entry_id')->index();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->string('response', 24)->nullable();
            $table->timestamps();
            $table->foreign('waiting_list_entry_id')->references('id')->on('waiting_list_entries')->cascadeOnDelete();
        });

        Schema::create('work_calendar_days', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('kindergarten_id')->nullable();
            $table->boolean('is_working_day')->default(false);
            $table->string('label')->nullable();
            $table->timestamps();
            $table->unique(['date', 'kindergarten_id']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kindergartener_id');
            $table->unsignedBigInteger('kindergarten_id')->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->date('attendance_date')->index();
            $table->string('status', 24);
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->unique(['kindergartener_id', 'attendance_date']);
            $table->foreign('kindergartener_id')->references('id')->on('kindergarteners')->cascadeOnDelete();
        });

        Schema::create('reinstatement_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kindergartener_id')->index();
            $table->string('token_hash', 64)->unique();
            $table->string('document_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamp('expires_at');
            $table->date('absence_from')->nullable();
            $table->date('absence_to')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 500)->nullable();
            $table->timestamps();
            $table->foreign('kindergartener_id')->references('id')->on('kindergarteners')->cascadeOnDelete();
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kindergartener_id')->nullable()->index();
            $table->string('event', 64)->index();
            $table->string('channel', 24)->default('mail');
            $table->string('recipient_hash', 64)->nullable();
            $table->string('idempotency_key', 120)->unique();
            $table->string('status', 24)->default('queued')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('users', 'is_administrator')) {
            DB::table('users')->where('is_administrator', 1)->update(['role' => 'union_admin']);
        } else {
            $firstUserId = DB::table('users')->min('id');
            if ($firstUserId) DB::table('users')->where('id', $firstUserId)->update(['role' => 'union_admin']);
        }
        DB::table('kindergarteners')->where('active_status_id', 1)->update(['application_status' => 'waiting']);
        DB::table('kindergarteners')->where('active_status_id', 2)->update(['application_status' => 'enrolled']);
        DB::table('kindergarteners')->where('active_status_id', 3)->update(['application_status' => 'graduated']);
        DB::table('kindergarteners')->where('active_status_id', 4)->update(['application_status' => 'cancelled']);
        DB::table('kindergarteners')->where('application_status', 'waiting')->orderBy('id')->chunkById(200, function ($children) {
            foreach ($children as $child) {
                DB::table('waiting_list_entries')->updateOrInsert(['kindergartener_id' => $child->id], ['kindergarten_id' => $child->kindergarten_id, 'group_id' => $child->group_id, 'priority_rank' => 999, 'queued_at' => $child->created_at ?: now(), 'state' => 'waiting', 'created_at' => now(), 'updated_at' => now()]);
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('reinstatement_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('work_calendar_days');
        Schema::dropIfExists('placement_offers');
        Schema::dropIfExists('waiting_list_entries');
        Schema::dropIfExists('application_status_histories');
        Schema::table('kindergarteners', function (Blueprint $table) {
            $table->dropColumn(['application_status', 'status_changed_at', 'suspended_at']);
        });
        Schema::table('kindergarten_group_age_range', function (Blueprint $table) {
            $table->dropColumn('space_reserved');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'kindergarten_id']);
        });
    }
}
