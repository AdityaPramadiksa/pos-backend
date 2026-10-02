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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique(); // Contoh: INV-20260312-0001

            // Relasi ke tabel users (Kasir yang bertugas)
            $table->foreignId('user_id')->constrained('users');

            $table->string('customer_name')->nullable()->default('Pelanggan Umum');
            $table->string('table_number')->nullable();

            // Tipe pesanan dasar
            $table->enum('order_type', ['dine_in', 'to_go', 'delivery'])->default('dine_in');

            // Kolom baru: Untuk mendeteksi platform ojol jika order_type adalah delivery
            $table->string('delivery_platform')->nullable(); // Isi: gojek, grab, shopee

            // Perhitungan Uang
            $table->integer('subtotal');
            $table->integer('discount_amount')->default(0);
            $table->integer('tax_amount')->default(0); // Pajak PB1 10%
            $table->integer('total_price'); // Total akhir yang harus dibayar

            // Pembayaran (Update: Tambah credit & delivery)
            $table->enum('payment_method', ['cash', 'qris', 'debit', 'credit', 'delivery'])->nullable();
            $table->integer('amount_paid')->default(0); // Uang yang diterima kasir
            $table->integer('change_amount')->default(0); // Uang kembalian

            // Status Transaksi & Fitur VOID
            $table->enum('status', ['pending', 'paid', 'void'])->default('pending');
            $table->foreignId('void_by')->nullable()->constrained('users');
            $table->string('void_reason')->nullable();

            // Metadata tambahan (Opsional tapi berguna)
            $table->text('notes')->nullable(); // Catatan pesanan seperti "Pedas", dll

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
