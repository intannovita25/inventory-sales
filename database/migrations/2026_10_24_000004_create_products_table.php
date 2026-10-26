<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: kategori yang masih punya produk tidak bisa dihapus
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name', 150)->index();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->timestamps();
            $table->softDeletes(); // produk yang pernah dipakai transaksi tidak di-hard-delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
