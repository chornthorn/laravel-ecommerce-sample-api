<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('order_id');
             $table->foreign('order_id')->references('id')->on('orders')
                 ->onDelete('cascade');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
