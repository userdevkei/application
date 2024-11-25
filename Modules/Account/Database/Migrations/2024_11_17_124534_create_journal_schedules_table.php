<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_schedules', function (Blueprint $table) {
            $table->string('journal_schedule_id', 12)->unique()->primary();
            $table->string('purchase_id', 12);
            $table->string('journal_id', 12);
            $table->integer('duration');
            $table->double('amount_due', 15, 2);
            $table->double('monthly_due', 15, 2);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('journal_schedules');
    }
}
