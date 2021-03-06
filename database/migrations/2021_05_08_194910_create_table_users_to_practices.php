<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsersToPractices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_to_practices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('practice_id')->unsigned();
            $table->bigInteger('users_to_practices_status_id')->unsigned();

            $table->unique(['user_id', 'practice_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('practice_id')->references('id')->on('practices')->onDelete('cascade');
            $table->foreign('users_to_practices_status_id')->references('id')->on('users_to_practices_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_to_practices');
    }
}
