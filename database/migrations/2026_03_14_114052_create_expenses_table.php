<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            // Relasi ke kasir yang input pengeluaran
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->integer('amount'); // Nominal uang keluar (Misal: 50000)
            $table->string('description'); // Keterangan (Misal: "Beli Es Batu & Gas")
            $table->string('receipt_image')->nullable(); // Nama file foto nota

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
