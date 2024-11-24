<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->string('payment_id', 12)->primary()->unique();
            $table->string('invoice_number', 18)->unique();
            $table->string('client_id', 12);
            $table->string('financial_year_id', 12);
            $table->string('account_id', 12);
            $table->bigInteger('date_received');
            $table->double('amount_received', 12, 2);
            $table->string('transaction_code', 50)->nullable();
            $table->mediumText('description')->nullable();
            $table->string('user_id', 12);
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
        Schema::dropIfExists('payments');
    }
}
