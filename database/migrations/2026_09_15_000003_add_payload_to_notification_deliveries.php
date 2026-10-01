<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayloadToNotificationDeliveries extends Migration
{
    public function up()
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('recipient_hash');
        });
    }

    public function down()
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->dropColumn('payload');
        });
    }
}
