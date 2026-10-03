<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Aktifkan lisensi agar middleware EnsureLicensed mengizinkan akses ke menu stok
        app(LicenseService::class)->activate('NTRS-DEMO-2026-DEV1', [
            'supplier_name' => 'CV Nutrisaka Sejahtera',
            'pic_name' => 'Ahmad',
            'supplier_phone' => '08123456789',
            'pin' => '1234',
        ]);
    }

    public function test_stock_in_route_resolves_and_returns_http_200(): void
    {
        $response = $this->get('/stock/in');
        $response->assertStatus(200);
        $response->assertViewIs('stock.in');
    }

    public function test_stock_adjustment_route_resolves_and_returns_http_200(): void
    {
        $response = $this->get('/stock/adjustment');
        $response->assertStatus(200);
        $response->assertViewIs('stock.adjustment');
    }

    public function test_stock_history_route_resolves_and_returns_http_200(): void
    {
        $response = $this->get('/stock/history');
        $response->assertStatus(200);
        $response->assertViewIs('stock.history');
    }

    public function test_stock_show_product_route_resolves_for_numeric_id(): void
    {
        $category = Category::create(['name' => 'Bahan Baku']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'PRD-TEST-99',
            'name' => 'Beras Pandan Wangi',
            'unit' => 'Kg',
            'selling_price' => 15000,
            'cost_price' => 12000,
            'current_stock' => 100,
            'min_stock' => 10,
        ]);

        $response = $this->get('/stock/' . $product->id);
        $response->assertStatus(200);
        $response->assertViewIs('stock.show');
        $response->assertSee('Beras Pandan Wangi');
    }
}
