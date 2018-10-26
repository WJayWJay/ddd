<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBasicInfoAttachTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('basicinfoattach', function (Blueprint $table) {
            $table->increments('id');
            $table->string('property')->comment('属性，即表单属性');
            $table->string('value')->comment('属性值, 即表单值');
            $table->integer('bid')->comment('对应basicinfo基本信息表的id');
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
        Schema::dropIfExists('basicinfoattach');
    }
}
