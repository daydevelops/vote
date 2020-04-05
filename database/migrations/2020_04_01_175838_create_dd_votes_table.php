<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDDVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dd_votes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('voted_id');
            $table->string('voted_type');
            $table->integer('value');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('votable_user_id')->nullable(); // the id of the owner of the votable object, if there is an owner
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
        Schema::dropIfExists('votes');
    }
}
