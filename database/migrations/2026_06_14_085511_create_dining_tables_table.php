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
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);  
            $table->unsignedInteger('capacity')->default(0)->comment('Số ghế ngồi');
            $table->tinyInteger('status')->default(0)->comment('0: Trống, 1: Đang phục vụ, 2: Đã đặt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.  
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_tables');
    }
};
