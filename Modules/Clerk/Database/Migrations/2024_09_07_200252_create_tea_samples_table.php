<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeaSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tea_samples', function (Blueprint $table) {
            $table->string('sample_id', 12)->primary()->unique();
            $table->string('delivery_id', 12);
            $table->string('stock_id', 12);
            $table->double('sample_weight', 10,2);
            $table->double('package_weight', 10,2);
            $table->integer('sample_palletes');
            $table->string('user_id', 12);
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('tea_samples');
    }
}
