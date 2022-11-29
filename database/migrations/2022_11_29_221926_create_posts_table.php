<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image');
            $table->boolean('pinned')->default(false);
            $table->integer('user_id');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('tag_post', function (Blueprint $table) {
            $table->integer('tag_id');
            $table->integer('post_id');
        });
    }


    public function down()
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('tag_post');
    }
}
