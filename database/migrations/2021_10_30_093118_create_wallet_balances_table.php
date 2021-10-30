<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_balances', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->integer('user_id');
            $table->integer('destination_id')->nullable()->default(0);
            $table->bigInteger('amount');
            $table->string('description')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('wallet_number')->after('email');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_balances');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_number');
        });
    }
}
