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
    Schema::create('discounts', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Nama Promo
        $table->enum('type', ['percentage', 'fixed']); // Persen atau Potongan Tetap
        $table->integer('value'); // Nilai diskon (misal: 10 atau 5000)
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
