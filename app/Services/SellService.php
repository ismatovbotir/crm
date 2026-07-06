<?php

namespace App\Services;

use App\Models\Invoice\Invoice;
use App\Models\Sell\Sell;

class SellService
{
    public function generateNumber(): string
    {
        $max = Sell::withTrashed()->max('number');

        if (!$max) {
            return 'SL-0001';
        }

        $num = (int) substr($max, 3);

        return 'SL-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateShipmentStatus(Invoice $invoice): void
    {
        $sells = $invoice->sells()->whereIn('status', ['shipped', 'delivered'])->get();

        if ($sells->isEmpty()) {
            $status = 'none';
        } else {
            $shipped = $sells->sum('total');
            $invoiceTotal = (float) $invoice->total;

            if ($invoiceTotal > 0 && $shipped >= $invoiceTotal) {
                $status = 'complete';
            } else {
                $status = 'partial';
            }
        }

        $invoice->update(['shipment_status' => $status]);
    }
}
