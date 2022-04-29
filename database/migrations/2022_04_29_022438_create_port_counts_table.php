<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('port_counts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('ix')->index();
            $table->string('site')->index();
            $table->string('switch')->index();

            $table->integer('count_100')->index();
            $table->integer('count_1000')->index();
            $table->integer('count_10000')->index();
            $table->integer('count_40000')->index();
            $table->integer('count_100000')->index();
            $table->integer('count_400000')->index();

            $table->integer('total_cross_connects')->index();
            $table->integer('used_cross_connects')->index();
            $table->integer('free_cross_connects')->index();

            $table->index(['ix', 'site', 'switch']);
            $table->index(['site', 'switch']);
            $table->index(['ix', 'switch']);

            $table->unique(['ix', 'site', 'switch', 'created_at']);
        });
    }

};
