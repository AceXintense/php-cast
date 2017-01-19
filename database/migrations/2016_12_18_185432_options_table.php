<?php

use App\Option;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('value');
            $table->timestamps();
        });

        $shuffle = new Option();
        $shuffle->name = 'shuffle';
        $shuffle->value = false;
        $shuffle->save();

        $playThrough = new Option();
        $playThrough->name = 'play_through';
        $playThrough->value = false;
        $playThrough->save();

        $playThroughDirection = new Option();
        $playThroughDirection->name = 'play_through_direction';
        $playThroughDirection->value = 'down';
        $playThroughDirection->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('options');
    }
}
