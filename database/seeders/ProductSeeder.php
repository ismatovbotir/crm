<?php

namespace Database\Seeders;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductPrice;
use App\Models\Catalog\ProductStock;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cats = Category::pluck('id', 'name_ru');

        $products = [
            // ── POS-системы ──────────────────────────────────────────────────
            [
                'category' => 'POS-системы',
                'name_ru'  => 'POS-терминал Sunmi T2 Lite',
                'name_uz'  => 'POS-terminal Sunmi T2 Lite',
                'sku'      => 'POS-SUNMI-T2L',
                'brand'    => 'Sunmi',
                'model_number' => 'T2 Lite',
                'unit'     => 'шт',
                'description_ru' => 'Сенсорный POS-терминал с 15.6" дисплеем, Android 7.1, встроенный принтер чеков 80 мм, поддержка WiFi и Ethernet.',
                'is_visible_portal' => true,
                'retail'   => 4_500_000, 'cost' => 3_200_000, 'currency' => 'UZS',
                'qty' => 8, 'reserved' => 2,
            ],
            [
                'category' => 'POS-системы',
                'name_ru'  => 'POS-терминал Posiflex RT-5015',
                'name_uz'  => 'POS-terminal Posiflex RT-5015',
                'sku'      => 'POS-POSIFLEX-RT5015',
                'brand'    => 'Posiflex',
                'model_number' => 'RT-5015',
                'unit'     => 'шт',
                'description_ru' => 'Настольный POS-терминал с 15" резистивным экраном, Intel Celeron, Windows 10 IoT. Надёжное решение для ритейла.',
                'is_visible_portal' => true,
                'retail'   => 6_800_000, 'cost' => 5_100_000, 'currency' => 'UZS',
                'qty' => 5, 'reserved' => 1,
            ],
            [
                'category' => 'POS-системы',
                'name_ru'  => 'Мобильный POS Sunmi P2',
                'name_uz'  => 'Mobil POS Sunmi P2',
                'sku'      => 'POS-SUNMI-P2',
                'brand'    => 'Sunmi',
                'model_number' => 'P2',
                'unit'     => 'шт',
                'description_ru' => 'Портативный Android POS-терминал с NFC, встроенным сканером и принтером. Идеален для доставки и выездной торговли.',
                'is_visible_portal' => true,
                'retail'   => 3_200_000, 'cost' => 2_400_000, 'currency' => 'UZS',
                'qty' => 12, 'reserved' => 3,
            ],

            // ── Кассовые аппараты ─────────────────────────────────────────────
            [
                'category' => 'Кассовые аппараты',
                'name_ru'  => 'Фискальный регистратор АТОЛ 91Ф',
                'name_uz'  => 'Fiskal registrator ATOL 91F',
                'sku'      => 'KKT-ATOL-91F',
                'brand'    => 'АТОЛ',
                'model_number' => '91Ф',
                'unit'     => 'шт',
                'description_ru' => 'Компактный фискальный регистратор со скоростью печати 75 мм/с. Поддержка OFD, WiFi, Bluetooth.',
                'is_visible_portal' => true,
                'retail'   => 1_900_000, 'cost' => 1_350_000, 'currency' => 'UZS',
                'qty' => 15, 'reserved' => 4,
            ],
            [
                'category' => 'Кассовые аппараты',
                'name_ru'  => 'ККТ Datecs FP-700',
                'name_uz'  => 'KKT Datecs FP-700',
                'sku'      => 'KKT-DATECS-FP700',
                'brand'    => 'Datecs',
                'model_number' => 'FP-700',
                'unit'     => 'шт',
                'description_ru' => 'Фискальный принтер с GPRS/WiFi модулем. Встроенная аккумуляторная батарея, автоотрез бумаги.',
                'is_visible_portal' => false,
                'retail'   => 2_400_000, 'cost' => 1_800_000, 'currency' => 'UZS',
                'qty' => 7, 'reserved' => 0,
            ],

            // ── Весы ──────────────────────────────────────────────────────────
            [
                'category' => 'Весы',
                'name_ru'  => 'Весы торговые Mertech M-ER 326 AC',
                'name_uz'  => 'Savdo tarozi Mertech M-ER 326 AC',
                'sku'      => 'SCALE-MERTECH-326AC',
                'brand'    => 'Mertech',
                'model_number' => 'M-ER 326 AC',
                'unit'     => 'шт',
                'description_ru' => 'Напольные торговые весы до 15 кг, дискретность 5 г. Подключение к кассе, встроенный принтер этикеток.',
                'is_visible_portal' => true,
                'retail'   => 1_200_000, 'cost' => 850_000, 'currency' => 'UZS',
                'qty' => 20, 'reserved' => 5,
            ],
            [
                'category' => 'Весы',
                'name_ru'  => 'Весы-этикетировщик CAS LP-1.5R',
                'name_uz'  => 'Tarozi-etiketlovchi CAS LP-1.5R',
                'sku'      => 'SCALE-CAS-LP15R',
                'brand'    => 'CAS',
                'model_number' => 'LP-1.5R',
                'unit'     => 'шт',
                'description_ru' => 'Весы с принтером этикеток для супермаркетов. До 15 кг, Wi-Fi, сенсорный экран, 1500 PLU.',
                'is_visible_portal' => true,
                'retail'   => 2_800_000, 'cost' => 2_100_000, 'currency' => 'UZS',
                'qty' => 6, 'reserved' => 1,
            ],

            // ── Сканеры штрихкодов ────────────────────────────────────────────
            [
                'category' => 'Сканеры штрихкодов',
                'name_ru'  => 'Сканер Honeywell Voyager 1250g',
                'name_uz'  => 'Skaner Honeywell Voyager 1250g',
                'sku'      => 'SCN-HW-1250G',
                'brand'    => 'Honeywell',
                'model_number' => 'Voyager 1250g',
                'unit'     => 'шт',
                'description_ru' => 'Ручной одномерный лазерный сканер. Дальность считывания до 30 см, интерфейс USB/RS-232.',
                'is_visible_portal' => true,
                'retail'   => 380_000, 'cost' => 260_000, 'currency' => 'UZS',
                'qty' => 35, 'reserved' => 8,
            ],
            [
                'category' => 'Сканеры штрихкодов',
                'name_ru'  => '2D-сканер Zebra DS2208',
                'name_uz'  => '2D-skaner Zebra DS2208',
                'sku'      => 'SCN-ZBR-DS2208',
                'brand'    => 'Zebra',
                'model_number' => 'DS2208',
                'unit'     => 'шт',
                'description_ru' => 'Имиджевый 2D-сканер для считывания QR-кодов, PDF417, DataMatrix. USB HID.',
                'is_visible_portal' => true,
                'retail'   => 560_000, 'cost' => 390_000, 'currency' => 'UZS',
                'qty' => 18, 'reserved' => 2,
            ],
            [
                'category' => 'Сканеры штрихкодов',
                'name_ru'  => 'Беспроводной сканер Honeywell 1202g',
                'name_uz'  => 'Simsiz skaner Honeywell 1202g',
                'sku'      => 'SCN-HW-1202G',
                'brand'    => 'Honeywell',
                'model_number' => '1202g Stratos',
                'unit'     => 'шт',
                'description_ru' => 'Беспроводной Bluetooth-сканер с дальностью связи до 10 м. Аккумулятор на 14 часов работы.',
                'is_visible_portal' => true,
                'retail'   => 720_000, 'cost' => 510_000, 'currency' => 'UZS',
                'qty' => 10, 'reserved' => 3,
            ],

            // ── Принтеры этикеток ─────────────────────────────────────────────
            [
                'category' => 'Принтеры этикеток',
                'name_ru'  => 'Принтер этикеток Zebra ZD220',
                'name_uz'  => 'Etiket printeri Zebra ZD220',
                'sku'      => 'LBL-ZBR-ZD220',
                'brand'    => 'Zebra',
                'model_number' => 'ZD220',
                'unit'     => 'шт',
                'description_ru' => 'Настольный термопринтер этикеток 203 dpi, ширина печати 104 мм, USB. Подходит для склада и ритейла.',
                'is_visible_portal' => true,
                'retail'   => 1_650_000, 'cost' => 1_200_000, 'currency' => 'UZS',
                'qty' => 9, 'reserved' => 2,
            ],
            [
                'category' => 'Принтеры этикеток',
                'name_ru'  => 'Принтер этикеток TSC TTP-244 Pro',
                'name_uz'  => 'Etiket printeri TSC TTP-244 Pro',
                'sku'      => 'LBL-TSC-244PRO',
                'brand'    => 'TSC',
                'model_number' => 'TTP-244 Pro',
                'unit'     => 'шт',
                'description_ru' => 'Термотрансферный принтер этикеток 203 dpi, USB + Ethernet. Скорость печати до 127 мм/с.',
                'is_visible_portal' => true,
                'retail'   => 2_100_000, 'cost' => 1_550_000, 'currency' => 'UZS',
                'qty' => 4, 'reserved' => 0,
            ],

            // ── Принтеры чеков ────────────────────────────────────────────────
            [
                'category' => 'Принтеры чеков',
                'name_ru'  => 'Принтер чеков Epson TM-T20III',
                'name_uz'  => 'Chek printeri Epson TM-T20III',
                'sku'      => 'RCP-EPS-TMT20III',
                'brand'    => 'Epson',
                'model_number' => 'TM-T20III',
                'unit'     => 'шт',
                'description_ru' => 'Термопринтер чеков 80 мм, скорость 250 мм/с, автоотрез, USB + Serial.',
                'is_visible_portal' => true,
                'retail'   => 960_000, 'cost' => 700_000, 'currency' => 'UZS',
                'qty' => 22, 'reserved' => 6,
            ],
            [
                'category' => 'Принтеры чеков',
                'name_ru'  => 'Принтер чеков Citizen CT-S310II',
                'name_uz'  => 'Chek printeri Citizen CT-S310II',
                'sku'      => 'RCP-CTZ-CTS310II',
                'brand'    => 'Citizen',
                'model_number' => 'CT-S310II',
                'unit'     => 'шт',
                'description_ru' => 'Компактный чековый принтер 80 мм с USB, RS-232 и LAN. Скорость 250 мм/с, ресурс 150 км ленты.',
                'is_visible_portal' => false,
                'retail'   => 1_100_000, 'cost' => 820_000, 'currency' => 'UZS',
                'qty' => 11, 'reserved' => 2,
            ],

            // ── Денежные ящики ────────────────────────────────────────────────
            [
                'category' => 'Денежные ящики',
                'name_ru'  => 'Денежный ящик АТОЛ CD-57',
                'name_uz'  => "Pul qutisi ATOL CD-57",
                'sku'      => 'CASH-ATOL-CD57',
                'brand'    => 'АТОЛ',
                'model_number' => 'CD-57',
                'unit'     => 'шт',
                'description_ru' => 'Металлический денежный ящик 460×170×100 мм. 4 отсека для купюр, 8 монетниц. Подключение RJ-11.',
                'is_visible_portal' => true,
                'retail'   => 320_000, 'cost' => 210_000, 'currency' => 'UZS',
                'qty' => 30, 'reserved' => 5,
            ],

            // ── Дисплеи покупателя ────────────────────────────────────────────
            [
                'category' => 'Дисплеи покупателя',
                'name_ru'  => 'Дисплей покупателя Posiflex PD-3000',
                'name_uz'  => 'Xaridor displeyi Posiflex PD-3000',
                'sku'      => 'DSP-POSIFLEX-PD3000',
                'brand'    => 'Posiflex',
                'model_number' => 'PD-3000',
                'unit'     => 'шт',
                'description_ru' => 'Светодиодный дисплей покупателя 2×20 символов, подключение USB, поворотная стойка.',
                'is_visible_portal' => true,
                'retail'   => 450_000, 'cost' => 310_000, 'currency' => 'UZS',
                'qty' => 14, 'reserved' => 3,
            ],
            [
                'category' => 'Дисплеи покупателя',
                'name_ru'  => 'Монитор покупателя 10" Sunmi DS026',
                'name_uz'  => '10" xaridor monitoru Sunmi DS026',
                'sku'      => 'DSP-SUNMI-DS026',
                'brand'    => 'Sunmi',
                'model_number' => 'DS026',
                'unit'     => 'шт',
                'description_ru' => 'Сенсорный 10.1" дисплей покупателя с возможностью показа рекламы. Full HD, USB-C.',
                'is_visible_portal' => true,
                'retail'   => 890_000, 'cost' => 650_000, 'currency' => 'UZS',
                'qty' => 6, 'reserved' => 1,
            ],

            // ── Аксессуары ────────────────────────────────────────────────────
            [
                'category' => 'Аксессуары',
                'name_ru'  => 'Термобумага 80×80 (10 рулонов)',
                'name_uz'  => 'Termokogoz 80×80 (10 rulon)',
                'sku'      => 'ACC-PAPER-80X80-10',
                'brand'    => 'Noname',
                'model_number' => null,
                'unit'     => 'уп.',
                'description_ru' => 'Термобумага 80 мм для чековых принтеров. Ширина 80 мм, диаметр 80 мм. Упаковка 10 рулонов.',
                'is_visible_portal' => true,
                'retail'   => 85_000, 'cost' => 55_000, 'currency' => 'UZS',
                'qty' => 100, 'reserved' => 15,
            ],
            [
                'category' => 'Аксессуары',
                'name_ru'  => 'Кабель USB-A — RJ-11 (для ящика)',
                'name_uz'  => 'USB-A — RJ-11 kabeli (quti uchun)',
                'sku'      => 'ACC-CBL-USB-RJ11',
                'brand'    => 'Noname',
                'model_number' => null,
                'unit'     => 'шт',
                'description_ru' => 'Кабель для подключения денежного ящика к принтеру чеков через порт RJ-11. Длина 2 м.',
                'is_visible_portal' => true,
                'retail'   => 45_000, 'cost' => 28_000, 'currency' => 'UZS',
                'qty' => 50, 'reserved' => 0,
            ],
            [
                'category' => 'Аксессуары',
                'name_ru'  => 'Этикетки 58×40 мм (1000 шт)',
                'name_uz'  => "Etiketlar 58×40 mm (1000 dona)",
                'sku'      => 'ACC-LBL-58X40-1000',
                'brand'    => 'Noname',
                'model_number' => null,
                'unit'     => 'рул.',
                'description_ru' => 'Самоклеящиеся этикетки для принтеров этикеток. Размер 58×40 мм, 1000 шт на рулоне, втулка 25 мм.',
                'is_visible_portal' => true,
                'retail'   => 68_000, 'cost' => 42_000, 'currency' => 'UZS',
                'qty' => 80, 'reserved' => 10,
            ],
        ];

        $created = 0;

        foreach ($products as $data) {
            $categoryId = $cats[$data['category']] ?? null;

            if (! $categoryId) {
                $this->command->warn("Category not found: {$data['category']}");
                continue;
            }

            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id'       => $categoryId,
                    'name_ru'           => $data['name_ru'],
                    'name_uz'           => $data['name_uz'],
                    'brand'             => $data['brand'],
                    'model_number'      => $data['model_number'],
                    'unit'              => $data['unit'],
                    'description_ru'    => $data['description_ru'],
                    'is_active'         => true,
                    'is_visible_portal' => $data['is_visible_portal'],
                ]
            );

            if ($product->wasRecentlyCreated) {
                // Розничная цена
                ProductPrice::create([
                    'product_id' => $product->id,
                    'type'       => 'retail',
                    'amount'     => $data['retail'],
                    'currency'   => $data['currency'],
                    'is_active'  => true,
                ]);

                // Себестоимость
                ProductPrice::create([
                    'product_id' => $product->id,
                    'type'       => 'cost',
                    'amount'     => $data['cost'],
                    'currency'   => $data['currency'],
                    'is_active'  => true,
                ]);

                // Остатки
                ProductStock::create([
                    'product_id' => $product->id,
                    'quantity'   => $data['qty'],
                    'reserved'   => $data['reserved'],
                    'warehouse'  => 'Основной склад',
                ]);

                $created++;
            }
        }

        $this->command->info("✓ Products: {$created} created (" . count($products) . ' total defined)');
    }
}
