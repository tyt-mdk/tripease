<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->string('day_label');  // "1日目" や "事前準備" など自由なラベル
            $table->text('memo');
            $table->integer('order')->default(0);  // 表示順
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('itineraries');
    }
};
