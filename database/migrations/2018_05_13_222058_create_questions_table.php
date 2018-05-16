<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('statement');
            $table->string('rightAnswer');
            $table->string('wrongAnswer1');
            $table->string('wrongAnswer2');
            $table->integer('rightAnswerCount')->default(0);
            $table->integer('wrongAnswer1Count')->default(0);
            $table->integer('wrongAnswer2Count')->default(0);
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
        Schema::dropIfExists('questions');
    }
}
