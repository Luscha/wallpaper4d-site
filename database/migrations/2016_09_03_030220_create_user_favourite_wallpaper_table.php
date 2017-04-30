<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFavouriteWallpaperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::create('user_favourite_wallpapers', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('image_id')->unsigned();
			
			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('image_id')->references('id')->on('images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('user_favourite_wallpapers');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
