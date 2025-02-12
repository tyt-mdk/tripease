<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            // start_dateカラムのみを削除
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            // ロールバック時にstart_dateを戻す
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }
};