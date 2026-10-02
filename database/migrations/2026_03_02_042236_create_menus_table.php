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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            // 1. Relasi Kategori (Pastikan tabel categories sudah dimigrate duluan)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            // 2. Identitas Menu
            $table->string('name');
            $table->string('image')->nullable(); // Foto menu (disimpan path-nya)

            // 3. Logika Harga Bertingkat (Sinkron dengan Flutter POS)
            // price_dine_in = Harga di Resto (Dine In / To Go)
            // price_online = Harga di Ojol (Grab/Gojek/Shopee)
            $table->integer('price_dine_in')->default(0);
            $table->integer('price_online')->default(0);
            $table->integer('price')->default(0); // Harga dasar (fallback jika butuh)

            // 4. Manajemen Stok & Status
            $table->integer('stock')->default(0); // Sisa porsi yang tersedia
            $table->boolean('is_available')->default(true); // Switch On/Off Menu

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
