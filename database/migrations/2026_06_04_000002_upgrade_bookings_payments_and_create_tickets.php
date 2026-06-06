<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('bookings', 'booking_code')) {
                $table->string('booking_code')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('bookings', 'contact_name')) {
                $table->string('contact_name', 100)->nullable()->after('participant_count');
            }

            if (! Schema::hasColumn('bookings', 'contact_phone')) {
                $table->string('contact_phone', 30)->nullable()->after('contact_name');
            }

            if (! Schema::hasColumn('bookings', 'contact_email')) {
                $table->string('contact_email', 100)->nullable()->after('contact_phone');
            }

            if (! Schema::hasColumn('bookings', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('contact_email');
            }

            if (! Schema::hasColumn('bookings', 'service_fee')) {
                $table->decimal('service_fee', 12, 2)->default(0)->after('subtotal');
            }

            if (! Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            if (! Schema::hasColumn('bookings', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('notes');
            }
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('status', 40)->default('waiting_payment')->change();
        });

        DB::table('bookings')
            ->whereNull('booking_code')
            ->orWhere('booking_code', '')
            ->orderBy('id')
            ->get(['id', 'total_price', 'status'])
            ->each(function (object $booking): void {
                $status = match ($booking->status) {
                    'pending', 'waiting_verification' => 'waiting_payment',
                    default => $booking->status,
                };

                DB::table('bookings')->where('id', $booking->id)->update([
                    'booking_code' => 'BK-'.now()->format('ymd').'-'.str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT),
                    'subtotal' => $booking->total_price ?? 0,
                    'service_fee' => 0,
                    'status' => $status,
                    'expires_at' => $status === 'waiting_payment' ? now()->addDay() : null,
                    'updated_at' => now(),
                ]);
            });

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'file_path')) {
                $table->text('file_path')->nullable()->change();
            }

            if (! Schema::hasColumn('payments', 'order_id')) {
                $table->string('order_id')->nullable()->unique()->after('booking_id');
            }

            if (! Schema::hasColumn('payments', 'snap_token')) {
                $table->text('snap_token')->nullable()->after('order_id');
            }

            if (! Schema::hasColumn('payments', 'snap_redirect_url')) {
                $table->text('snap_redirect_url')->nullable()->after('snap_token');
            }

            if (! Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('snap_redirect_url');
            }

            if (! Schema::hasColumn('payments', 'transaction_status')) {
                $table->string('transaction_status')->nullable()->after('payment_type');
            }

            if (! Schema::hasColumn('payments', 'fraud_status')) {
                $table->string('fraud_status')->nullable()->after('transaction_status');
            }

            if (! Schema::hasColumn('payments', 'gross_amount')) {
                $table->decimal('gross_amount', 12, 2)->default(0)->after('fraud_status');
            }

            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('gross_amount');
            }

            if (! Schema::hasColumn('payments', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('paid_at');
            }

            if (! Schema::hasColumn('payments', 'raw_response')) {
                $table->json('raw_response')->nullable()->after('expired_at');
            }
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->string('status', 40)->default('unpaid')->change();
        });

        DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->where(function ($query): void {
                $query->whereNull('payments.order_id')
                    ->orWhere('payments.order_id', '');
            })
            ->select('payments.id', 'payments.status', 'bookings.booking_code', 'bookings.total_price')
            ->orderBy('payments.id')
            ->get()
            ->each(function (object $payment): void {
                $status = match ($payment->status) {
                    'approved' => 'paid',
                    'rejected' => 'failed',
                    'pending' => 'pending',
                    default => $payment->status ?: 'unpaid',
                };

                DB::table('payments')->where('id', $payment->id)->update([
                    'order_id' => 'ORDER-'.$payment->booking_code,
                    'gross_amount' => $payment->total_price ?? 0,
                    'status' => $status,
                    'updated_at' => now(),
                ]);
            });

        if (! Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('ticket_code')->unique();
                $table->string('qr_code_path')->nullable();
                $table->string('status', 40)->default('active')->index();
                $table->timestamp('checked_in_at')->nullable();
                $table->timestamps();
            });
        }

        DB::table('bookings')
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNotExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('tickets')
                    ->whereColumn('tickets.booking_id', 'bookings.id');
            })
            ->get(['id', 'booking_code'])
            ->each(function (object $booking): void {
                DB::table('tickets')->insert([
                    'booking_id' => $booking->id,
                    'ticket_code' => 'TKT-'.$booking->booking_code,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');

        Schema::table('payments', function (Blueprint $table): void {
            foreach ([
                'raw_response',
                'expired_at',
                'paid_at',
                'gross_amount',
                'fraud_status',
                'transaction_status',
                'payment_type',
                'snap_redirect_url',
                'snap_token',
                'order_id',
            ] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bookings', function (Blueprint $table): void {
            foreach ([
                'expires_at',
                'notes',
                'service_fee',
                'subtotal',
                'contact_email',
                'contact_phone',
                'contact_name',
                'booking_code',
            ] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
