<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('trips', 'start_date', 'end_date')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->dropColumn('start_date');
                $table->dropColumn('end_date');
            });
        }

        if (!Schema::hasColumn('trips', 'confirmed_start_date')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->date('confirmed_start_date')->nullable();
                $table->date('confirmed_end_date')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'confirmed_start_date')) {
                $table->dropColumn(['confirmed_start_date', 'confirmed_end_date']);
            }
            if (!Schema::hasColumn('trips', 'start_date', 'end_date')) {
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
            }
        });
    }
};