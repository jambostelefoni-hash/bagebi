<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaToPublicPagesTable extends Migration
{
    public function up()
    {
        Schema::table('public_pages', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('body');
        });
    }

    public function down()
    {
        Schema::table('public_pages', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
}
