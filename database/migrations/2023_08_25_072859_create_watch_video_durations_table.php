<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watch_video_durations', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->string('video_id')->index();
            $table->string('category_id')->index();
            $table->bigInteger('duration');
            $table->string('play_date')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('watch_video_durations');
    }
};
