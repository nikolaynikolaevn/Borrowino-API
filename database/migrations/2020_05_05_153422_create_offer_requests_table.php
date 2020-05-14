<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('borrower');
            $table->integer('offer');
            $table->timestamp('from')->nullable();
            $table->timestamp('until')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['accepted', 'declined'])->nullable();
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
        Schema::dropIfExists('offer_requests');
    }
}
