<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Http\Functions;

class CreateTableUsers extends Migration
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
            $table->timestamps();
            $table->softDeletes();

            $table->string('login', 64);
            $table->string('first_name', 64);
            $table->string('second_name', 64);
            $table->string('third_name', 64)->nullable();
            $table->string('email', 64)->nullable();
            $table->tinyInteger('show_email')->default(Functions::SETTING_VALUE_NOONE);
            $table->string('phone', 16)->nullable();
            $table->tinyInteger('show_phone')->default(Functions::SETTING_VALUE_NOONE);
            $table->tinyInteger('accept_chats_from')->default(Functions::SETTING_VALUE_ALL);
            $table->string('password_sha512', 128);
            $table->bigInteger('role_id')->default(0)->unsigned();
            $table->bigInteger('institution_id')->nullable()->unsigned();
            $table->timestamp('last_activity_at')->nullable();
            $table->bigInteger('avatar_file_id')->nullable()->unsigned();

            $table->unique('login');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('institution_id')->references('id')->on('institutions');
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
}
