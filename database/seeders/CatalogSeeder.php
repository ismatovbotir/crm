<?php

namespace Database\Seeders;

use App\Models\Catalog\Category;
use App\Models\Catalog\ProductGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $groups = ProductGroup::pluck('id', 'name_ru');

        $pos  = $groups['POS-оборудование']    ?? null;
        $torg = $groups['Торговое оборудование'] ?? null;
        $mat  = $groups['POS-материалы']         ?? null;
        $ras  = $groups['Расходные материалы']   ?? null;

        $categories = [
            // POS-оборудование
            ['name_ru' => 'POS-системы',          'name_uz' => 'POS-tizimlar',           'icon' => '🖥️', 'group_id' => $pos],
            ['name_ru' => 'Кассовые аппараты',    'name_uz' => 'Kassa apparatlari',      'icon' => '🧾', 'group_id' => $pos],
            ['name_ru' => 'Сканеры штрихкодов',   'name_uz' => 'Shtrix-kod skanerlar',   'icon' => '📷', 'group_id' => $pos],
            ['name_ru' => 'Принтеры чеков',       'name_uz' => 'Chek printerlari',       'icon' => '🖨️', 'group_id' => $pos],
            ['name_ru' => 'Денежные ящики',       'name_uz' => 'Pul qutilari',           'icon' => '💰', 'group_id' => $pos],
            ['name_ru' => 'Дисплеи покупателя',   'name_uz' => 'Xaridor displaylari',    'icon' => '📟', 'group_id' => $pos],

            // Торговое оборудование
            ['name_ru' => 'Весы',                 'name_uz' => 'Tarozilar',              'icon' => '⚖️', 'group_id' => $torg],
            ['name_ru' => 'Холодильное оборудование', 'name_uz' => 'Sovutish uskunalari','icon' => '❄️', 'group_id' => $torg],
            ['name_ru' => 'Стеллажи и витрины',   'name_uz' => 'Javon va vitrinalar',    'icon' => '🗄️', 'group_id' => $torg],

            // POS-материалы
            ['name_ru' => 'Принтеры этикеток',    'name_uz' => 'Etiket printerlari',     'icon' => '🏷️', 'group_id' => $mat],
            ['name_ru' => 'Рамки и ценникодержатели', 'name_uz' => 'Narx teglar',        'icon' => '🔖', 'group_id' => $mat],
            ['name_ru' => 'Аксессуары',           'name_uz' => 'Aksessuarlar',           'icon' => '🔌', 'group_id' => $mat],

            // Расходные материалы
            ['name_ru' => 'Бумага и чековая лента', 'name_uz' => "Qog'oz va chek lenta", 'icon' => '📜', 'group_id' => $ras],
            ['name_ru' => 'Этикетки',             'name_uz' => 'Etiketlar',              'icon' => '🏷️', 'group_id' => $ras],
            ['name_ru' => 'Красящие ленты',       'name_uz' => 'Rəng tasmalari',         'icon' => '🖊️', 'group_id' => $ras],
        ];

        foreach ($categories as $i => $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name_ru'])],
                array_merge($cat, ['sort_order' => $i, 'is_active' => true])
            );
        }

        $this->command->info('✓ Categories: ' . count($categories));
    }
}
