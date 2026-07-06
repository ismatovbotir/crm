# UI Components — RSG-CRM Admin

Список переиспользуемых компонентов для админ-панели. Все компоненты — Blade-партиалы или Livewire-компоненты.

## 📦 Каталог компонентов

### Layout

| Компонент | Путь | Описание |
|-----------|------|----------|
| `admin.layout` | `layouts/admin.blade.php` | Базовый layout с сайдбаром + хедером |
| `admin.partials.sidebar` | `admin/partials/sidebar.blade.php` | Левая навигация |
| `admin.partials.header` | `admin/partials/header.blade.php` | Верхний бар |
| `admin.partials.page-header` | `admin/partials/page-header.blade.php` | Заголовок страницы |

### Базовые UI элементы

| Компонент | Слот / Props | Использование |
|-----------|--------------|---------------|
| `<x-card>` | slot | Карточка с padding и shadow |
| `<x-button>` | variant=primary\|secondary\|danger | Кнопка |
| `<x-badge>` | variant=success\|warning\|danger\|info\|gray | Бейдж статуса |
| `<x-input>` | name, label, type, placeholder | Текстовое поле с label |
| `<x-select>` | name, options, selected | Выпадающий список |
| `<x-textarea>` | name, rows | Многострочный ввод |
| `<x-checkbox>` | name, label | Чекбокс |
| `<x-icon name="...">` | name (heroicons) | SVG-иконка |
| `<x-avatar>` | src, name, size | Аватар пользователя |
| `<x-modal>` | id | Модальное окно |
| `<x-dropdown>` | trigger, items | Выпадающее меню |
| `<x-tab-group>` | tabs slot | Вкладки |

### Data Display

| Компонент | Описание |
|-----------|----------|
| `<x-table>` | Стилизованная таблица с зеброй и hover |
| `<x-table.row>` | Строка таблицы |
| `<x-table.cell>` | Ячейка |
| `<x-empty-state>` | "Нет данных" с иконкой и кнопкой |
| `<x-pagination>` | Постраничная навигация (используется в Livewire) |
| `<x-stat-card>` | KPI карточка (число + тренд) |

### Feedback

| Компонент | Описание |
|-----------|----------|
| `<x-alert>` | Уведомление (success/warning/danger/info) |
| `<x-toast>` | Тост (auto-dismiss, появляется внизу справа) |
| `<x-loader>` | Spinner для loading-состояний |
| `<x-skeleton>` | Скелетон для placeholder при загрузке |

### Forms

| Компонент | Описание |
|-----------|----------|
| `<x-form-section>` | Секция формы с заголовком + описанием |
| `<x-form-actions>` | Контейнер для кнопок submit/cancel |
| `<x-search-input>` | Поле поиска с иконкой |
| `<x-date-picker>` | Выбор даты (через flatpickr) |
| `<x-money-input>` | Поле для ввода суммы с валютой |

## 🎯 Принципы

1. **Один компонент = одна ответственность** — не пихаем логику в UI
2. **Tailwind в классах** — никаких отдельных CSS-файлов кроме базовых
3. **Slot-first** — переиспользуемость через слоты Blade
4. **Привязка через Alpine** — для простой интерактивности (dropdown, toggle)
5. **Livewire для данных** — для списков, форм и фильтров

## 🔧 Пример: KPI карточка

```blade
{{-- resources/views/components/stat-card.blade.php --}}
@props(['label', 'value', 'trend' => null, 'icon' => null, 'color' => 'primary'])

<div class="bg-white rounded-lg shadow-sm p-6 flex items-center gap-4">
    @if($icon)
        <div class="bg-{{ $color }}-100 text-{{ $color }}-600 p-3 rounded-full">
            <x-icon :name="$icon" class="w-6 h-6" />
        </div>
    @endif
    <div class="flex-1">
        <p class="text-sm text-gray-500">{{ $label }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
        @if($trend !== null)
            <p class="text-sm {{ $trend >= 0 ? 'text-success-500' : 'text-danger-500' }}">
                {{ $trend >= 0 ? '▲' : '▼' }} {{ abs($trend) }}%
            </p>
        @endif
    </div>
</div>
```

Использование:
```blade
<x-stat-card label="Новые лиды" value="42" :trend="12" icon="user-plus" color="primary" />
```
