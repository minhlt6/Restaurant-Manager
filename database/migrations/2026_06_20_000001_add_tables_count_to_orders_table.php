<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Số bàn đặt tiệc (chỉ dành cho khách sỉ, null với khách lẻ)
            $table->unsignedSmallInteger('tables_count')->nullable()->after('customer_id');
            // Tiền đặt cọc
            $table->decimal('deposit', 10, 2)->default(0)->after('tables_count');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tables_count', 'deposit']);
        });
    }
};
