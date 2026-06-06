<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->date('visit_date')->index();
            $table->unsignedInteger('participant_count');
            $table->decimal('total_price', 12, 2);
            $table->enum('status', [
                'pending',
                'waiting_verification',
                'confirmed',
                'cancelled',
                'completed',
            ])->default('pending')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['service_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
