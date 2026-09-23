<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('instructions')->nullable()->change();
            $table->index(['instructor', 'start_date', 'start_time'], 'bookings_instructor_schedule_index');
            $table->index(['user_id', 'start_date'], 'bookings_user_schedule_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_instructor_schedule_index');
            $table->dropIndex('bookings_user_schedule_index');
        });
    }
};
