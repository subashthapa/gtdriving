<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0)->after('instructions');
            $table->string('payment_method')->default('cash')->after('amount');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'amount',
                'payment_method',
                'payment_status',
                'paid_at',
                'payment_reference',
            ]);
        });
    }
};
