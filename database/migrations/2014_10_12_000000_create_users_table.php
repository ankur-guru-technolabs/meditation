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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_no')->unique();
            $table->string('birth_date');
            $table->string('gender');
            $table->string('status')->default(1);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('is_notification_mute')->default(0);
            $table->string('fcm_token')->nullable();
            $table->string('password');
            $table->string('user_type');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
