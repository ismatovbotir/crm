> ⚠️ АРХИВ (2026-07-06): stream-based workflow заменён на ролевую модель PM + субагенты. См. `CLAUDE.md` → "Часть 1.5. Архитектура агентов" и `.claude/agents/`. Этот файл сохранён только для истории.

# Backend Stream — STATUS

**Last updated**: 2026-05-11
**Phase**: Phase 8+ / Product Returns module

## ✅ Сделано

### Phase 1 (Auth + Roles)
- Login/Logout через Laravel default
- Middleware `role:internal` / `role:client`
- `App\Helpers\Acl` — единая точка проверки прав
- `AppServiceProvider` — Blade-директивы `@acl`, `@isInternal`, `@isClient`

### Phase 2 (Leads + Customers)
- `App\Livewire\Admin\Leads\{Index, CreateForm, EditForm, Show}`
- `App\Livewire\Admin\Customers\{Index, CreateForm, EditForm, Show}`
- `LeadPolicy`, `CustomerPolicy`
- routes в `routes/web.php` (группы admin/portal)

### Phase 3 (Catalog)
- `App\Livewire\Admin\Catalog\Categories\{Index, CreateForm, EditForm}`
- `App\Livewire\Admin\Catalog\Products\{Index, CreateForm, EditForm, Show}`

### Phase 4 (Quotes + Invoices)
- `App\Livewire\Admin\Quotes\{Index, CreateForm, Show}`
- `App\Livewire\Admin\Invoices\{Index, Show}` (частичные оплаты)
- Конвертация КП → Инвойс, авто-обновление статуса
- `QuotePolicy`, `InvoicePolicy`

### Phase 5 (Tickets)
- `App\Livewire\Admin\Tickets\{Index, CreateForm, Show}`
- Внутренние и публичные комментарии, assignee
- `TicketPolicy`

### Phase 6 (Portal)
- `App\Livewire\Portal\{Dashboard, Quotes\*, Invoices\*, Tickets\*, Catalog\*, Profile\*}`
- Ownership-check во всех Portal Show-компонентах

### Phase 7 (PDF + Notifications)
- `App\Services\PdfService` — generateQuote / generateInvoice
- `App\Http\Controllers\Admin\PdfController`, `Portal\PdfController`
- Notifications: `QuoteViewedNotification`, `QuoteAcceptedNotification`, `NewTicketNotification`

### Phase 8 (Polish)
- Eager loading (N+1 устранён во всех Index)
- `app/Livewire/Admin/Dashboard.php` — реальные KPI с Chart.js
- Leads: кнопка удаления убрана, edit modal через `@entangle` (надёжный показ)
- Leads kanban: ViewMode убран из `$queryString`, данные загружаются в `render()`

### Product Returns + Serial Tracking Refactor (2026-05-09)
- `app/Services/SerialService.php` — markSold / markReturned / markAvailable
- `app/Services/ReturnService.php` — generateNumber / processRefund
- `app/Livewire/Admin/Returns/{Index, CreateForm, Show}.php`
- `Admin\Invoices\Show` и `Admin\Catalog\Products\Serials` обновлены под новую схему (`current_status`)
- Роуты: `/admin/returns/*`

### Portal My Equipment (2026-05-09)
- `app/Livewire/Portal/Equipment/Index.php` — serial lookup, external registration, RSG serial claim, history panel
- Route `GET /portal/equipment` → `portal.equipment.index`

### Catalog: Groups & Recommendations CRUD (2026-05-10)
- `app/Livewire/Admin/Catalog/Groups/Index.php` — инлайн-редактирование строк, create slide-over, toggleActive, валидация через `rules()`/`createRules()`
- `app/Livewire/Admin/Catalog/Recommendations/Index.php` — выбор типа бизнеса, product autocomplete (search >= 2 chars, исключает уже добавленные), add/updatePriority/remove, `selectProduct()` helper
- `app/Policies/ProductPolicy.php` — добавлены методы `create()`, `update()`, `delete()` (ранее отсутствовали → 403 при любом mutating action)
- Stub Blade-шаблон для Recommendations создан; шаблон для Groups уже был от designer-агента
- Роуты уже зарегистрированы в `web.php`

### Quotes: Business-Type Recommendations (2026-05-10)
- `Admin\Quotes\CreateForm`: добавлен `$recommendations[]` property
- `updatedCustomerId()` — Livewire hook: при смене клиента загружает рекомендации по `business_type_id` из `business_type_recommendations` + eager load `category.group` + `prices`
- `render()` — `$productsList` теперь включает `group_name` и `group_color` через `category.group` eager load
- Зависит от db-агента: модели `ProductGroup`, `BusinessTypeRecommendation`; поле `group_id` в `categories`

### External Equipment Support + Serial History UI (2026-05-09)
- `SerialService`: `registerExternal()`, `markInRepair()`
- `Admin\Tickets\CreateForm`: `lookupSerial()`, external registration flow в `save()`
- `Portal\Tickets\CreateForm`: `lookupSerial()` с ownership check, external registration
- `Admin\Catalog\Products\Serials`: `openHistory()` / `closeHistory()` slide-over
- `Admin\Tickets\Show`: eager-load `serial.statusHistory`, `serial.ownerHistory.customer`, `serial.tickets`

### Documents\CreateForm unified component + Quotes EditForm redesign (2026-05-11)
- `app/Livewire/Admin/Documents/CreateForm.php` — unified КП/Инвойс creation, `$type = 'quote'|'invoice'`; global_discount_type/value; per-item discount_type/value/final_price; recommendations (quote only); `updatedGlobalDiscountType()` сбрасывает value на 0
- `app/Livewire/Admin/Quotes/EditForm.php` — полная переработка под новую модель скидок; customer typeahead; recommendations; invoice-lock (`abort_if invoice()->exists()`); все 3 пересчёта (recalculateTotal/FromFinalPrice/Discount)
- `QuoteController::edit()` — invoice-lock на уровне роута
- Скидка поля: `wire:model.blur` на value-input в обоих blade-файлах

## 🔄 В работе

— нет

## ⏭ Следующее (опционально)

- API endpoints (мобильное приложение)
- Telegram-бот уведомления
- Аудит-лог (`spatie/laravel-activitylog`)
- Экспорт CSV/Excel (лиды, клиенты, каталог)
- Reports страницы

## ⚠️ Требует ручных шагов

```bash
composer require barryvdh/laravel-dompdf
php artisan migrate:fresh --seed
php artisan queue:work
```
Настроить `.env`: MAIL_*, QUEUE_CONNECTION=redis
