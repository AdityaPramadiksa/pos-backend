<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom penanda dari aplikasi kasir supaya transaksi yang dikirim ulang
 * (mis. setelah sinyal kembali) tidak tercatat dua kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'client_uuid')) {
                $table->string('client_uuid', 36)->nullable()->unique();
            }
            if (!Schema::hasColumn('orders', 'pay_uuid')) {
                $table->string('pay_uuid', 36)->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'client_batch')) {
                $table->string('client_batch', 36)->nullable()->index();
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'client_uuid')) {
                $table->string('client_uuid', 36)->nullable()->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn(['client_uuid', 'pay_uuid']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['client_batch']);
            $table->dropColumn('client_batch');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn('client_uuid');
        });
    }
};
