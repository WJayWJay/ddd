<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type')->comment('类别, 1.填空, 2.单选, 3.时间选择, 4.地区选择, 5.图片上传');
            $table->integer('uid')->comment('谁添加的');
            $table->string('projectName')->comment('类别名');
            $table->string('proAliasName')->comment('类别英文名,表单字段名');
            $table->integer('submit')->comment('1提交资料中显示， 1 显示 0, 不显示');
            $table->integer('basic')->comment('2基本信息列表中显示， 1 显示 0, 不显示');
            $table->integer('filter')->comment('3作为基本信息筛选项， 1 显示 0, 不显示');
            $table->text('options')->comment('用做单选项');
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
        Schema::dropIfExists('category');
    }
}
