<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_views', function (Blueprint $table) {
            $table->increments('id'); // Incremental (private) views id
            $table->string('image_iid'); // Random (public) image id

            $table->integer('internal_views')->unsigned(); // Views which origin is anime4d
            $table->integer('external_views')->unsigned(); // Views which origin is *not* anime4d

            $table->boolean('is_thumbnail'); // Low-resolution images for preview

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('image_iid')->references('iid')->on('images');
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
        Schema::dropIfExists('image_views');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
