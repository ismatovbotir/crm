> ⚠️ АРХИВ (2026-07-06): stream-based workflow заменён на ролевую модель PM + субагенты. См. `CLAUDE.md` → "Часть 1.5. Архитектура агентов" и `.claude/agents/`. Этот файл сохранён только для истории.

# Design Stream — STATUS

**Last updated**: 2026-05-11
**Phase**: Phase 10 (Documents unified form + Quote edit redesign)

## ✅ Сделано

### Foundation
- Design system (цвета, типографика, отступы)
- Layout admin + portal (`resources/views/layouts/`)
- Tailwind config с фирменной палитрой (primary blue)
- Permission-aware sidebar

### x-Компоненты (`resources/views/components/`)
- `<x-button>`, `<x-card>`, `<x-input>`, `<x-select>`, `<x-badge>`
- `<x-modal>` — модальное окно (Alpine + Livewire events)
- `<x-slide-over>` — боковая панель
- `<x-lead-status-badge>`, `<x-quote-status-badge>`, `<x-invoice-status-badge>`, `<x-ticket-status-badge>`

### Blade-страницы (CRM)
- `livewire/admin/leads/` — index (таблица + канбан), create-form, edit-form, show
- `livewire/admin/customers/` — index, create-form, edit-form, show
- `livewire/admin/quotes/` — index, create-form, show (с PDF-кнопками)
- `livewire/admin/invoices/` — index, show (с PDF, частичной оплатой)
- `livewire/admin/tickets/` — index, create-form, show (внутренние/публичные комменты)
- `livewire/admin/catalog/` — categories index/create/edit, products index/create/edit/show/serials
- `livewire/admin/dashboard.blade.php` — KPI + Chart.js
- `pdf/quote.blade.php`, `pdf/invoice.blade.php`

### Blade-страницы (Portal)
- `portal/partials/sidebar.blade.php`, `header.blade.php`
- Portal: dashboard, quotes (index+show), invoices (index+show), tickets (index+show+create), catalog, profile

### UI Polish (Phase 8 / 2026-05-05)
- `<x-modal>` и `<x-slide-over>`: кнопки Cancel+Save перенесены в хедер справа, X кнопка убрана
- Leads kanban: исправлен горизонтальный скролл, добавлена max-height для столбцов
- Leads kanban: кнопка-переключатель исправлена (type="button")
- Leads таблица: кнопка удаления убрана, edit открывает модал через `@entangle`

### Product Returns Module UI (2026-05-09)
- `returns/index`: paginated table, search + status filter, reason labels, status badges
- `returns/create-form`: full-page — customer/sell selectors, conditional lines table with serial input, reason/amount/notes, breadcrumb
- `returns/show`: two-column layout, action buttons per status (approve / markRefunded / cancel), links card for sell/invoice/ticket
- `invoices/show`: "Возврат" button added to header (conditional on sells present)
- `tickets/show`: "Оформить возврат" button added to header actions
- `catalog/products/serials`: all `status` references updated to `current_status`; filter + badge updated for `returned` and `in_repair` statuses

### Catalog CRUD UI + Serials (2026-05-09)
- `products/index`: кнопка "+ Создать товар" + slide-over `@if($showCreate)`
- `products/show`: кнопка "Редактировать" + slide-over `@if($showEdit)`, динамические вкладки с условным `serials`, вложенный компонент `serials`
- `products/create-form`: чекбокс `is_serial`
- `products/edit-form`: `id="product-edit-form"`, чекбокс `is_serial`, удалены дублирующие кнопки Cancel/Save
- `products/serials`: новый вид — toolbar, форма добавления, таблица с пагинацией, импорт CSV

### Serial number tracking UI (2026-05-09)
- `invoices/show`: кол-ячейка в shipment modal стала условной — для `is_serial` строк показывает счётчик выбранных + кнопку "Выбрать серии" (wire:click="openSerialsModal"); для обычных — прежнее поле ввода
- `invoices/show`: добавлен serials picker modal (`z-[60]`) с чекбоксами, счётчиком и кнопкой "Готово"
- `admin/tickets/create-form`: поле `serial_number` после Темы (перед Описанием)
- `portal/tickets/create-form`: поле `serial_number` после Темы (перед Описанием)
- `catalog/products/serials`: колонка "Клиент / Продажа" — показывает `customer->name` и `sellItem->sell->number` для проданных серийников

### Portal "Мои устройства" page (2026-05-09)
- `livewire/portal/equipment/index.blade.php`: device grid, add-form with serial lookup + external fields, history slide-over with status timeline and linked tickets
- `portal/partials/sidebar.blade.php`: "Мои устройства" nav item added (between Тикеты and Каталог), `device` icon entry added to `$icons`
- `livewire/portal/tickets/create-form.blade.php`: Alpine `x-init` pre-fills `serial_number` + triggers `lookupSerial()` from `?serial_number=` query param

### Catalog Groups & Recommendations pages (2026-05-10)
- `catalog/groups/index`: table with inline edit rows, color-picker radio circles, toggle-active button, create slide-over
- `catalog/recommendations/index`: two-column layout, Alpine.js product search dropdown, recommendations grouped by priority with inline priority-change select

### Documents unified form + Quote edit redesign (2026-05-11)
- `livewire/admin/documents/create-form.blade.php` — новый единый blade для КП и Инвойса; 3-зонный sticky layout (header flex-shrink-0 / items flex-1 min-h-0 overflow-y-auto / footer flex-shrink-0); шапка: клиент+даты / валюта+глобальная скидка+поиск; товары: таблица с двухстрочными ячейками Цена (прайс/фин) и Итого (прайс/фин); футер: строка 1 — итоги справа, строка 2 — условия + примечания
- `livewire/admin/quotes/edit-form.blade.php` — полная переработка под тот же 3-зонный layout; баннер предупреждения для sent/viewed; рекомендации; кнопка "Редактировать" в show.blade.php скрыта если есть инвойс
- `admin/quotes/edit.blade.php` — карточка с `height: calc(100vh - 9rem)` для sticky-layout
- Итого-колонка таблицы: убрана красная строка суммы скидки (оставлено только 2 строки: прайс и фин.итого)

### Quotes create-form enhancements (2026-05-10)
- Recommendations panel: shown after the Client section when `$recommendations` is non-empty; priority badge (red "обяз" / blue "рек"), group_name subtitle, Alpine "Added" toggle via `$wire.items`
- Items table: group badge rendered server-side via `@php collect($productsList)->firstWhere()` with 4-color match; badge appears below SKU in each row
- Search dropdown: `p.group_name` line added under SKU in each dropdown result row

### External equipment registration + serial history UI (2026-05-09)
- `admin/tickets/create-form`: serial lookup block — inline input + "Найти" button, success banner (`foundSerial`), external form panel (`showExternalForm`) with ext_brand/ext_model inputs
- `portal/tickets/create-form`: same pattern — "Проверить" button, success/external panels
- `catalog/products/serials`: history icon button per row (clock SVG, opens slide-over); history slide-over — device info card, timeline of status changes with badges, linked tickets list
- `admin/tickets/show`: "Устройство" card in right sidebar — serial number, display_name, external badge, status badge, mini status history (last 5 entries)

## 🔄 В работе

— нет (все экраны реализованы)

## ⏭ Следующее (опционально)

- Страницы Reports, Settings UI
- Responsive доработки (мобильный вид)
- Skeleton-loaders и lazy loading

## 📝 Рекомендация для следующей сессии

Все ключевые экраны готовы. При изменении — смотри `resources/views/components/` для переиспользуемых элементов.
