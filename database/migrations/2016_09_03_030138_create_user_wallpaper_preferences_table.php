<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWallpaperPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_wallpaper_preferences', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->string('attribute');
            $table->integer('attribute_count')->unsigned();
			
			$table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('user_wallpaper_preferences');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
