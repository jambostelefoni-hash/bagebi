<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShortActionLinksTable extends Migration
{
    public function up()
    {
        Schema::create('short_action_links', function (Blueprint $table) {
            $table->id();
            $table->string('code_hash', 64)->unique();
            $table->text('destination_url');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('short_action_links');
    }
}
