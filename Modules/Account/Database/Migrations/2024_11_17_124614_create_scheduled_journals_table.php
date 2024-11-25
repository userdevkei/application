<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_journals', function (Blueprint $table) {
            $table->string('scheduled_journal_id', 12)->primary()->unique();
            $table->string('journal_schedule_id', 12);
            $table->double('amount_settled', 15, 2);
            $table->timestamp('date_settled')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('scheduled_journals');
    }
}
