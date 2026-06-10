<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('bookings', 'cancelled_reason')) {
                $table->text('cancelled_reason')->nullable()->after('expires_at');
            }

            if (! Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_reason');
            }

            if (! Schema::hasColumn('bookings', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (Schema::hasColumn('bookings', 'cancelled_by')) {
                $table->dropConstrainedForeignId('cancelled_by');
            }

            foreach (['cancelled_at', 'cancelled_reason'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
