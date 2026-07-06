<?php

namespace App\Services;

use App\Models\Catalog\ProductSerial;
use App\Models\Catalog\SerialOwner;
use App\Models\Catalog\SerialStatus;

class SerialService
{
    public function markSold(ProductSerial $serial, int $customerId, int $sellItemId, ?int $userId = null): void
    {
        $serial->update([
            'current_status'   => 'sold',
            'current_owner_id' => $customerId,
        ]);

        SerialStatus::create([
            'serial_id'  => $serial->id,
            'status'     => 'sold',
            'changed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);

        SerialOwner::create([
            'serial_id'    => $serial->id,
            'customer_id'  => $customerId,
            'sell_item_id' => $sellItemId,
            'acquired_at'  => now(),
        ]);
    }

    public function markReturned(ProductSerial $serial, int $returnItemId, ?int $userId = null): void
    {
        $serial->update([
            'current_status'   => 'returned',
            'current_owner_id' => null,
        ]);

        SerialStatus::create([
            'serial_id'  => $serial->id,
            'status'     => 'returned',
            'changed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);

        // Release current owner record
        SerialOwner::where('serial_id', $serial->id)
            ->whereNull('released_at')
            ->update([
                'released_at'    => now(),
                'return_item_id' => $returnItemId,
            ]);
    }

    public function markAvailable(ProductSerial $serial, ?int $userId = null): void
    {
        $serial->update(['current_status' => 'available', 'current_owner_id' => null]);

        SerialStatus::create([
            'serial_id'  => $serial->id,
            'status'     => 'available',
            'changed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    public function registerExternal(
        string  $serialNumber,
        ?string $extBrand,
        ?string $extModel,
        ?int    $customerId = null,
        ?string $notes = null
    ): ProductSerial {
        $serial = ProductSerial::create([
            'product_id'       => null,
            'serial_number'    => $serialNumber,
            'is_external'      => true,
            'ext_brand'        => $extBrand ?: null,
            'ext_model'        => $extModel ?: null,
            'current_status'   => 'in_repair',
            'current_owner_id' => $customerId,
            'notes'            => $notes ?: null,
        ]);

        SerialStatus::create([
            'serial_id'  => $serial->id,
            'status'     => 'in_repair',
            'changed_by' => auth()->id(),
            'notes'      => 'Зарегистрировано при создании тикета (внешнее оборудование)',
            'created_at' => now(),
        ]);

        if ($customerId) {
            SerialOwner::create([
                'serial_id'   => $serial->id,
                'customer_id' => $customerId,
                'acquired_at' => now(),
            ]);
        }

        return $serial;
    }

    public function markInRepair(
        ProductSerial $serial,
        ?string $notes = null,
        ?int $userId = null
    ): void {
        $serial->update(['current_status' => 'in_repair']);

        SerialStatus::create([
            'serial_id'  => $serial->id,
            'status'     => 'in_repair',
            'changed_by' => $userId ?? auth()->id(),
            'notes'      => $notes,
            'created_at' => now(),
        ]);
    }
}
