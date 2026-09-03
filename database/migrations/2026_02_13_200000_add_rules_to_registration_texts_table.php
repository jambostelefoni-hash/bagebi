<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRulesToRegistrationTextsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('registration_texts', 'rules')) {
            Schema::table('registration_texts', function (Blueprint $table) {
                $table->text('rules')->nullable()->after('description');
            });
        }
    }

    public function down()
    {
        Schema::table('registration_texts', function (Blueprint $table) {
            $table->dropColumn('rules');
        });
    }
}
