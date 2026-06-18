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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->string('name',30);
            $table->string('gender',5);
            $table->string('address',250)->nullable();
            $table->string('phone',11)->nullable()->unique();
            $table->string('email',50)->nullable()->unique();
            $table->timestamps();

            $table->foreign('customer_group_id')->references('id')->on('customer_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
