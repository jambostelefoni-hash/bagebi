<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderStatusToNotificationDeliveries extends Migration
{
    public function up()
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->string('provider_status', 24)->nullable()->index()->after('status');
            $table->string('provider_reason', 500)->nullable()->after('provider_status');
            $table->timestamp('provider_updated_at')->nullable()->after('provider_reason');
            $table->timestamp('delivered_at')->nullable()->after('provider_updated_at');
        });
    }

    public function down()
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->dropIndex(['provider_status']);
            $table->dropColumn(['provider_status', 'provider_reason', 'provider_updated_at', 'delivered_at']);
        });
    }
}
