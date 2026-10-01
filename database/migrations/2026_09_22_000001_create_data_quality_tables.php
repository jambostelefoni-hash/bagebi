<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataQualityTables extends Migration
{
    public function up()
    {
        Schema::create('data_quality_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('triggered_by')->nullable()->index();
            $table->string('source', 24)->default('scheduled');
            $table->string('status', 24)->default('running')->index();
            $table->unsignedInteger('issues_found')->default(0);
            $table->unsignedInteger('critical_count')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('info_count')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('data_quality_issues', function (Blueprint $table) {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->string('type', 64)->index();
            $table->string('severity', 16)->index();
            $table->string('status', 16)->default('open')->index();
            $table->string('entity_type', 40)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->unsignedBigInteger('kindergarten_id')->nullable()->index();
            $table->string('message', 500);
            $table->json('details')->nullable();
            $table->timestamp('first_detected_at');
            $table->timestamp('last_detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'severity', 'type'], 'quality_issue_filters');
            $table->index(['entity_type', 'entity_id'], 'quality_issue_entity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_quality_issues');
        Schema::dropIfExists('data_quality_scans');
    }
}
