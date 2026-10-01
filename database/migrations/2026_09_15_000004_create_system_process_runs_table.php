<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemProcessRunsTable extends Migration
{
    public function up()
    {
        Schema::create('system_process_runs', function (Blueprint $table) {
            $table->id();
            $table->string('process', 40)->index();
            $table->string('status', 20)->index();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_process_runs');
    }
}
