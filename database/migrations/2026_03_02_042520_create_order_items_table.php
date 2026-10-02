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
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();

        // Relasi ke order utama (Jika order dihapus, item ikut terhapus / cascade)
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

        // Relasi ke menu yang dipesan
        $table->foreignId('menu_id')->constrained('menus');

        $table->integer('price'); // Harga satuan saat itu
        $table->integer('qty'); // Jumlah pesanan
        $table->integer('subtotal'); // price * qty
        $table->string('note')->nullable(); // Catatan: "Pedas sedikit"
        $table->boolean('is_kitchen_printed')->default(false); // Penanda print dapur

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
