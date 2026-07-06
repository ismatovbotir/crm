# Changelog — Общий журнал изменений

Append-only журнал. Каждое значимое изменение → запись здесь.

> ⚠️ С 2026-07-06 проект перешёл со stream-based workflow (design/database/backend) на ролевую модель: PM-агент (`pm-b2b-crm`) + 5 субагентов (`laravel-fullstack`, `admin-bi-developer`, `ticket-system`, `swagger-docs`, `qa-tester`). См. `CLAUDE.md` → "Часть 1.5. Архитектура агентов". Записи до этой даты используют старый тег `[stream]` (design/database/backend) — они остаются как есть, история не переписывается.

**Формат записи (с 2026-07-06)**:
```
## YYYY-MM-DD — [agent-name] Краткое описание
- Что сделано (буллеты)
- Влияние на других агентов / модули (если есть)
```

При старте новой задачи — пробежать **последние 10 записей**, чтобы понять контекст.

---

## 2026-07-06 — [laravel-fullstack] Удаление legacy-миграций + полный прогон migrate:fresh/seed + восстановление config/permissions.php
- Удалены (одобрено пользователем явно) 3 legacy-дубля миграций: `2014_10_12_000000_create_users_table.php`, `2014_10_12_100000_create_password_reset_tokens_table.php`, `2019_08_19_000000_create_failed_jobs_table.php` — эти таблицы уже создаются современными консолидированными `0001_01_01_*` миграциями (Laravel 11+ stubs); дубли ломали `migrate:fresh`. Коммит `95b890f`, содержит только эти 3 удаления.
- **Обнаружен и устранён критичный пробел**: `config/permissions.php` — файл, документированный в `CLAUDE.md` и changelog (запись от 2026-04-28 "config/permissions.php — единая карта ролей и прав") как уже существующий, **физически отсутствовал в репозитории** (не найден ни в рабочем дереве, ни в истории git — судя по всему, не был закоммичен на Phase 1). Из-за этого `RolesSeeder` и `App\Livewire\Admin\Setup` создавали 0 permissions/ролей.
- Восстановлен `config/permissions.php` реконструкцией из фактически используемых в коде permission-строк (`grep` по `->can(...)`, `Acl::can(...)`, `@acl(...)`, `Policy`-классам, `sidebar.blade.php`) + сверкой с картой ролей из `CLAUDE.md` §2.4. Итог: 40 permissions (11 групп: leads, customers, quotes, invoices, sells, returns, catalog, tickets, equipment_requests, reports, settings), 8 ролей (`super-admin` = все permissions, `sales-director`, `sales-manager`, `tech-support`, `catalog-manager`, `accountant`, `client-admin`/`client-user` без module-permissions — портал использует ownership-check в Livewire, не Spatie permissions).
- `php artisan migrate:fresh` — все 44 миграции прошли чисто (`Ran`).
- `php artisan db:seed --class=RolesSeeder` — 8 ролей / 40 permissions созданы. Полный `php artisan db:seed` — все core-сидеры (BusinessTypes, Banks, LeadSources, ProductGroups, Catalog, Product, BusinessTypeRecommendations, TicketCategories, DemoUsers) прошли успешно; `DemoLeadsSeeder` **упал** с `Class "Database\Factories\CustomerFactory" not found` — фабрика для `Customer` отсутствует в `database/factories/` (найден только `UserFactory.php`). Это pre-existing пробел, не связан с задачей миграций/ролей, вне текущего скоупа — нужен `db-architect`/`laravel-fullstack` фикс отдельной задачей (создать `CustomerFactory`), иначе демо-сидер `Customers`/`Leads` не работает в `local`/`development`.
- `php artisan test`: 1 из 2 тестов красный — `ExampleTest::test_the_application_returns_a_successful_response` ожидает 200 от `/`, получает 302 (роут `/` делает `redirect('/admin')`, это уже так в `routes/web.php`, не мной введено). Не блокирующая, pre-existing; `qa-tester` в курсе.
- **Влияние**: любой поток/агент, который читает `config/permissions.php` (сайдбар, Setup-компонент, RolesSeeder) — теперь получает реальные данные, а не пустой массив. Роли/права в БД актуальны и соответствуют `CLAUDE.md` §2.4 + фактическому использованию permission-строк в коде.
- **Открытый вопрос для координатора/db-architect**: нужно создать `database/factories/CustomerFactory.php`, иначе `DemoLeadsSeeder` (и любой другой сидер/тест, использующий `Customer::factory()`) будет падать.

## 2026-07-06 — [claude-md-maintainer] Коррекция tech-stack секции (Laravel/PHP/Livewire версии)
- Часть 1.7 Roadmap, Phase 0: `Laravel scaffold (10.10 + Livewire 4.2 + Sanctum)` → `Laravel scaffold (13.18 + Livewire 4.3 + Sanctum пакет установлен)`
- Часть 2.1 Project Overview: `Laravel 10 application` → `Laravel 13 application`; `Backend: Laravel 10.10 + Livewire 4.2` → `Laravel 13.18 + Livewire 4.3`; `PHP: 8.1+` → `PHP: 8.4+`
- Строка про API/Sanctum уточнена: пакет `laravel/sanctum` установлен, но `routes/api.php` пустой — API-контроллеров/эндпоинтов нет (было утверждение "API: Laravel Sanctum (token-based auth)" как рабочей фичи)
- Версии проверены напрямую по `composer.json`/`composer.lock`: `laravel/framework` v13.18.1, `livewire/livewire` v4.3.3, `spatie/laravel-permission` 8.3.0 (установлен, но явно не указан отдельной строкой в `require` — подтверждено в `composer.lock`), PHP-констрейнт `^8.4`
- **Не изменено** (вне скоупа задачи): §2.4 "Authentication" всё ещё описывает Sanctum как рабочий механизм для SPA/API — фактически пакет установлен, но не подключён (нет `Api`-контроллеров); требует отдельной правки, если будет запрошено

## 2026-05-11 — [design] Quote edit-form redesign + documents create-form polish
- `livewire/admin/quotes/edit-form.blade.php` — полная переработка: 3-зонный layout (header/items scroll/footer), те же компоненты что в create-form, баннер предупреждения для sent/viewed статусов
- `livewire/admin/documents/create-form.blade.php` — футер разделён на 2 строки: строка 1 = итоги (w-72, right-aligned), строка 2 = условия + примечания; колонка Итого в таблице упрощена (убрана красная строка со скидкой)
- `admin/quotes/edit.blade.php` — карточке добавлена высота `calc(100vh - 9rem)` для sticky-layout внутри
- `livewire/admin/quotes/show.blade.php` — кнопка "Редактировать" скрыта когда `$quote->invoice` существует

## 2026-05-11 — [backend] Quote EditForm redesign + discount logic fix
- `app/Livewire/Admin/Quotes/EditForm.php` — полная переработка: global_discount_type/value вместо discount_percent/vat_percent; customer typeahead + recommendations; invoice-lock (abort 403 если invoice exists); item-структура: discount_type/value/final_price; все пересчёты из Documents\CreateForm
- `QuoteController::edit()` — добавлен `abort_if($quote->invoice()->exists(), 403)`
- `Documents\CreateForm` + `Quotes\EditForm` — добавлен `updatedGlobalDiscountType()`: сбрасывает value→0 при смене типа %; → Сум
- `documents/create-form.blade.php` + `quotes/edit-form.blade.php` — `wire:model.blur` вместо `live` на поле значения глобальной скидки (обновление только при покидании инпута)
- Логика скидки проверена: grossSubtotal → per-item скидки → subtotal → global discount → grandTotal; всё корректно

## 2026-05-11 — [backend] Documents\CreateForm unified quote/invoice component
- Created `app/Livewire/Admin/Documents/CreateForm.php` — unified form for КП and Инвойс creation, controlled by `$type = 'quote'|'invoice'` property
- Full line-item discount logic (percent/sum per line, final_price, recalculate helpers) carried over from Quotes\CreateForm
- Business-type recommendations loaded only when `$type === 'quote'`; guard at top of `loadRecommendations()` clears array for invoice type
- `rules()` is conditional: quote adds `valid_until` + `terms`; invoice adds `due_date`
- `save()` dispatches to `saveQuote()` or `saveInvoice()` (private methods); each re-authorizes before persisting
- `saveInvoice()` applies global discount to subtotal before computing invoice total (same discount model as quotes)
- `mount()` accepts `string $type` and `?int $customerId`; authorizes against correct model class at entry
- View target: `livewire.admin.documents.create-form` (design stream to create the Blade template)
- No existing files modified

## 2026-05-10 — [backend] Groups & Recommendations Livewire components
- Created `app/Livewire/Admin/Catalog/Groups/Index.php` — inline row editing, create slide-over, toggleActive; authorization via `ProductPolicy::create/update`
- Created `app/Livewire/Admin/Catalog/Recommendations/Index.php` — type selector, product autocomplete (>= 2 chars, excludes already added), addRecommendation/updatePriority/removeRecommendation; `selectProduct()` helper for Alpine dropdown
- Extended `app/Policies/ProductPolicy.php` — added `create()`, `update(?Product)`, `delete()` methods (catalog-manager + super-admin + explicit permission)
- Created stub view `resources/views/livewire/admin/catalog/recommendations/index.blade.php` — designer to implement
- Routes already exist in `web.php` (lines 66-67): `admin.catalog.groups.index`, `admin.catalog.recommendations.index`
- Product field name: `name_ru` (not `name`); `is_visible_portal` (not `is_visible_in_portal`)

## 2026-05-10 — [design] Groups & Recommendations catalog pages
- Created `resources/views/livewire/admin/catalog/groups/index.blade.php` — table with inline edit rows, color-picker radio circles, toggle-active button, create slide-over with color picker
- Created `resources/views/livewire/admin/catalog/recommendations/index.blade.php` — two-column layout (business-type list + recommendations panel), product search with Alpine.js dropdown, grouped list by priority (required/recommended/optional) with priority-change select and remove button

## 2026-05-10 — [database] Product groups & business-type recommendations
- Новая миграция `2026_04_28_139900_create_product_groups_table.php` — таблица product_groups (name_ru/uz, color, sort_order, is_active)
- Правка существующей миграции `2026_04_28_140000_create_categories_table.php` — добавлен `group_id` FK → product_groups (nullOnDelete) + индекс
- Новая миграция `2026_04_28_140150_create_business_type_recommendations_table.php` — приоритетные рекомендации товаров по типу бизнеса (required/recommended/optional), UNIQUE(business_type_id, product_id)
- Новая модель `App\Models\Catalog\ProductGroup` — fillable, casts, `categories()` HasMany, `scopeActive()`
- Новая модель `App\Models\Catalog\BusinessTypeRecommendation` — fillable, casts, `businessType()`, `product()` BelongsTo, `scopeRequired()`, `scopeRecommended()`
- Обновлена модель `App\Models\Catalog\Category` — добавлен group_id в fillable/casts, `group()` BelongsTo ProductGroup
- Обновлена модель `App\Models\BusinessType` — добавлен `recommendations()` HasMany BusinessTypeRecommendation
- Обновлена модель `App\Models\Catalog\Product` — добавлен `recommendations()` HasMany BusinessTypeRecommendation
- Новый сидер `ProductGroupsSeeder` — 4 группы (blue/green/orange/gray), updateOrCreate (идемпотентный)
- Новый сидер `BusinessTypeRecommendationsSeeder` — рекомендации для 6 типов бизнеса (shop/supermarket/restaurant/cafe/pharmacy/warehouse), graceful (пропускает отсутствующие SKU)
- Обновлён `DatabaseSeeder` — ProductGroupsSeeder перед CatalogSeeder, BusinessTypeRecommendationsSeeder после ProductSeeder
- Обновлён `data-contracts.md` — добавлены разделы product_groups и business_type_recommendations, обновлён раздел categories

## 2026-05-10 — [backend] Quotes CreateForm: business-type recommendations
- `Admin\Quotes\CreateForm`: новый `$recommendations[]` property
- `updatedCustomerId()`: Livewire lifecycle hook, загружает рекомендации `BusinessTypeRecommendation` для `business_type_id` клиента, сортирует required → recommended → optional, eager load `category.group` + `prices`
- `render()`: `$productsList` расширен полями `group_name` / `group_color` (через `category.group` eager load, safe `?->` chaining пока группы не смигрированы)
- Зависит от db-потока: нужны `ProductGroup`, `BusinessTypeRecommendation` модели и `group_id` FK в `categories`

## 2026-05-10 — [design] Quotes create-form: recommendations panel + group badges
- Added recommendations sidebar section after Client block (shown only when `$recommendations` non-empty); priority badge, group_name, Alpine "added" toggle via `$wire.items`
- Added server-side group color badge in items table rows (under SKU); uses `collect($productsList)->firstWhere()` + `match()` color map
- Added `p.group_name` line in search dropdown results

## 2026-05-09 — [design] Portal "Мои устройства" page
- Created `resources/views/livewire/portal/equipment/index.blade.php` — device grid with add-form (serial lookup + external fields panel), status badges, history slide-over (timeline + linked tickets)
- Updated `resources/views/portal/partials/sidebar.blade.php` — added "Мои устройства" nav item with device icon between Тикеты and Каталог
- Updated `resources/views/livewire/portal/tickets/create-form.blade.php` — Alpine x-init pre-fills serial_number + triggers lookupSerial() from `?serial_number=` URL param

## 2026-05-09 — [backend] Portal My Equipment section
- `app/Livewire/Portal/Equipment/Index.php` — new Livewire component for client portal "My Equipment" page
- Serial lookup flow: checks existing serials, ownership guard, external registration via `SerialService::registerExternal()`
- RSG serial claim: direct `current_owner_id` update + `SerialOwner` record creation
- History panel: `openHistory()` loads `statusHistory` + last 10 tickets, scoped by `current_owner_id` for security
- Route: `GET /portal/equipment` → `portal.equipment.index` added to portal middleware group

## 2026-05-09 — [design] External equipment registration + serial history UI

- `admin/tickets/create-form`: replaced simple serial input with interactive lookup block — row with text input (font-mono) + "Найти" button; success panel shown when `$foundSerial` is set (green bg, display_name + owner_name); external equipment form shown when `$showExternalForm` (warning bg, ext_brand/ext_model inputs in 2-col grid)
- `portal/tickets/create-form`: same pattern adapted for portal — "Проверить" button; external panel uses blue-50 scheme
- `catalog/products/serials`: history button (clock icon, primary hover) added to every row action cell alongside existing delete button; history slide-over appended before root closing `</div>` — fixed overlay, right-side panel with header (serial number mono), device info card with status badge, status timeline with vertical line + dot connectors, linked tickets list with chevron links
- `admin/tickets/show`: "Устройство" `<x-card>` inserted in right sidebar after "Детали" card, guarded by `@if($ticket->serial)`; shows serial_number (mono), display_name, external badge, current status badge, mini history list (last 5 entries with dot + label + date)

## 2026-05-09 — [backend] External equipment support + serial history UI

- `app/Services/SerialService.php`: added `registerExternal()` — creates ProductSerial with `is_external=true`, seeds SerialStatus `in_repair`, optionally creates SerialOwner record; added `markInRepair()` — updates serial status + writes SerialStatus history row
- `app/Livewire/Admin/Tickets/CreateForm.php`: added `lookupSerial()` — searches by serial_number, populates `$foundSerial` or sets `$showExternalForm=true`; updated `save()` to use SerialService for in-repair marking or external registration; reset now clears `ext_brand`, `ext_model`, `showExternalForm`, `foundSerial`
- `app/Livewire/Portal/Tickets/CreateForm.php`: same lookup/save pattern; portal adds ownership check (`current_owner_id === customer->id || is_external`); external registration auto-assigns customer from auth user
- `app/Livewire/Admin/Catalog/Products/Serials.php`: added `openHistory(int $id)` — eager-loads `statusHistory`, `ownerHistory.customer`, `tickets` (limit 10), builds `$historyData` array; added `closeHistory()`; imported `App\Models\Support\Ticket`
- `app/Livewire/Admin/Tickets/Show.php`: `mount()` and `changeStatus()` now eager-load `serial.statusHistory`, `serial.ownerHistory.customer`, `serial.tickets` (limit 5)

## 2026-05-09 — [design] Product returns module UI

- Created `resources/views/livewire/admin/returns/index.blade.php`: paginated table with search + status filter, reason labels in Russian, status badges (gray/blue/green/red), empty state
- Created `resources/views/livewire/admin/returns/create-form.blade.php`: full-page form — customer/sell selectors, conditional lines table with serial input + quantity control, reason/amount/notes section, breadcrumb
- Created `resources/views/livewire/admin/returns/show.blade.php`: two-column layout (items table left, info sidebar right), action buttons per status (approve/markRefunded/cancel), links to sell/invoice/ticket
- Updated `resources/views/livewire/admin/invoices/show.blade.php`: added "Возврат" button in header (shown only if `$invoice->sells->isNotEmpty()`)
- Updated `resources/views/livewire/admin/tickets/show.blade.php`: added "Оформить возврат" button in header actions area
- Updated `resources/views/livewire/admin/catalog/products/serials.blade.php`: all `$serial->status` replaced with `$serial->current_status`; filter options updated (removed reserved, added returned/in_repair); badge colors/labels updated for new statuses

## 2026-05-09 — [backend] Product returns module + serial tracking refactor

- Created `app/Services/SerialService.php`: `markSold()`, `markReturned()`, `markAvailable()` — writes to `serial_statuses` and `serial_owners` on every status transition
- Created `app/Services/ReturnService.php`: `generateNumber()` (RET-NNNN), `processRefund()` in DB transaction — calls SerialService for serial items, increments ProductStock for non-serial
- Updated `app/Livewire/Admin/Invoices/Show.php`: `createShipment()` now delegates to `SerialService::markSold()` instead of raw `update()`; `openSerialsModal()` queries `current_status` instead of `status`
- Updated `app/Livewire/Admin/Catalog/Products/Serials.php`: all `status` references changed to `current_status`; `addSerial()` and `importCsv()` write `current_status`; `render()` eager-loads `currentOwner` + `statusHistory` (replaces stale `customer`/`sellItem.sell`)
- Created `app/Livewire/Admin/Returns/Index.php`: paginated list with search + status filter
- Created `app/Livewire/Admin/Returns/CreateForm.php`: pre-fillable from `?sell_id` / `?ticket_id`; loads lines from sell; validates serial ownership (`current_status=sold`, `current_owner_id=customer`)
- Created `app/Livewire/Admin/Returns/Show.php`: `approve()`, `markRefunded()`, `cancel()` actions
- Added returns routes to `routes/web.php`: `/admin/returns`, `/admin/returns/create`, `/admin/returns/{productReturn}`

## 2026-05-09 — [database] Serial tracking refactor + product returns module

- Refactored `product_serials` migration: removed `customer_id`, `sell_item_id`, `sold_at`, `status(enum)`; added denormalized cache columns `current_status` (string 20, default 'available') and `current_owner_id` (FK customers, nullOnDelete)
- Created `serial_statuses` migration: append-only log (created_at only, no updated_at), FK serial_id → product_serials cascade, FK changed_by → users nullOnDelete
- Created `serial_owners` migration: ownership history per serial, `released_at = null` marks current owner, `return_item_id` intentionally has no FK (return_items table created after)
- Created `product_returns` migration: soft deletes, links to invoice/sell/customer/ticket, reason + status strings, refund_amount decimal(15,2)
- Created `return_items` migration: snapshot of name/sku, decimal quantity(10,3), FK to product_serials
- Rewrote `App\Models\Catalog\ProductSerial`: new fillable/casts, removed `customer()`+`sellItem()`, added `currentOwner()`, `statusHistory()`, `ownerHistory()`, added `scopeSold()` and `scopeReturned()`
- Created `App\Models\Catalog\SerialStatus` (timestamps=false, append-only)
- Created `App\Models\Catalog\SerialOwner`
- Created `App\Models\Sell\ProductReturn` (SoftDeletes)
- Created `App\Models\Sell\ReturnItem`
- Updated `App\Models\Sell\Sell`: added `productReturns()` HasMany
- Updated `App\Models\Invoice\Invoice`: added `productReturns()` HasMany
- Updated `data-contracts.md`: product_serials section rewritten; serial_statuses, serial_owners, product_returns, return_items sections added; TOC updated

## 2026-05-09 — [design] Serial number tracking UI across invoice shipment, ticket forms and serials table
- `invoices/show.blade.php`: shipment modal quantity cell is now conditional — serial products show selected-count + "Выбрать серии" button; non-serial products keep the number input
- `invoices/show.blade.php`: serials picker modal added (z-[60], @if gate) with scrollable checklist, header counter, Готово button
- `admin/tickets/create-form.blade.php`: serial_number input field added after subject
- `portal/tickets/create-form.blade.php`: serial_number input field added after subject
- `catalog/products/serials.blade.php`: "Клиент / Продажа" column added — renders customer name and sell number for sold serials (eager-load of customer + sellItem.sell on backend still pending)

## 2026-05-09 — [design] Product catalog CRUD UI wired up + serial number management views
- `products/index.blade.php`: added "+ Создать товар" button (gated by `update` policy) and `@if($showCreate)` slide-over rendering `create-form`
- `products/show.blade.php`: added "Редактировать" button + `@if($showEdit)` slide-over, dynamic tabs (@php-built array with conditional `serials` tab), serials tab panel with nested `livewire:admin.catalog.products.serials`
- `products/create-form.blade.php`: added `is_serial` checkbox in "Видимость" section
- `products/edit-form.blade.php`: added `id="product-edit-form"`, added `is_serial` checkbox, removed bottom Cancel/Save buttons (now in slide-over header)
- `products/serials.blade.php`: created new view — search/filter toolbar, inline add form, paginated table with status badges, CSV import collapsible

## 2026-05-09 — [database] Serial number support added to product catalog
- Added `is_serial` boolean column to `products` migration (after `is_visible_portal`, default false, indexed)
- Created new migration `2026_04_28_141000_create_product_serials_table.php`: tracks individual units by serial number with status (available/reserved/sold)
- Created `App\Models\Catalog\ProductSerial` model: fillable, `product()` BelongsTo, `scopeAvailable()`
- Updated `App\Models\Catalog\Product`: `is_serial` added to `$fillable` and `$casts`, `serials()` HasMany relationship added
- Updated `shared/data-contracts.md`: `products` table updated, `product_serials` table documented
- Backend stream: may want to add serial number assignment logic in QuoteService/InvoiceService when items are dispatched

## 2026-04-28 — [database] Phase 1 + Phase 2 миграции и модели созданы
- **Установлен Spatie Permission** (через Laragon Terminal)
- **Phase 1**:
    - Migration: расширение users (phone, is_active, avatar_path, last_login_at)
    - Model User расширен: HasRoles trait, scopeActive, scopeManagers
    - RolesSeeder — создаёт permissions и роли из config/permissions.php
    - DemoUsersSeeder — 6 демо-пользователей (admin@rsg.uz, manager@rsg.uz и т.д., пароль: password)
- **Phase 2 миграции** (6 файлов): business_types, customers, contacts, lead_sources, leads, lead_activities
- **Phase 2 модели** (6): BusinessType, Customer, Contact, LeadSource, Lead, LeadActivity
- **Сидеры справочников**: BusinessTypesSeeder (7 типов), LeadSourcesSeeder (7 источников)
- **Влияние**:
    - **backend** — может начинать Phase 2: Lead module (Livewire + Policies)
    - **design** — формы могут использовать select из business_types и lead_sources

## 2026-04-28 — [database] ER уточнена — справочники, i18n, атрибуты по категориям
- `business_types` → отдельный справочник (вместо enum) для customers + leads
- `product_translations` (RU + UZ) для названий и описаний товаров
- `category_attributes` + `product_attribute_values` — предопределённые характеристики по категории (вместо JSON specs)
- Итого +4 таблицы: business_types, product_translations, category_attributes, product_attribute_values
- **Влияние**:
  - **design** — формы Customer/Lead используют select для типа бизнеса; форма Product имеет UZ-вкладку и динамические поля по категории
  - **backend** — Livewire-компоненты подгружают локали и атрибуты через связи

## 2026-04-28 — [database] Полная ER-диаграмма всех 9 модулей
- Спроектированы 25+ таблиц для всех модулей CRM (Auth, Customers, Leads, Quotes, Invoices, Catalog, Equipment Requests, Tickets, System)
- Все поля, типы, индексы, FK, soft deletes
- Решения: single-tenant, UZS основная валюта, soft deletes для критичных
- 5 открытых вопросов для согласования (business_type enum, мультиязычность товаров и др.)
- **Влияние**:
  - **design** — теперь точная структура полей для форм Customer/Lead/Quote/Invoice/Product/Ticket
  - **backend** — модели и поля для Form Requests, Livewire-компонентов, Policies
- Файл: `.claude/shared/data-contracts.md`

## 2026-04-28 — [shared] Single-tenant архитектура зафиксирована
- RSG-CRM = одна установка для RSG. Другие компании = Customer (не tenant)
- Никаких `tenant_id` колонок
- Если в будущем понадобится SaaS — потребуется отдельная инстанция или крупный рефакторинг
- Записано в `shared/decisions-log.md`

## 2026-04-28 — [shared] Создана stream-структура (3 потока + shared)
- Разделение работы: design / database / backend
- Каждый поток имеет scope, STATUS, decisions, TODO, handoffs
- Shared knowledge: glossary, data-contracts, api-contracts, decisions-log
- Slash-команды `/design`, `/db`, `/backend`, `/sync`
- Custom subagents: designer, db-architect, backend-dev
- **Влияние**: при работе над любой задачей — определи поток, прочитай scope/STATUS

## 2026-04-28 — [backend] Контроль доступа и Acl helper
- Создан `App\Helpers\Acl` — единая точка проверки прав
- Зарегистрированы Blade-директивы `@acl`, `@isInternal`, `@isClient`
- Работает в preview-mode (без Spatie) и production
- **Влияние**: design — все условные рендеры через `@acl`. backend — `Acl::can()` в коде.

## 2026-04-28 — [shared] config/permissions.php — единая карта ролей и прав
- Источник истины для сидера, сайдбара, UI настроек
- 6 внутренних ролей, 2 клиентских
- ~40 permissions сгруппированных по модулям
- **Влияние**: database — сидер ролей берёт данные отсюда. design — сайдбар читает permissions.

## 2026-05-05 — [design] UI Polish — модалы, канбан, edit-форма лидов

- `<x-modal>` и `<x-slide-over>`: кнопки Cancel+Save перенесены в хедер справа, X кнопка убрана
- Leads kanban: исправлен горизонтальный скролл (`overflow-x-auto` + `width: max-content`), добавлена `max-height: calc(100vh - 300px)` для столбцов
- Leads kanban: кнопка-переключатель получила `type="button"` (предотвращает неявный submit)
- Leads таблица: кнопка "Удалить" убрана из действий строки
- Leads edit modal: переход от `@if($showEditForm)` к `@entangle('showEditForm')` — устраняет race condition при рендеринге вложенного Livewire-компонента
- **Влияние**: backend — `viewMode` убран из `$queryString` в `Leads\Index`, данные канбана перенесены в `render()`

## 2026-05-05 — [backend] Phases 2–8 завершены (ретроспективная запись)

- Реализованы все Livewire CRUD-компоненты: Leads, Customers, Catalog, Quotes, Invoices, Tickets, Portal
- PDF генерация (PdfService + dompdf), email-уведомления
- Dashboard с реальными KPI и Chart.js
- Eager loading в Index-компонентах (N+1 устранён)
- Portal с ownership-check, двухконтурная архитектура Admin/Portal

## 2026-05-05 — [backend] Customers Index + Show рефакторинг (Livewire 3)
- `Index.php`: убраны showCreateForm/showEditForm/editingCustomerId + все связанные методы; query перенесён из computed property в `render()` — фильтры теперь реактивны; добавлен `updatingPerPage()`; create-форма теперь Alpine event-driven
- `Show.php`: убраны showEditForm/onCustomerSaved; добавлены 18 `edit*` свойств для inline-editing; `saveField()` с явным маппингом полей → свойств, валидацией через `Validator::make` и dispatch('field-saved'); `syncEditFields()` приватный метод; управление пользователями: `attachUser()`, `detachUser()`, `showAddUserModal`; `render()` передаёт `businessTypes`, `banks`, `regions`; layout с `mainClass=''`

## 2026-04-28 — [design] Admin-шаблон применён в проект
- Layout (sidebar + header), dashboard
- Tailwind config с фирменной палитрой (primary blue, light theme)
- 10 wireframes в `.claude/artifacts/admin-panel/wireframes/`
- Permission-aware sidebar
- **Влияние**: backend — Livewire-вью используют этот layout (`@extends('layouts.admin')`)

## 2026-04-27 — [shared] Базовая настройка проекта
- CLAUDE.md с полным TZ
- `.claude/` структура: settings, memory, templates
- Архитектурные правила: CRUD → Livewire, контроль доступа 3 уровня
- **Влияние**: фундамент для всех потоков
