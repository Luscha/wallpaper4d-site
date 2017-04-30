<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id'); // Incremental (private) image id
            $table->string('iid'); // Random (public) image id
            
            $table->string('format'); // JPEG, WEBP, PNG, ...
            $table->string('quality'); // default, mqdefault, hqdefault

            $table->integer('height')->unsigned();
            $table->integer('width')->unsigned();

            // The original owner(uploader) of the image, can be null
			$table->integer('channel_id')->unsigned()->nullable();

            $table->boolean('is_default'); // Default resolutions/quality (see ImageController.php)
            $table->boolean('is_private'); // Only accessible by its owner
            $table->boolean('is_thumbnail'); // Low-resolution images for preview

            $table->boolean('age_restricted'); // Restriction for 18+ images

            $table->unique(['iid', 'format', 'quality', 'height', 'width', 'is_thumbnail']);

			//$table->foreign('channel_id')->references('id')->on('channels');
            
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('images');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
