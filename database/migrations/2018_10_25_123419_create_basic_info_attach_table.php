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
            $table->string('type')->comment('即表单类型，对应category表类型');
            $table->integer('bid')->comment('对应basicinfo基本信息表的id');
            $table->integer('uid')->comment('添加信息的用户id');
            $table->integer('categoryId')->comment('对应该字段所对应category表id');
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
