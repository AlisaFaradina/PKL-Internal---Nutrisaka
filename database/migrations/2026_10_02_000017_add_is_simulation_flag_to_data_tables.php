<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'categories',
            'products',
            'sppgs',
            'orders',
            'sales',
            'payments',
            'stock_movements',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'is_simulation')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->boolean('is_simulation')->default(false)->index();
                });
            }
        }

        // Tandai data sampel bawaan yang sudah ada di database saat ini sebagai simulasi
        if (Schema::hasTable('products')) {
            DB::table('products')->where('sku', 'like', 'PRD-%')->update(['is_simulation' => true]);
        }
        if (Schema::hasTable('sppgs')) {
            DB::table('sppgs')->where('code', 'like', 'SPPG-%')->update(['is_simulation' => true]);
        }
        if (Schema::hasTable('orders')) {
            DB::table('orders')->where('order_number', 'like', 'ORD-%')->update(['is_simulation' => true]);
        }
        if (Schema::hasTable('sales')) {
            DB::table('sales')->where('invoice_number', 'like', 'INV-%')->update(['is_simulation' => true]);
        }
        if (Schema::hasTable('payments')) {
            DB::table('payments')->where('payment_number', 'like', 'PAY-%')->update(['is_simulation' => true]);
        }
        if (Schema::hasTable('stock_movements')) {
            DB::table('stock_movements')->update(['is_simulation' => true]);
        }
        if (Schema::hasTable('categories')) {
            DB::table('categories')->update(['is_simulation' => true]);
        }
    }

    public function down(): void
    {
        $tables = [
            'categories',
            'products',
            'sppgs',
            'orders',
            'sales',
            'payments',
            'stock_movements',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_simulation')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('is_simulation');
                });
            }
        }
    }
};
