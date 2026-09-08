<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('messages', function (Blueprint $table) {
            // Intentionally no foreign key: users lives on a different
            // connection (mysql/laravelpro), which MySQL foreign keys can't
            // span. Nullable - system-generated notifications (dive
            // reminders, wishlist alerts) have no human "from".
            $table->unsignedBigInteger('from_user_id')->nullable()->after('userId');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('messages', function (Blueprint $table) {
            $table->dropColumn('from_user_id');
        });
    }
};
