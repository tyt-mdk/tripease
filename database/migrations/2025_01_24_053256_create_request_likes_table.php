<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('request_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // 同じユーザーが同じ要望に複数回いいねできないようにする
            $table->unique(['trip_request_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_likes');
    }
};