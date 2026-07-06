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

## 2026-07-06 — [claude-md-maintainer] Roadmap Phase 12 (EquipmentRequest portal self-service + реальная конвертация в КП) + правки Модуля 6 и §2.4
- Часть 1.7 Roadmap: добавлена `### Phase 12: Equipment Request — portal self-service + реальная конвертация в КП ✅ (Завершено — 2026-07-06)` после Phase 11 — portal `CreateForm/Index/Show`, реальный `convertToQuote()` (создаёт `Quote`, идемпотентен, авто-статус `quoted`), `quotes.equipment_request_id` как настоящий FK (миграция `equipment_requests` переупорядочена раньше `quotes`), новая таблица/модель `equipment_request_comments`/`EquipmentRequestComment` с внутренними/публичными комментариями. Явно отмечено, что вложения и категоризация (Модуль 6) всё ещё не реализованы, нового Order/Cart нет.
- §1.5 Модуль 6: добавлен буллет "Переписка по заявке: внутренние и публичные комментарии — аналогично тикетам" + `EquipmentRequestComment` в "Ключевые сущности" — ранее модуль вообще не упоминал переписку, хотя параллельный Модуль 7 (Tickets) её описывает.
- §2.4 Eloquent Models: строка про `App\Models\Support\` дополнена — `EquipmentRequestComment (Phase 12)` (модель существовала в коде, но не была упомянута в списке).
- Источник: отчёт `[ticket-system]` за 2026-07-06 (создание portal-компонентов, реальная конвертация, схема, комментарии) + сверка ground truth напрямую по коду/миграциям (не только по self-report). Проверено, что Phase 5 (Tickets) не содержит вводящих в заблуждение утверждений о portal-подаче заявок — правка там не потребовалась.

## 2026-07-06 — [ticket-system] EquipmentRequest → Quote real conversion, client-visible comment thread, migration reorder + real FK
- **Migration reorder (housekeeping)**: `database/migrations/2026_04_28_160400_create_equipment_requests_table.php` → renamed/retimestamped to `database/migrations/2026_04_28_141300_create_equipment_requests_table.php` (right after `serial_owners`, before `quotes`/`invoices`/`tickets`) via `git mv`. `equipment_requests` only has FKs to `customers`/`users` (early Phase 1/2 tables), so it had zero real dependency on running after quotes/tickets. No other file referenced the old filename/timestamp (verified via grep) — safe rename.
- `database/migrations/2026_04_28_150000_create_quotes_table.php`: upgraded `equipment_request_id` from a plain `unsignedBigInteger` + index (laravel-fullstack's stopgap, no FK possible due to ordering) to a real `$table->foreignId('equipment_request_id')->nullable()->constrained('equipment_requests')->nullOnDelete()`. Confirmed via `migrate:fresh --seed` (exit 0, all migrations incl. `141300`/`141310` ran before `150000`) and via raw schema inspection (`SELECT sql FROM sqlite_master WHERE name='quotes'`) — the FK is now a real, enforced constraint (`on delete set null`), not just an indexed column.
- **New migration** `database/migrations/2026_04_28_141310_create_equipment_request_comments_table.php`: `equipment_request_id` (FK → equipment_requests, cascade), `user_id` (FK → users, mirrors `ticket_comments.user_id` exactly — no nullable/onDelete clause), `body`, `is_internal` (boolean, default false, mirrors `ticket_comments`), timestamps.
- **New model** `app/Models/Support/EquipmentRequestComment.php` — mirrors `TicketComment` exactly (`$fillable`, `$casts`, `equipmentRequest()`/`user()` BelongsTo).
- `app/Models/Support/EquipmentRequest.php`: added `quote(): HasOne` (reciprocal of `Quote::equipmentRequest()`), `comments(): HasMany` (latest first), `publicComments(): HasMany` (`is_internal=false`, latest first).
- **Real conversion logic** — `app/Livewire/Admin/EquipmentRequests/Show.php::convertToQuote()` rewritten (was a stub redirecting to `quotes.index` with a wrong `: string` return type): now idempotent (redirects to existing linked Quote's edit page if `quote()->exists()`), otherwise creates a real `Quote::create([...])` (number via the standard `КП-YYYY-NNNN` pattern, `manager_id` from the request's assigned manager or falls back to `auth()->id()`, `notes` prefilled with a human-readable summary built from subject/description/budget/needed_by, `equipment_request_id` set, status `draft`), auto-transitions `EquipmentRequest::status` to `'quoted'` (no second manual click needed), flashes a success message, and redirects (via Livewire's `$this->redirect(..., navigate: true)`, matching the codebase's existing convention over the global `redirect()` helper) to `admin.quotes.edit` so the manager can add line items immediately.
- **Comment thread (mirrors Ticket/TicketComment exactly)**:
  - Admin: `app/Livewire/Admin/EquipmentRequests/Show.php` gained `commentBody`/`isInternal` (default false) + `addComment()`; `resources/views/livewire/admin/equipment-requests/show.blade.php` renders the thread (internal notes badged "Внутр." same as Ticket admin show) + a reply form with an internal-note checkbox.
  - Portal: `app/Livewire/Portal/EquipmentRequests/Show.php` gained `commentBody` + `addComment()` (always `is_internal=false`, never trusts a client-supplied toggle — mirrors Ticket portal pattern exactly); `resources/views/livewire/portal/equipment-requests/show.blade.php` renders only `publicComments()` + a reply form (hidden once request is `closed`). Existing ownership check in `mount()` untouched.
- **Backlinks**: admin equipment-request show now links to its `quote` (`admin.quotes.show`) in the sidebar if one exists; portal equipment-request show links to its `quote` (`portal.quotes.show`) if one exists. Reverse direction of the backlink laravel-fullstack already added on the Quote show views.
- `.claude/shared/data-contracts.md`: updated the `quotes.equipment_request_id` note (real FK, not just indexed column) and the migration-reorder warning banner; `equipment_requests` section's "Связи" line now includes `quote()`/`comments()`/`publicComments()`, the "Конвертация в Quote" note now describes the real FK + auto status transition (previously described as "нет FK; ручная конвертация"); added a new `equipment_request_comments` table section (mirrors how `ticket_comments` is documented); updated the summary relations diagram (equipment_requests now shows `comments` + a real 1:1 `quotes` link instead of a dashed "no FK" arrow).
- **Verification**: `php artisan migrate:fresh --seed` — exit 0, no FK-ordering errors. `php artisan test` — 121 passed, 243 assertions, no regressions (qa-tester will add dedicated coverage for the new conversion/comment-thread logic separately).
- **Out of scope, untouched per instructions**: no new Order/Cart entity, no `EquipmentRequestAttachment`, `CLAUDE.md` not touched (routing any doc updates to `claude-md-maintainer` separately).

## 2026-07-06 — [laravel-fullstack] Quote ↔ EquipmentRequest linkage (schema + model + backlink UI) для конвертации заявки в КП
- Изменена существующая миграция `database/migrations/2026_04_28_150000_create_quotes_table.php`: добавлена nullable-колонка `equipment_request_id` (сразу после `contact_id`) + индекс. **Без DB-level FK constraint** — таблица `equipment_requests` создаётся более поздней миграцией (`2026_04_28_160400`), MySQL требует, чтобы referenced-таблица уже существовала в момент создания constraint'а в рамках одного `migrate:fresh`. Ссылочная целостность обеспечивается только на уровне Eloquent (`belongsTo`) + `nullOnDelete` семантика должна поддерживаться приложением (ticket-system) при удалении EquipmentRequest, если это важно.
- `app/Models/Quote/Quote.php`: `equipment_request_id` добавлен в `$fillable`; новый метод `equipmentRequest(): BelongsTo` → `App\Models\Support\EquipmentRequest`. Не добавлено в `getActivitylogOptions()->logOnly()` — разовая связь при создании, не меняется.
- UI backlink (только PHP-условие `@if($quote->equipment_request_id)`, разметка простая, дизайн не менялся):
  - `resources/views/livewire/admin/quotes/show.blade.php` — ссылка на `route('admin.equipment-requests.show', $quote->equipment_request_id)`
  - `resources/views/livewire/portal/quotes/show.blade.php` — ссылка на `route('portal.equipment-requests.show', $quote->equipment_request_id)`
- `.claude/shared/data-contracts.md`: раздел `quotes` дополнен строкой `equipment_request_id`, обновлены "Индексы" и "Связи".
- **Не трогал**: `app/Models/Support/*`, `app/Livewire/Admin/EquipmentRequests/*`, `app/Livewire/Portal/EquipmentRequests/*` — это делает `ticket-system` параллельно (перепишет `convertToQuote()` чтобы реально создавать `Quote::create([..., 'equipment_request_id' => $request->id])`).
- **Открытый вопрос для PM/ticket-system**: если строгая ссылочная целостность на уровне БД (реальный FK + `nullOnDelete`) станет важна, потребуется переупорядочить миграции quotes/quote_items/quote_versions/invoices/invoice_items/payments так, чтобы они шли после `2026_04_28_160400_create_equipment_requests_table.php` — это отдельная, более инвазивная задача, не входящая в текущий скоуп.

## 2026-07-06 — [qa-tester] Тесты на portal-подачу заявок на оборудование (Module 6, ticket-system's new self-service flow)
- Новый файл `tests/Feature/Tickets/EquipmentRequestPortalCreationTest.php` (18 тестов, 42 assertions) — покрывает `App\Livewire\Portal\EquipmentRequests\{CreateForm,Index,Show}`:
  - Happy path: `client-user` и `client-admin` создают заявку через `CreateForm::save()` — проверка `EquipmentRequest` в БД (`customer_id`, `status='submitted'`, `manager_id=null`, поля формы), редирект на `portal.equipment-requests.index`, dispatch `equipment-request-saved`, сброс полей формы.
  - Валидация: пустой `subject` (кастомное сообщение через `messages()`), нечисловой `budget`, отрицательный `budget`, невалидная дата `needed_by` — во всех случаях запись в БД не создаётся.
  - Ownership на создании: `customer_id` всегда резолвится на серверный `auth()->user()->customers()->first()->id`, даже если в БД есть другие Customer; отдельным тестом подтверждено, что `customer_id` НЕ является публичным Livewire-свойством (`->set('customer_id', ...)` кидает `PublicPropertyNotFoundException`) — структурно невозможно подменить владельца с клиента.
  - Ownership на Show/Index: клиент Customer A получает 403 при попытке открыть заявку Customer B напрямую (`Show::mount()`'s `abort_unless`), и не видит чужой `subject` в своём Index-списке.
  - Route/middleware smoke: guest → редирект на `/login`; internal-роль (`sales-manager`) → 403 на `/portal/equipment-requests` и `/portal/equipment-requests/create`; `client-user` → 200 на обоих роутах.
- `tests/Feature/Tickets/EquipmentRequestTest.php` — устаревший doc-комментарий ("there is no Livewire CreateForm... for creating an EquipmentRequest") заменён на актуальный, указывающий на новый файл с portal-покрытием.
- **Багов не найдено** — все 18 новых тестов прошли с первой попытки без правок production-кода; `CreateForm`/`Index`/`Show` от `ticket-system` реализованы корректно (ownership, валидация, server-side resolution `customer_id`).
- `php artisan test`: **121 passed / 0 failed** (было 103 baseline + 18 новых = 121, регрессий нет).

## 2026-07-06 — [ticket-system] Модуль 6: закрыт пробел portal-подачи заявок на оборудование (EquipmentRequest создание+история)
- Проблема: `EquipmentRequest::create` не имел ни одного call site в проекте — клиент физически не мог подать заявку на оборудование через портал, хотя Модуль 6/8 CLAUDE.md это требуют. Существовал только admin-side (`App\Livewire\Admin\EquipmentRequests\{Index,Show}` — просмотр/статус/назначение).
- `app/Livewire/Portal/EquipmentRequests/CreateForm.php` + `resources/views/livewire/portal/equipment-requests/create-form.blade.php` — форма подачи заявки (`subject`, `description`, `budget`, `needed_by`). `customer_id` всегда резолвится на сервере из `auth()->user()->customers()->first()->id` (никогда из клиентского инпута); `manager_id` остаётся `null` (назначается позже в admin-панели); `status` явно `'submitted'`.
- `app/Livewire/Portal/EquipmentRequests/Index.php` + `resources/views/livewire/portal/equipment-requests/index.blade.php` — история заявок клиента (скоуп по `customer_id` текущего клиента), фильтр по статусу, пагинация, статус-бейджи (цвета/лейблы скопированы из `App\Livewire\Admin\EquipmentRequests\Index`'s blade для консистентности).
- `app/Livewire/Portal/EquipmentRequests/Show.php` + `resources/views/livewire/portal/equipment-requests/show.blade.php` — доп. карточка заявки (не входила в обязательный скоуп, добавлена как логичное продолжение Index). Ownership-check: `auth()->user()->customers()->where('customers.id', $equipmentRequest->customer_id)->exists()` — паттерн скопирован из уже пофикшенного `Portal\Tickets\Show` (см. запись выше от laravel-fullstack про multi-company 403 fix), а не naive `->first()->id ===`.
- `routes/web.php`: 3 новых роута в существующей `portal.` группе (`role:client-admin|client-user` middleware) — `GET /portal/equipment-requests` (`portal.equipment-requests.index`), `GET /portal/equipment-requests/create` (`portal.equipment-requests.create`), `GET /portal/equipment-requests/{equipmentRequest}` (`portal.equipment-requests.show`). Не пересекается с существующим `/portal/equipment` (`Portal\Equipment\Index` — serial-tracking по `ProductSerial`, другая фича, не трогал).
- `resources/views/portal/partials/sidebar.blade.php` — новый пункт меню "Заявки на оборудование" → `/portal/equipment-requests`, добавлена иконка `clipboard` в `$icons`. Не спутан с существующим "Мои устройства".
- Permissions: не добавлял ничего в `config/permissions.php` — клиентские роли (`client-admin`/`client-user`) там имеют пустые массивы permissions, портал гейтится только route-middleware (`role:client-admin|client-user`), это уже установленная в проекте конвенция (см. `Portal\Tickets\CreateForm` — та же схема, без `$this->authorize()` внутри).
- `.claude/shared/data-contracts.md` — секция "Equipment Requests" (~строка 616) переписана по факту реальной схемы: удалена фантомная таблица `request_categories` (её нет в БД), из `equipment_requests` убраны несуществующие поля `number`/`contact_id`/`category_id`/`title`/`deadline`/`quote_id`/`closed_at`, вписаны реальные (`customer_id` nullable/nullOnDelete, `manager_id` nullable/nullOnDelete, `subject` string(500), `description`/`budget`/`needed_by`/`notes` nullable, soft deletes). Обновлены TOC-ссылка и ASCII-диаграмма связей (убрана строка `request_categories ── equipment_requests`, строка про конвертацию в `quotes` помечена как "нет FK, ручной процесс").
- **Сознательно НЕ сделано (backlog, не баг)**: (a) вложения — подсистемы `EquipmentRequestAttachment` не существует, новую не создавал; (b) категоризация — поля `category_id` в реальной схеме нет (в отличие от Tickets), новую миграцию/колонку не добавлял. Оба пункта отмечены в data-contracts.md как "Известные пробелы".
- Admin-side (`App\Livewire\Admin\EquipmentRequests\*`, `EquipmentRequestController`) не трогал — вне скоупа этой задачи.
- **Влияние на других агентов**: если потребуется полноценная конвертация заявки в `Quote` (сейчас это ручной процесс менеджера, без FK) — это домен `laravel-fullstack` (Quote не мой домен). Изменений, требующих правки `CLAUDE.md`, нет (Модули 6/8 уже описывают эту функциональность как ожидаемую — теперь она просто реализована).

## 2026-07-06 — [laravel-fullstack] Фикс 3 багов из qa-tester findings (Category policy, Product EditForm crash, Portal ownership)
- `app/Policies/CategoryPolicy.php`: добавлены `create()`, `update(?Category)`, `delete()` — ранее отсутствовали, из-за чего никто (включая super-admin) не мог создать/отредактировать Category через CRM UI. Паттерн повторяет `ProductPolicy` (`hasAnyRole(['super-admin','catalog-manager'])` || explicit `catalog.products.*` permission)
- `app/Policies/CatalogPolicy.php` — удалён (dead-code дубликат класса `CategoryPolicy`, никогда не был зарегистрирован в `AuthServiceProvider`)
- `app/Livewire/Admin/Catalog/Products/EditForm.php::mount()`: nullable-колонки (`name_uz`, `brand`, `model_number`, `description_ru`) больше не приводят к `TypeError` при `fill()` — коалесятся в `''` перед присвоением строковым свойствам (консистентно с `CreateForm`)
- `app/Livewire/Admin/Catalog/Categories/EditForm.php::mount()`: тот же фикс для `name_uz`/`icon` — тот же класс бага обнаружился при прогоне теста на Category (блокировал зелёный тест из Бага 1), это не было явно described в задаче, но необходимо для прохождения `test_catalog_manager_can_update_category`
- `app/Livewire/Portal/Invoices/Show.php` и `app/Livewire/Portal/Tickets/Show.php`: ownership-проверка переведена с `auth()->user()->customers()->first()` на `->where('customers.id', ...)->exists()` — устранён ложный 403 для multi-company портал-пользователей, чья компания не первая в pivot `customer_users`. Паттерн скопирован из уже корректного `App\Livewire\Portal\Quotes\Show`
- `php artisan test`: 103/103 зелёных (было 102/103 до фикса Category EditForm null-bug, обнаруженного по ходу)
- Не трогал `app/Livewire/Admin/Tickets/*` и `app/Livewire/Admin/EquipmentRequests/*` — это домен `ticket-system`

## 2026-07-06 — [ticket-system] Фикс authorization gaps в Tickets/EquipmentRequests Index/CreateForm
- `App\Livewire\Admin\Tickets\Index`: добавлен `mount()` с `$this->authorize('viewAny', Ticket::class)` — ранее любой internal-пользователь без `tickets.view` (sales-manager, catalog-manager, accountant) видел полный список тикетов
- `App\Livewire\Admin\Tickets\CreateForm::save()`: добавлен `$this->authorize('create', Ticket::class)` в начало метода — ранее тикет мог создать любой internal-пользователь без `tickets.*` прав
- `App\Livewire\Admin\EquipmentRequests\Index`: добавлен `mount()` с `abort_unless(auth()->user()->can('equipment-requests.view'), 403)` — по аналогии с уже защищённым `Show`; ранее список заявок был виден без проверки прав
- `TicketPolicy::viewAny/create` уже существовали (без изменений схемы policy) — использованы как есть
- Тесты qa-tester (`tests/Feature/Tickets/TicketManagementTest.php`, `tests/Feature/Tickets/EquipmentRequestTest.php`) теперь зелёные: 20/20 passed в `tests/Feature/Tickets/`; полный прогон `php artisan test` — 102/103 passed, единственный красный тест (`Catalog\CategoryManagementTest::test_catalog_manager_can_update_category`) вне Support-домена, чинится параллельно `laravel-fullstack`
- Открытый вопрос (не в этом скоупе, для PM/product-обсуждения): в проекте нет UI/кода для создания `EquipmentRequest` (ни admin, ни portal) — Модуль 6 описывает "клиент создаёт заявку через портал", но такого компонента не существует; `Portal\Equipment\Index` — другая фича (serial-tracking), не связана с `equipment_requests`

## 2026-07-06 — [claude-md-maintainer] Roadmap Phase 11 + актуализация Redis/activitylog/Telegram статусов
- Часть 1.7 Roadmap: добавлена `### Phase 11: Test Coverage & Infra Hardening ✅ (Завершено — 2026-07-06)` после Phase 10 — расширенное тестовое покрытие (27→103 теста), перевод очередей на Redis, установка и подключение `spatie/laravel-activitylog` к 4 моделям, `TELEGRAM_BOT_TOKEN` placeholder, подтверждение `migrate:fresh --seed`; открытый вопрос про 6 найденных багов вынесен отдельным пунктом
- §2.8 Performance & Caching: строка про Redis Queue уточнена — это факт конфигурации (`QUEUE_CONNECTION=redis`), а не только рекомендация
- §2.9 Security & Compliance: строка про аудит-лог изменена с "планируется" на факт — пакет установлен, подключён к `Customer`/`Lead`/`Quote`/`Invoice`, таблица `activity_log`
- §2.6 Environment Configuration: уточнено, что `TELEGRAM_BOT_TOKEN` — уже существующий пустой placeholder в `.env`/`.env.example`, сама интеграция бота всё ещё не реализована
- Источник: отчёты `[qa-tester]` и `[laravel-fullstack]` за 2026-07-06 (см. записи ниже)

## 2026-07-06 — [qa-tester] Новое покрытие: Catalog, Tickets/EquipmentRequests, Portal ownership, route smoke — 5 новых багов найдено

**Новые фабрики** (`database/factories/`): `CategoryFactory`, `ProductFactory`, `TicketFactory`, `EquipmentRequestFactory`, `TicketCommentFactory`.
- `Category`, `EquipmentRequest`, `TicketComment` не используют `HasFactory`/`newFactory()` (в отличие от конвенции CLAUDE.md §2.4) — фабрики созданы напрямую через `SomeFactory::new()->create()` вместо `Model::factory()`. Не фиксил модели (вне скоупа QA), но это гигиенический пробел для `laravel-fullstack`: стоит добавить трейт+override в 3 модели по аналогии с `Product`/`Ticket`.
- `ProductFactory` не может использовать `Category::factory()` по той же причине — использует `CategoryFactory::new()` напрямую как default для `category_id`.
- `Ticket.priority` реальный default в БД — `'medium'` (не `'normal'`, как было в `data-contracts.md` до этой сессии); `EquipmentRequest`/`Ticket` реальные поля заметно отличаются от `data-contracts.md` (нет `sla_due_at`, `first_response_at`, `RequestCategory`, `contact_id`/`quote_id` на equipment_requests и т.д. — таблица `Tickets`/`Equipment Requests` в `data-contracts.md` не актуализирована, в отличие от Quotes/Invoices; нужен проход `ticket-system`/`laravel-fullstack` по актуализации).

**Новые тесты**: `tests/Feature/Catalog/{CategoryManagementTest,ProductManagementTest}.php`, `tests/Feature/Tickets/{TicketManagementTest,EquipmentRequestTest}.php`, `tests/Feature/Access/{PortalOwnershipTest,RouteSmokeTest}.php` — 76 новых тестов (viewAny/create/update/delete allow-deny для Category/Product, ticket creation+SLA+priority+ownership+internal/public comment visibility, EquipmentRequest submitted→under_review→quoted→closed lifecycle + manager assignment, Portal Quotes/Invoices/Tickets ownership Index+Show, 23-route HTTP smoke test admin+portal для всех демо-ролей).

**Итог `php artisan test`** (`D:\OSPanel\modules\PHP-8.4\PHP\php.exe artisan test`): **103 tests, 94 passed, 8 failed, 1 error, 199 assertions**. Все 9 непрошедших тестов — намеренно оставленные документирующие баг тесты (per QA-policy, не переписывались "под баг"), сгруппированы в 6 отдельных находок ниже. Остальные 94 (включая все 23 route-smoke, все Portal-ownership allow/deny кейсы для одного-customer случая, все Product allow/deny/validation кейсы, Ticket SLA/comment-visibility кейсы, полный EquipmentRequest lifecycle) — зелёные.

### Найденные баги (для делегирования)

1. **`CategoryPolicy` не реализует `create()`/`update()`/`delete()` — категорию нельзя создать/отредактировать НИКОМУ, включая super-admin** (`app/Policies/CategoryPolicy.php`). `AuthServiceProvider::$policies` регистрирует `CategoryPolicy` для `Category`, но класс содержит только `viewAny()`/`view()`. Без `Gate::before` (его нет нигде в приложении) `Gate::authorize('create', Category::class)` для отсутствующего метода policy возвращает `false` для всех. `Categories\CreateForm::save()`/`EditForm::save()` вызывают `authorize()` раньше `validate()`, так что заодно не тестируется required/unique-slug валидация. Подтверждено 3 тестами: `CategoryManagementTest::test_super_admin_can_create_category`, `test_catalog_manager_can_create_category`, `test_catalog_manager_can_update_category`. → **laravel-fullstack**: добавить `create()`/`update()`/`delete()` в `CategoryPolicy`, по образцу `ProductPolicy` (`hasAnyRole(['super-admin','catalog-manager'])`).
   - **Побочный файл-баг**: `app/Policies/CatalogPolicy.php` объявляет `class CategoryPolicy` (дубль имени класса с `CategoryPolicy.php`, в одном namespace `App\Policies`) — мёртвый файл, PSR-4 его не грузит (грузится только `CategoryPolicy.php` по имени файла), но явно copy-paste опечатка (должен быть `class CatalogPolicy` или файл не нужен). Не ломает тесты, но стоит почистить.

2. **`Admin\Catalog\Products\EditForm::mount()` падает с `TypeError`, если у товара `name_uz`/`brand`/`model_number`/`description_ru` = `null`** (реалистичный кейс — это nullable-колонки без дефолта). Публичные свойства компонента типизированы как non-nullable `string`, а `$product->only([...])` отдаёт настоящий `null` из БД → `Livewire::fill()` кидает `Cannot assign null to property ...::$name_uz of type string`. Подтверждено (error, не failure): `ProductManagementTest::test_editing_product_with_null_optional_fields_crashes_editform`. → **laravel-fullstack**: либо `?? ''` при `fill()`, либо сделать свойства `?string`.

3. **`Admin\Tickets\Index` не вызывает `$this->authorize('viewAny', Ticket::class)`** — `TicketPolicy::viewAny()` требует `tickets.view` (есть только у `super-admin`/`sales-director`/`tech-support`), но `sales-manager`/`catalog-manager`/`accountant` (у которых `tickets.view` нет) проходят широкий route-middleware `/admin/tickets` и видят ПОЛНЫЙ список тикетов — `getTicketsProperty()` сужает выборку только для роли `tech-support` буквально, для остальных вообще не фильтрует. Подтверждено: `TicketManagementTest::test_sales_manager_without_tickets_permission_cannot_see_tickets_index`. → **ticket-system** (домен Tickets).

4. **`Admin\Tickets\CreateForm::save()` не вызывает `authorize('create', Ticket::class)` вообще** — `TicketPolicy::create()` существует и корректно проверяет `tickets.view`, но никогда не вызывается компонентом, поэтому `sales-manager` (без единого `tickets.*` permission) может создать тикет через `/admin/tickets`. Подтверждено: `TicketManagementTest::test_sales_manager_cannot_create_ticket`. → **ticket-system**.

5. **`Admin\EquipmentRequests\Index` тоже не авторизует (тот же паттерн, что и баг №3, но в другом компоненте)** — `Show::mount()` корректно делает `abort_unless(auth()->user()->can('equipment-requests.view'), 403)`, а `Index` — нет никакой проверки вообще. `sales-manager`/`catalog-manager`/`accountant` (без `equipment-requests.view`) видят полный список заявок на оборудование (тема, клиент, бюджет) через `/admin/equipment-requests`. Подтверждено: `EquipmentRequestTest::test_sales_manager_without_permission_cannot_see_equipment_requests_index`. → **ticket-system**.
   - **Наблюдение (не баг, а пробел функциональности)**: нигде в коде нет пути создания `EquipmentRequest` (ни Livewire `CreateForm`, ни контроллер — `grep EquipmentRequest::create` даёт 0 совпадений). `App\Livewire\Portal\Equipment\Index` — это другая фича (самостоятельный учёт серийных номеров/устройств клиента через `ProductSerial`), не связанная с таблицей `equipment_requests`. Модуль 6 из `CLAUDE.md` §1.5 ("клиент создаёт заявку через портал") похоже не реализован for real incoming requests — только Admin `Index`/`Show` (просмотр + смена статуса + назначение менеджера) существуют. Стоит уточнить у `pm-b2b-crm`, планируется ли ещё портальная форма создания заявки.

6. **`Portal\Invoices\Show`/`Portal\Tickets\Show` используют `auth()->user()->customers()->first()` вместо проверки по ЛЮБОЙ привязанной компании** (в отличие от корректного паттерна в `Portal\Quotes\Show`, который делает `->where('customers.id', $quote->customer_id)->exists()`). Для portal-пользователя, привязанного к 2+ `Customer` через `customer_users`, просмотр СВОЕГО ЖЕ инвойса/тикета второй компании ошибочно даёт 403 (false-deny, не IDOR — не пропускает чужие данные, но ошибочно блокирует часть своих). Подтверждено: `PortalOwnershipTest::test_client_with_two_customers_can_view_invoice_belonging_to_second_customer`, `test_client_with_two_customers_can_view_ticket_belonging_to_second_customer`. → **laravel-fullstack**: привести `Invoices\Show`/`Tickets\Show` к паттерну `Quotes\Show`.

**Проверено и подтверждено корректным (без багов)**: ownership-check во всех Portal `Quotes/Invoices/Tickets` Index/Show для одного-customer случая (allow свой / deny чужой — оба направления по каждому модулю), `ProductPolicy` create/update/delete allow/deny по ролям, sku/name_ru required + sku unique validation на создании/редактировании Product, `TicketPolicy::view()` ownership (assignee/creator) allow/deny + `sales-director`/`super-admin` bypass, `Portal\Tickets\Show` корректно фильтрует `is_internal=true` комментарии из выдачи клиенту (`publicComments()`), все 23 admin+portal маршрута из ключевого меню отвечают `200 OK` для соответствующих демо-ролей (регрессия на баг №1 из прошлой сессии не вернулась).

**Замечание о параллельной работе**: во время этой сессии в рабочей копии (не мной) шли одновременные изменения `app/Models/{Customer,Lead,Invoice,Quote}.php` (`composer require spatie/laravel-activitylog` интеграция) — короткое время namespace `Spatie\Activitylog\Traits\LogsActivity`/`Spatie\Activitylog\LogOptions` в этих 4 моделях не совпадал с фактически установленным v5 пакетом (`Spatie\Activitylog\Models\Concerns\LogsActivity`/`Spatie\Activitylog\Support\LogOptions`), что на несколько минут ронялo Fatal Error весь `php artisan test` (включая старый зелёный baseline). К моменту финального прогона это было исправлено (другим агентом/сессией) — итоговые цифры выше сняты уже на исправленном коде. Упоминаю на случай, если кто-то видел этот Fatal Error в логах параллельно — это не относится к находкам qa-tester выше.

## 2026-07-06 — [laravel-fullstack] Инфраструктура: Redis queue, activitylog, TELEGRAM_BOT_TOKEN placeholder, migrate:fresh --seed

**1. Redis queue** (`.env`): `QUEUE_CONNECTION=database` → `redis`. Обнаружено на этой машине: phpredis-расширение физически присутствует (`php_redis.dll` в `D:\OSPanel\modules\PHP-8.4\PHP\ext`), но было закомментировано в `php.ini` (`;extension = redis`) — включено (`extension = redis`). Redis-сервер (`D:\OSPanel\modules\Redis-7.4\redis-server.exe`) не был запущен (`redis-cli ping` → connection refused) — запущен вручную в фоне (`redis-server.exe --port 6379`), это **не Windows-сервис**, не переживёт перезагрузку — нужно либо настроить автозапуск через OSPanel/как службу, либо стартовать вручную при каждом старте окружения. Проверено сквозным тестом: диспатч job через `Queue::push()` → подтверждено напрямую через `redis-cli llen laravel-database-queues:default` (0→1) → `php artisan queue:work redis --queue=default --once` → job показал `RUNNING`→`DONE`, побочный файл реально записан, очередь вернулась в 0. Реальная сквозная работа очереди через Redis подтверждена, не только конфиг.

**2. `spatie/laravel-activitylog`** установлен (`composer require`, зафиксировалась версия **5.0.0** — важно: пакет в этой версии переехал с `Spatie\Activitylog\Traits\LogsActivity`/`Spatie\Activitylog\LogOptions` (API из документации/задания, соответствующей v4.x) на `Spatie\Activitylog\Models\Concerns\LogsActivity`/`Spatie\Activitylog\Support\LogOptions` — использованы актуальные namespace, иначе `Trait not found` (поймано и исправлено на первом прогоне `migrate:fresh --seed`, детали — см. соседнюю запись qa-tester про "замечание о параллельной работе", это была не параллельная сессия, а мой собственный первый прогон). Миграция `create_activity_log_table` опубликована (реальное имя таблицы пакета — `activity_log`, единственное число, **не** `audit_logs`, как было заглушкой в `data-contracts.md`). Конфиг `config/activitylog.php` тоже опубликован.
- Трейт + `getActivitylogOptions()` (`LogOptions::defaults()->logOnly([...])->logOnlyDirty()->useLogName(...)`) добавлены в 4 модели: `Customer`, `Lead`, `Quote`, `Invoice` — список полей ограничен ключевыми бизнес-атрибутами (без служебных timestamps).
- `data-contracts.md` → секция System → `audit_logs`-заглушка заменена на реальную схему `activity_log` с полным списком колонок и перечнем подключённых моделей.

**3. `TELEGRAM_BOT_TOKEN=`** добавлен пустой placeholder в `.env` и `.env.example` (без значения — токен впишет пользователь позже).

**4. `php artisan migrate:fresh --seed`**: первый прогон упал на `Trait "Spatie\Activitylog\Traits\LogsActivity" not found` (см. п.2, исправлено немедленно). Второй прогон упал на `DemoLeadsSeeder` → `Call to undefined method Database\Factories\CustomerFactory::vip()` — `DemoLeadsSeeder::run()` вызывает `Customer::factory(2)->vip()->create()`, но `CustomerFactory` (уже существовавший до этой сессии) не определял состояние `vip()`. Добавлен метод `vip()` в `CustomerFactory` (`segment='A', status='vip'`). Третий прогон — **чистый успех**: все миграции + все сидеры (`RolesSeeder`, `BusinessTypesSeeder`, `BanksSeeder`, `LeadSourcesSeeder`, `ProductGroupsSeeder`, `CatalogSeeder`, `ProductSeeder`, `BusinessTypeRecommendationsSeeder`, `TicketCategoriesSeeder`, `DemoUsersSeeder`, `DemoLeadsSeeder`) отработали end-to-end без ошибок (exit code 0).

**`php artisan test` после всех изменений**: 103 tests, 94 passed, 8 failed, 1 error — все непройденные тесты принадлежат параллельной сессии `qa-tester` (Catalog/Tickets/Portal-ownership, см. соседнюю запись выше `[qa-tester] Новое покрытие...`), задокументированы как намеренные bug-tracking тесты, из них 3 явно делегированы `laravel-fullstack` (`CategoryPolicy` create/update/delete, `Products\EditForm` null-crash, `Portal\Invoices|Tickets\Show` two-customer false-deny) — **не фиксились в этой сессии**, т.к. вне скоупа заданных 4 инфраструктурных задач; `phpunit.xml` форсирует `QUEUE_CONNECTION=sync`+`DB_DATABASE=:memory:`, так что Redis-изменения из п.1 никак не влияют на тестовый прогон. Рекомендуется отдельная задача на исправление 3 делегированных багов.

**Изменённые/созданные файлы**: `.env`, `.env.example`, `composer.json`, `composer.lock`, `config/activitylog.php` (новый), `database/migrations/2026_07_06_150311_create_activity_log_table.php` (новый), `database/factories/CustomerFactory.php`, `app/Models/Customer/Customer.php`, `app/Models/Lead/Lead.php`, `app/Models/Quote/Quote.php`, `app/Models/Invoice/Invoice.php`, `.claude/shared/data-contracts.md`. Системное изменение вне репозитория: `D:\OSPanel\modules\PHP-8.4\PHP\php.ini` (включено `extension = redis`) — затрагивает все проекты на этом PHP 8.4 модуле OSPanel, не только rService.

## 2026-07-06 — [qa-tester] Регрессионный прогон после фиксов laravel-fullstack
- Переписан `tests/Feature/Quotes/QuoteToInvoiceConversionTest::test_creating_quote_items_via_mass_assignment_fails_due_to_missing_final_price_fillable` (документировал баг №2 через `expectException(QueryException::class)`, стал ложно-красным после фикса) → переименован в `test_creating_quote_items_via_mass_assignment_persists_final_price`: теперь это позитивный регрессионный тест — создание `QuoteItem` через mass-assignment (реальный путь `Documents\CreateForm::saveQuote()`) должно проходить без exception, `assertHasNoErrors()`, `QuoteItem` должен быть создан в БД, и `final_price` должен корректно сохраняться и читаться обратно (`->fresh()->final_price === 500000.0`). Это защищает от повторного выпадения `final_price` из `$fillable`/`$casts` в будущем. Остальные тесты в файле не тронуты.
- Полный прогон `php artisan test` (через `D:\OSPanel\modules\PHP-8.4\PHP\php.exe artisan test`, т.к. `php` не резолвится в PATH): **27 tests, 27 passed, 75 assertions**, 0 failures. Проверены поимённо (`--testdox`) все файлы: `AccessControlTest` (11/11 — баги №1 и №3 подтверждённо исправлены), `InvoiceGenerationTest` (5/5), `LeadCreationTest` (4/4), `QuoteToInvoiceConversionTest` (5/5, включая переписанный тест), `ExampleTest` (1/1 Feature + 1/1 Unit).
- Новых багов/расхождений в ходе этого прогона не обнаружено. Открытые вопросы из предыдущих записей (создание `CustomerFactory` — уже сделано в baseline; `data-contracts.md` устарел по Quote/Invoice полям — уже актуализирован laravel-fullstack в предыдущей записи) закрыты, дополнительных находок нет.
- **Влияние**: тестовый набор полностью зелёный, задокументированные баги №1–3 подтверждены исправленными регрессионными тестами (не просто "тест удалён", а перепроверено ожидаемое поведение).

## 2026-07-06 — [laravel-fullstack] Фикс 3 критичных багов из qa-tester baseline
- **Баг №1 (критичный, ломал всё приложение)**: `bootstrap/app.php` — `->withMiddleware()` был пустым, из-за чего Laravel 13-style bootstrap никогда не читал `app/Http/Kernel.php` (мёртвый код), и алиасы `role`/`permission`/`role_or_permission`/`internal`/`client` не были зарегистрированы. Любой роут с `role:...` middleware (буквально все `/admin/*` и `/portal/*`) кидал `BindingResolutionException: Target class [role] does not exist.` для авторизованных пользователей (гостей спасал редирект на `/login` раньше по pipeline). Фикс: зарегистрированы алиасы через `$middleware->alias([...])` в `bootstrap/app.php`; `app/Http/Kernel.php` удалён (не используется нигде — проверено, что на него нет ссылок в `bootstrap/providers.php`/`config/app.php`).
- **Баг №2 (данные)**: `app/Models/Quote/QuoteItem.php` — `$fillable` не включал `final_price`, а `quote_items.final_price` — `NOT NULL` без дефолта. Mass-assignment из `Documents\CreateForm::saveQuote()` и `Quotes\CreateForm::save()` молча отбрасывал поле → `QueryException` при создании любого КП с позициями через реальный UI. Фикс: добавлен `'final_price'` в `$fillable` и `'final_price' => 'decimal:2'` в `$casts`. Проверена аналогичная модель `App\Models\Invoice\InvoiceItem` — расхождений нет, её `$fillable` (`invoice_id, product_id, name, sku, quantity, unit_price, tax_rate, total, sort_order`) полностью покрывает то, что реально передаётся в `Documents\CreateForm::saveInvoice()`; изменений не потребовалось.
- **Баг №3 (IDOR)**: `App\Livewire\Admin\Invoices\Show::mount()` не вызывал `$this->authorize('view', $invoice)` — любой internal-пользователь мог открыть чужой инвойс по прямой ссылке `/admin/invoices/{id}`, минуя `InvoicePolicy::view()` (сам policy реализован корректно). Фикс: добавлен `$this->authorize('view', $invoice);` в начало `mount()`, по аналогии с `Leads\Show`/`Quotes\Show`. Проверен `App\Http\Controllers\Admin\InvoiceController::show()` — он используется в роуте `admin.invoices.show`, но, как и `LeadController::show()`/`QuoteController::show()`, является тонкой обёрткой, которая просто рендерит blade-шаблон с `<livewire:admin.invoices.show :invoice="$invoice" />` внутри и не содержит собственной бизнес-логики/authorize — это устоявшийся в проекте паттерн (авторизация всегда только в Livewire `mount()`), поэтому в контроллер authorize намеренно не добавлялся, чтобы не дублировать и не расходиться с паттерном для Leads/Quotes.
- **Прогон `php artisan test`**: 27 тестов, 26 passed, 1 failed. Упавший тест — `tests/Feature/Quotes/QuoteToInvoiceConversionTest::test_creating_quote_items_via_mass_assignment_fails_due_to_missing_final_price_fillable`, который через `expectException(QueryException::class)` намеренно документировал баг №2 — теперь бага нет, исключение не бросается, и тест стал "ложно-красным". Это ожидаемо (предсказано координатором заранее) и **не переписывался мной** — это зона `qa-tester`, тест нужно адаптировать под "теперь создание проходит успешно". Все 10 тестов `AccessControlTest` (баг №1) и IDOR-тест на Invoices\Show (баг №3) теперь зелёные. `LeadCreationTest` и `InvoiceGenerationTest` не пострадали.
- Обновлён `.claude/shared/data-contracts.md`: секции "Quotes" и "Invoices" переписаны под реальную схему миграций/моделей — реальные поля `discount_total` (не `discount_amount`), `tax_rate`/`tax_amount` (не `vat_percent`/`vat_amount` — актуально только для `invoices`, `quotes` по-прежнему использует `vat_percent`/`vat_amount`), `shipment_status` вместо несуществующих `balance`/`paid_at`/`cancelled_at`/`cancel_reason`/`pdf_path` на invoices; добавлена документация колонки `final_price` в `quote_items`; исправлены названия полей `total` (не `line_total`), `items_snapshot` (не `snapshot`), `recorded_by` (не `created_by` в payments), `method` default `'bank_transfer'` (не `'bank'`); добавлены связи `invoice()`, `sells()`, `productReturns()`, accessor `remaining`, scopes.
- **Влияние**: приложение теперь снова работоспособно для всех ролей (баг №1 был blocker уровня "приложение полностью недоступно"). Создание КП с позициями через `Documents\CreateForm`/`Quotes\CreateForm` больше не падает. Invoices\Show больше не допускает IDOR.
- **Открытый вопрос для `qa-tester`**: адаптировать `QuoteToInvoiceConversionTest::test_creating_quote_items_via_mass_assignment_fails_due_to_missing_final_price_fillable` — либо переименовать/переписать в позитивный тест "создание успешно проходит", либо удалить как задокументированный и уже исправленный баг.

## 2026-07-06 — [laravel-fullstack] Удаление legacy-миграций + полный прогон migrate:fresh/seed + восстановление config/permissions.php
- Удалены (одобрено пользователем явно) 3 legacy-дубля миграций: `2014_10_12_000000_create_users_table.php`, `2014_10_12_100000_create_password_reset_tokens_table.php`, `2019_08_19_000000_create_failed_jobs_table.php` — эти таблицы уже создаются современными консолидированными `0001_01_01_*` миграциями (Laravel 11+ stubs); дубли ломали `migrate:fresh`. Коммит `95b890f`, содержит только эти 3 удаления.
- **Обнаружен и устранён критичный пробел**: `config/permissions.php` — файл, документированный в `CLAUDE.md` и changelog (запись от 2026-04-28 "config/permissions.php — единая карта ролей и прав") как уже существующий, **физически отсутствовал в репозитории** (не найден ни в рабочем дереве, ни в истории git — судя по всему, не был закоммичен на Phase 1). Из-за этого `RolesSeeder` и `App\Livewire\Admin\Setup` создавали 0 permissions/ролей.
- Восстановлен `config/permissions.php` реконструкцией из фактически используемых в коде permission-строк (`grep` по `->can(...)`, `Acl::can(...)`, `@acl(...)`, `Policy`-классам, `sidebar.blade.php`) + сверкой с картой ролей из `CLAUDE.md` §2.4. Итог: 40 permissions (11 групп: leads, customers, quotes, invoices, sells, returns, catalog, tickets, equipment_requests, reports, settings), 8 ролей (`super-admin` = все permissions, `sales-director`, `sales-manager`, `tech-support`, `catalog-manager`, `accountant`, `client-admin`/`client-user` без module-permissions — портал использует ownership-check в Livewire, не Spatie permissions).
- `php artisan migrate:fresh` — все 44 миграции прошли чисто (`Ran`).
- `php artisan db:seed --class=RolesSeeder` — 8 ролей / 40 permissions созданы. Полный `php artisan db:seed` — все core-сидеры (BusinessTypes, Banks, LeadSources, ProductGroups, Catalog, Product, BusinessTypeRecommendations, TicketCategories, DemoUsers) прошли успешно; `DemoLeadsSeeder` **упал** с `Class "Database\Factories\CustomerFactory" not found` — фабрика для `Customer` отсутствует в `database/factories/` (найден только `UserFactory.php`). Это pre-existing пробел, не связан с задачей миграций/ролей, вне текущего скоупа — нужен `db-architect`/`laravel-fullstack` фикс отдельной задачей (создать `CustomerFactory`), иначе демо-сидер `Customers`/`Leads` не работает в `local`/`development`.
- `php artisan test`: 1 из 2 тестов красный — `ExampleTest::test_the_application_returns_a_successful_response` ожидает 200 от `/`, получает 302 (роут `/` делает `redirect('/admin')`, это уже так в `routes/web.php`, не мной введено). Не блокирующая, pre-existing; `qa-tester` в курсе.
- **Влияние**: любой поток/агент, который читает `config/permissions.php` (сайдбар, Setup-компонент, RolesSeeder) — теперь получает реальные данные, а не пустой массив. Роли/права в БД актуальны и соответствуют `CLAUDE.md` §2.4 + фактическому использованию permission-строк в коде.
- **Открытый вопрос для координатора/db-architect**: нужно создать `database/factories/CustomerFactory.php`, иначе `DemoLeadsSeeder` (и любой другой сидер/тест, использующий `Customer::factory()`) будет падать.

## 2026-07-06 — [qa-tester] Baseline Feature tests добавлены
- Созданы недостающие фабрики (были только `UserFactory`): `database/factories/CustomerFactory.php`, `LeadFactory.php`, `QuoteFactory.php` (со стейтом `accepted()`), `InvoiceFactory.php` — все реалистичные, поля сверены с миграциями (не с устаревшим `data-contracts.md`, где часть полей КП/Инвойса — `discount_amount`/`vat_amount` вместо реальных `discount_total`/`tax_rate`/`tax_amount` — уже разошлась с кодом).
- `tests/Feature/ExampleTest.php` — переписан: `/` реально делает `redirect('/admin')` (302), стаб про `assertStatus(200)` больше не актуален.
- Написаны 4 новых test-файла (27 тестов, 59 assertions):
  - `tests/Feature/Leads/LeadCreationTest.php` — создание лида через `Leads\CreateForm` (happy path + auto-assign менеджера), обязательные `name`/`phone`, невалидный email, 403 для роли без `leads.create` (tech-support).
  - `tests/Feature/Quotes/QuoteToInvoiceConversionTest.php` — accepted Quote → Invoice (`Quotes\Show::convertToInvoice`): суммы/НДС 12%/due_date по `payment_terms_days`/итемы переносятся; повторная конвертация не создаёт 2-й инвойс; draft-КП не конвертируется; чужой sales-manager получает 403. Плюс тест, документирующий баг (см. ниже).
  - `tests/Feature/Invoices/InvoiceGenerationTest.php` — итемы/итоги инвойса, частичная оплата → `partially_paid`, полная → `paid`, накопление двух платежей, `paymentAmount` не может быть 0.
  - `tests/Feature/Access/AccessControlTest.php` — guest→login redirect, client-user не пускает в `/admin`, internal-роль не пускает в `/portal`, sales-manager видит только свои лиды (Index list) и получает 403 на чужом лиде (Show), sales-director видит любые, CustomerPolicy ownership через `customer_users` pivot, IDOR-тест на Invoices\Show (см. баг ниже).
- **Результат `php artisan test`**: 27 tests, 17 passed, **10 errors** (все 10 — в `AccessControlTest`, один и тот же root cause, см. баг №1 ниже). Assertions: 59.
- **БАГ №1 (критичный, блокирует буквально всё приложение) → `laravel-fullstack`**: `role:...` middleware (Spatie) нигде не зарегистрирован как алиас. `app/Http/Kernel.php` объявляет `'role' => \Spatie\Permission\Middleware\RoleMiddleware::class`, но `bootstrap/app.php` — это Laravel 11+/13 стиль (`Application::configure()->withMiddleware(fn ($middleware) => ...)` с пустым телом), который **не читает** `app/Http/Kernel.php` вообще — файл мёртвый. Итог: любой роут с `role:...` middleware (то есть буквально все `/admin/*` и `/portal/*` из `routes/web.php`) валится с `BindingResolutionException: Target class [role] does not exist.`, как только запрос проходит `auth` (для гостя срабатывает редирект на `/login` раньше, чем pipeline доходит до `role`, поэтому баг незаметен без авторизации). Проверено вручную вне тестов: авторизованный пользователь, открывающий `/admin`, получает реальный **500**, а не 403/200. Это не гипотетический баг — сейчас всё приложение недоступно ни одной роли. Фикс: зарегистрировать алиасы (`role`, `permission`, `role_or_permission`, `internal`, `client`) через `$middleware->alias([...])` в `bootstrap/app.php`, затем удалить неиспользуемый `app/Http/Kernel.php`. 10 тестов в `AccessControlTest` намеренно оставлены падающими — это и есть документация бага.
- **БАГ №2 → `laravel-fullstack`**: `App\Models\Quote\QuoteItem::$fillable` не включает `final_price`, а колонка `quote_items.final_price` — `NOT NULL` без дефолта (`database/migrations/2026_04_28_150100_create_quote_items_table.php`). И `App\Livewire\Admin\Documents\CreateForm::saveQuote()`, и `App\Livewire\Admin\Quotes\CreateForm::save()` создают итемы через `$quote->items()->create([..., 'final_price' => ...])` — значение молча отбрасывается mass-assignment guard'ом, и INSERT падает с `QueryException` (NOT NULL constraint). То есть создание ЛЮБОГО нового КП с позициями через реальный UI (единая форма КП/Инвойс из Phase 10) сейчас сломано. Задокументировано тестом `QuoteToInvoiceConversionTest::test_creating_quote_items_via_mass_assignment_fails_due_to_missing_final_price_fillable` (воспроизводит через реальный Livewire-компонент, ловит `QueryException`). Фикс: добавить `'final_price'` в `QuoteItem::$fillable`.
- **БАГ №3 (IDOR) → `laravel-fullstack`**: `App\Livewire\Admin\Invoices\Show::mount()` не вызывает `$this->authorize('view', $invoice)` (в отличие от `Leads\Show` и `Quotes\Show`, которые оба авторизуют на mount), и `InvoiceController::show()` тоже не авторизует. `InvoicePolicy::view()` реализован правильно ("свои инвойсы только"), и `Invoices\Index` его корректно фильтрует по `manager_id` — но прямой переход по URL `/admin/invoices/{id}` открывает ЛЮБОЙ инвойс любому internal-пользователю, прошедшему только route-level `role:...` (когда баг №1 будет исправлен). Задокументировано тестом `AccessControlTest::test_sales_manager_cannot_view_another_managers_invoice` (кодирует ожидаемое поведение — 403 для чужого инвойса; сейчас фактически 200, но замаскировано багом №1 до его починки). Фикс: добавить `$this->authorize('view', $invoice);` в `Invoices\Show::mount()`.
- **Влияние на другие агенты**: `laravel-fullstack` должен исправить баги №1–3 (см. выше) — без фикса бага №1 приложение нерабочее в принципе, приоритет максимальный. После фикса бага №1 нужно перезапустить `php artisan test` — ожидаемо все 10 тестов `AccessControlTest` станут зелёными (кроме теста на баг №3, который так и останется падать до отдельного фикса Invoices\Show). `db-architect`/поддерживающий `data-contracts.md` — контракт для Quote/Invoice разошёлся с реальными миграциями (`discount_total`/`tax_rate`/`tax_amount`/`shipment_status` вместо документированных `discount_amount`/`vat_percent`(на invoice)/`balance`) — стоит актуализировать при следующей правке.

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

## 2026-07-06 — [admin-bi-developer] Сайдбар admin-панели стал сворачиваемым (collapsed icon-rail)

- `resources/views/layouts/admin.blade.php` — в `x-data` на `<body>` добавлено состояние `sidebarCollapsed` (инициализируется из `localStorage.getItem('rsg-admin-sidebar-collapsed')`) и метод `toggleSidebarCollapsed()`, пишущий значение обратно в `localStorage` — состояние переживает переход между страницами (обычные full page reload, не `wire:navigate`). Ширина `<aside>` (`w-64` ↔ `lg:w-20`) и левый паддинг контентной обёртки (`lg:pl-64` ↔ `lg:pl-20`) теперь биндятся через Alpine `:class` вместо статичных классов; добавлен `transition-all duration-200` на обоих элементах для плавной анимации. Мобильное off-canvas поведение (`sidebarOpen`, `< lg`) не тронуто — collapse-режим применяется только на `lg+`.
- `resources/views/admin/partials/header.blade.php` — новая кнопка-тоггл (`hidden lg:inline-flex`, иконка chevron-double, разворачивается на 180° через `:class="sidebarCollapsed ? 'rotate-180' : ''"`) рядом с мобильным гамбургером; вызывает `toggleSidebarCollapsed()` из родительского Alpine-скоупа.
- `resources/views/admin/partials/sidebar.blade.php`:
  - Логотип: текстовый блок (`RSG-CRM` + подпись) скрывается в collapsed-режиме через `:class="sidebarCollapsed ? 'lg:hidden' : ''"`, иконка-квадрат остаётся.
  - `$navLink()` (генерирует пункты меню): каждый пункт получил `title="{label}"` (нативный tooltip — важно для icon-only режима, per требование задачи), label-`<span>` скрывается в collapsed через `:class="sidebarCollapsed ? 'lg:hidden' : ''"`, сама ссылка получает `lg:justify-center lg:px-2` для центрирования иконки.
  - Новый helper `$sectionLabel()` — рендерит заголовок секции меню ("Продажи", "Каталог" и т.д.) + скрытый по умолчанию `<div>`-разделитель; в collapsed-режиме текст прячется (`lg:hidden`), а разделитель показывается (`lg:block`) — секции остаются визуально разграничены даже без текста.
  - Карточка пользователя внизу: имя/роль скрываются в collapsed (`lg:hidden`), аватар получил `title` с именем и ролью, кнопка logout остаётся видимой в обоих режимах (не скрыта — важно для доступности).
  - Все permission-gated блоки (`@if(\App\Helpers\Acl::can(...))`) не тронуты — видимость пунктов по правам работает идентично в развёрнутом и свёрнутом состоянии.
- `.claude/artifacts/admin-panel/design-system.md` — раздел "Responsive breakpoints" дополнен описанием collapse-конвенции (Alpine state, localStorage key, `lg:hidden`-паттерн для скрытия текста, tooltip через `title`) для будущих UI-задач.
- Проверено: `php artisan view:cache` (полная прекомпиляция всех Blade-шаблонов проекта) прошла без ошибок — синтаксис директив/скобок корректен; кеш очищен обратно (`view:clear`).
- Не трогал: миграции, Livewire PHP-логику, роуты, permissions — чисто Blade/Alpine/Tailwind слой.

## 2026-07-06 — [qa-tester] Feature-тесты для конвертации EquipmentRequest → Quote и треда комментариев

- `tests/Feature/Tickets/EquipmentRequestConversionTest.php` (новый, 5 тестов) — покрывает `App\Livewire\Admin\EquipmentRequests\Show::convertToQuote()`: создание черновика Quote с `equipment_request_id`/`customer_id`/статусом `draft`/номером формата `КП-YYYY-####`/непустыми `notes`, авто-переход заявки в статус `quoted`, редирект на `admin.quotes.edit` новой Quote; идемпотентность (повторный вызов не создаёт вторую Quote, редиректит на существующую); фоллбэк `manager_id` (берётся из `EquipmentRequest.manager_id`, если назначен, иначе `auth()->id()`); подтверждение, что роль без `equipment-requests.view` (`sales-manager`) блокируется на уровне `mount()` и не может ничего сконвертировать.
- `tests/Feature/Tickets/EquipmentRequestCommentTest.php` (новый, 6 тестов) — покрывает новую модель `EquipmentRequestComment` и `addComment()` на Admin/Portal `Show`: сотрудник может оставить внутреннюю (`is_internal=true`) и публичную (`is_internal=false`) заметку; внутренние комментарии не попадают в `publicComments()` и не отображаются в Portal `Show` (`assertDontSee`/`assertSee`); ответ клиента из портала всегда создаётся с `is_internal=false`; на Portal `Show` физически нет публичного свойства-тумблера для пометки комментария внутренним (`set('isInternal', ...)` кидает `PublicPropertyNotFoundException`); клиент чужой компании по-прежнему получает 403 на `mount()` и не может достучаться до формы комментария.
- Использован существующий паттерн `makeClientUser()`/`customer_users` pivot из `EquipmentRequestPortalCreationTest.php`, дублирования фабрик/хелперов не создавалось.
- Багов в новой логике конвертации/комментариев не обнаружено — вся продуктовая логика (идемпотентность, фоллбэк менеджера, изоляция internal/public комментариев, отсутствие toggle на портале) ведёт себя как описано в спецификации.
- Итог прогона `php artisan test`: **132 passed** (было 121 до задачи, +11 новых тестов), 0 failed.
