<?php

namespace App\Services;

use App\Models\Catalog\ProductStock;
use App\Models\Sell\ProductReturn;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(private SerialService $serialService) {}

    public function generateNumber(): string
    {
        $max = ProductReturn::withTrashed()->max('number');
        if (!$max) return 'RET-0001';
        $num = (int) substr($max, 4);
        return 'RET-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    public function processRefund(ProductReturn $return): void
    {
        DB::transaction(function () use ($return) {
            foreach ($return->items()->with(['product.stock', 'serial'])->get() as $item) {
                if ($item->serial_id && $item->serial) {
                    // Serial product: mark returned via SerialService
                    $this->serialService->markReturned($item->serial, $item->id);
                } elseif ($item->product_id) {
                    // Normal product: restore stock
                    $stock = ProductStock::firstOrCreate(
                        ['product_id' => $item->product_id, 'warehouse' => 'main'],
                        ['quantity' => 0, 'reserved' => 0]
                    );
                    $stock->increment('quantity', (int) $item->quantity);
                }
            }

            $return->update([
                'status'      => 'refunded',
                'refunded_at' => now(),
            ]);
        });
    }
}
