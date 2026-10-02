<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengikat order & pengeluaran ke shift (settlement) tempat uangnya masuk/keluar,
     * supaya rekap shift tidak lagi bergantung pada tanggal (dobel hitung jika 2 shift sehari).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'settlement_id')) {
                $table->unsignedBigInteger('settlement_id')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'settlement_id')) {
                $table->unsignedBigInteger('settlement_id')->nullable()->index();
            }
        });

        Schema::table('settlements', function (Blueprint $table) {
            if (!Schema::hasColumn('settlements', 'total_credit_sales')) {
                $table->bigInteger('total_credit_sales')->default(0);
            }
            if (!Schema::hasColumn('settlements', 'total_delivery_sales')) {
                $table->bigInteger('total_delivery_sales')->default(0);
            }
            if (!Schema::hasColumn('settlements', 'notes')) {
                $table->string('notes')->nullable();
            }
        });

        // Backfill data lama: order lunas dianggap dibayar saat terakhir di-update
        DB::table('orders')
            ->where('status', 'paid')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);

        // Backfill data lama: pasangkan order & pengeluaran ke shift kasir yang sedang berjalan saat itu
        foreach (DB::table('settlements')->orderBy('id')->get() as $settlement) {
            $until = $settlement->closed_at ?? now();

            DB::table('orders')
                ->whereNull('settlement_id')
                ->where('user_id', $settlement->user_id)
                ->whereIn('status', ['paid', 'void'])
                ->whereBetween('created_at', [$settlement->created_at, $until])
                ->update(['settlement_id' => $settlement->id]);

            DB::table('expenses')
                ->whereNull('settlement_id')
                ->where('user_id', $settlement->user_id)
                ->whereBetween('created_at', [$settlement->created_at, $until])
                ->update(['settlement_id' => $settlement->id]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['settlement_id', 'paid_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('settlement_id');
        });
    }
};
