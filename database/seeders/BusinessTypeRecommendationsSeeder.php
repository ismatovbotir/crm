<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\Catalog\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTypeRecommendationsSeeder extends Seeder
{
    public function run(): void
    {
        // Структура: slug бизнес-типа => [['sku' => '...', 'priority' => '...', 'sort_order' => N, 'notes' => '...']]
        $map = [
            'shop' => [
                ['sku' => 'POS-SUNMI-T2L',        'priority' => 'required',    'sort_order' => 1, 'notes' => 'Основной кассовый узел'],
                ['sku' => 'KKT-ATOL-91F',          'priority' => 'required',    'sort_order' => 2, 'notes' => 'Фискальная регистрация'],
                ['sku' => 'SCN-HW-1250G',          'priority' => 'required',    'sort_order' => 3, 'notes' => 'Сканирование штрихкодов на кассе'],
                ['sku' => 'RCP-EPS-TMT20III',      'priority' => 'required',    'sort_order' => 4, 'notes' => 'Печать кассовых чеков'],
                ['sku' => 'CASH-ATOL-CD57',        'priority' => 'recommended', 'sort_order' => 5, 'notes' => 'Денежный ящик'],
                ['sku' => 'DSP-POSIFLEX-PD3000',   'priority' => 'recommended', 'sort_order' => 6, 'notes' => 'Дисплей покупателя'],
                ['sku' => 'ACC-PAPER-80X80-10',    'priority' => 'optional',    'sort_order' => 7, 'notes' => 'Расходные материалы'],
            ],
            'supermarket' => [
                ['sku' => 'POS-POSIFLEX-RT5015',   'priority' => 'required',    'sort_order' => 1, 'notes' => 'Стационарная касса'],
                ['sku' => 'KKT-ATOL-91F',          'priority' => 'required',    'sort_order' => 2, 'notes' => 'Фискальная регистрация'],
                ['sku' => 'SCALE-CAS-LP15R',       'priority' => 'required',    'sort_order' => 3, 'notes' => 'Весы с печатью этикеток'],
                ['sku' => 'SCN-ZBR-DS2208',        'priority' => 'required',    'sort_order' => 4, 'notes' => '2D-сканер для QR и штрихкодов'],
                ['sku' => 'LBL-ZBR-ZD220',         'priority' => 'recommended', 'sort_order' => 5, 'notes' => 'Печать ценников и этикеток'],
                ['sku' => 'CASH-ATOL-CD57',        'priority' => 'recommended', 'sort_order' => 6, 'notes' => 'Денежный ящик'],
                ['sku' => 'DSP-SUNMI-DS026',       'priority' => 'recommended', 'sort_order' => 7, 'notes' => 'Медиа-дисплей покупателя'],
                ['sku' => 'ACC-LBL-58X40-1000',   'priority' => 'optional',    'sort_order' => 8, 'notes' => 'Этикетки для весов'],
            ],
            'restaurant' => [
                ['sku' => 'POS-SUNMI-T2L',         'priority' => 'required',    'sort_order' => 1, 'notes' => 'POS-станция кассира'],
                ['sku' => 'POS-SUNMI-P2',          'priority' => 'recommended', 'sort_order' => 2, 'notes' => 'Мобильный терминал официанта'],
                ['sku' => 'RCP-EPS-TMT20III',      'priority' => 'required',    'sort_order' => 3, 'notes' => 'Принтер кухонных чеков'],
                ['sku' => 'KKT-ATOL-91F',          'priority' => 'required',    'sort_order' => 4, 'notes' => 'Фискальная регистрация'],
                ['sku' => 'DSP-SUNMI-DS026',       'priority' => 'optional',    'sort_order' => 5, 'notes' => 'Дисплей заказов для клиентов'],
            ],
            'cafe' => [
                ['sku' => 'POS-SUNMI-T2L',         'priority' => 'required',    'sort_order' => 1, 'notes' => 'Кассовый терминал'],
                ['sku' => 'KKT-ATOL-91F',          'priority' => 'required',    'sort_order' => 2, 'notes' => 'Фискальная регистрация'],
                ['sku' => 'RCP-EPS-TMT20III',      'priority' => 'recommended', 'sort_order' => 3, 'notes' => 'Принтер чеков'],
                ['sku' => 'POS-SUNMI-P2',          'priority' => 'optional',    'sort_order' => 4, 'notes' => 'Мобильный POS для выездных заказов'],
            ],
            'pharmacy' => [
                ['sku' => 'POS-POSIFLEX-RT5015',   'priority' => 'required',    'sort_order' => 1, 'notes' => 'Аптечная касса'],
                ['sku' => 'KKT-ATOL-91F',          'priority' => 'required',    'sort_order' => 2, 'notes' => 'Фискальная регистрация'],
                ['sku' => 'SCN-ZBR-DS2208',        'priority' => 'required',    'sort_order' => 3, 'notes' => 'Сканирование 2D-кодов (DataMatrix на лекарствах)'],
                ['sku' => 'RCP-EPS-TMT20III',      'priority' => 'required',    'sort_order' => 4, 'notes' => 'Чековый принтер'],
                ['sku' => 'CASH-ATOL-CD57',        'priority' => 'optional',    'sort_order' => 5, 'notes' => 'Денежный ящик'],
            ],
            'warehouse' => [
                ['sku' => 'SCN-HW-1202G',          'priority' => 'required',    'sort_order' => 1, 'notes' => 'Беспроводной сканер для склада'],
                ['sku' => 'LBL-ZBR-ZD220',         'priority' => 'required',    'sort_order' => 2, 'notes' => 'Печать складских этикеток'],
                ['sku' => 'LBL-TSC-244PRO',        'priority' => 'recommended', 'sort_order' => 3, 'notes' => 'Высокопроизводительный принтер этикеток'],
                ['sku' => 'SCALE-MERTECH-326AC',   'priority' => 'recommended', 'sort_order' => 4, 'notes' => 'Весы для приёмки товара'],
                ['sku' => 'ACC-LBL-58X40-1000',   'priority' => 'optional',    'sort_order' => 5, 'notes' => 'Этикетки для стеллажей и товара'],
            ],
        ];

        $inserted = 0;
        $skipped = 0;

        foreach ($map as $typeSlug => $items) {
            $businessType = BusinessType::where('slug', $typeSlug)->first();

            if (! $businessType) {
                $this->command->warn("BusinessType not found: {$typeSlug} — skipped");
                continue;
            }

            foreach ($items as $item) {
                $product = Product::where('sku', $item['sku'])->first();

                if (! $product) {
                    $this->command->warn("Product not found: {$item['sku']} — skipped");
                    $skipped++;
                    continue;
                }

                DB::table('business_type_recommendations')->insertOrIgnore([
                    'business_type_id' => $businessType->id,
                    'product_id'       => $product->id,
                    'priority'         => $item['priority'],
                    'sort_order'       => $item['sort_order'],
                    'notes'            => $item['notes'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $inserted++;
            }
        }

        $this->command->info("✓ BusinessTypeRecommendations: {$inserted} inserted, {$skipped} skipped");
    }
}
