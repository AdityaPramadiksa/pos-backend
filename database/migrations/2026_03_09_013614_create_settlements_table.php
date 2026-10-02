<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Kasir yang bertanggung jawab
            $table->bigInteger('starting_cash'); // Uang modal awal
            $table->bigInteger('total_cash_sales')->default(0); // Penjualan Cash
            $table->bigInteger('total_qris_sales')->default(0); // Penjualan QRIS
            $table->bigInteger('total_debit_sales')->default(0); // Penjualan Debit
            $table->bigInteger('total_credit_sales')->default(0); // FIX: Tambah Penjualan Credit
            $table->bigInteger('total_delivery_sales')->default(0); // FIX: Tambah Penjualan Delivery (Ojol)
            $table->bigInteger('total_expenses')->default(0); // Total Petty Cash/Pengeluaran
            $table->bigInteger('actual_cash_on_hand')->nullable(); // Uang fisik di laci saat tutup
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
