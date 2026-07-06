# Data Contracts — Структуры моделей RSG-CRM

**Owner**: database stream (только они меняют этот файл)
**Readers**: все потоки

Финальный список полей моделей. Источник истины при проектировании форм, валидации, API.

**Принципы**:
- Single-tenant (нет `tenant_id`)
- Soft deletes для критичных сущностей (`deleted_at`)
- UZS как основная валюта (`decimal(15,2)`), USD — отдельные поля
- Snake_case для таблиц и колонок
- Все таблицы во множественном числе (`customers`, не `customer`)
- На FK — `onDelete('cascade'|'set null'|'restrict')` обязательно

---

# 📋 Содержание

Always change migration file never creat add_ or extend_ migrations

1. [Auth & Users](#auth--users) — User, Spatie tables
2. [Customers](#customers) — Customer, Contact, BusinessType (справочник)
3. [Leads](#leads) — Lead, LeadSource, LeadActivity
4. [Quotes (КП)](#quotes) — Quote, QuoteItem, QuoteVersion
5. [Invoices](#invoices) — Invoice, InvoiceItem, Payment
6. [Catalog](#catalog) — ProductGroup, Category, CategoryAttribute, Product, ProductTranslation, ProductAttributeValue, ProductPrice, ProductStock, ProductImage, ProductAttachment, BusinessTypeRecommendation
7. [Equipment Requests](#equipment-requests) — EquipmentRequest, RequestCategory
8. [Tickets](#tickets) — Ticket, TicketComment, TicketAttachment, TicketCategory
9. [Product Returns](#product-returns) — ProductReturn, ReturnItem
10. [System](#system) — Setting, AuditLog, Notification

---

## Auth & Users

### `users`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(255) | no | — | ФИО |
| email | string(255) | no | — | unique |
| phone | string(20) | yes | null | |
| telegram_id | string(20) | yes | null | |
| password | string | no | — | hashed |
| email_verified_at | timestamp | yes | null | |
| is_active | boolean | no | true | |
| avatar_path | string(255) | yes | null | путь в storage |
| last_login_at | timestamp | yes | null | |
| remember_token | string(100) | yes | null | |
| timestamps | | | | |

**Индексы**: `email` UNIQUE, `is_active`
**Связи**: `roles()` (HasRoles trait), `assignedLeads()`, `managedCustomers()`, `assignedTickets()`
**Используется в**: все модули

### Spatie Permission tables (генерируются пакетом)
- `roles` — список ролей
- `permissions` — список прав
- `model_has_roles`, `model_has_permissions`, `role_has_permissions` — связи

---

## Customers

### `business_types` (справочник типов бизнеса)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(100) | no | — | "Магазин", "Ресторан", "Аптека" |
| slug | string(50) | no | — | UNIQUE: shop / restaurant / pharmacy / warehouse |
| is_active | boolean | no | true | |
| sort_order | int | no | 0 | |
| timestamps | | | | |

**Сидер (стартовый)**: shop / restaurant / pharmacy / warehouse / supermarket / cafe / other
**Используется**: customers.business_type_id, leads.business_type_id

### `customers` (компании-клиенты)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(255) | no | — | Название компании |
| legal_name | string(255) | yes | null | Юр. название (ООО, ИП и т.д.) |
| inn | string(20) | yes | null | UNIQUE (если не null) |
| oked | string(20) | yes | null | ОКЭД код |
| business_type_id | bigint | yes | null | FK business_types, restrict |
| segment | string(10) | no | 'B' | A / B / C |
| status | string(20) | no | 'active' | active / vip / inactive / blocked |
| region | string(100) | yes | null | |
| city | string(100) | yes | null | |
| address | text | yes | null | юр. адрес |
| phone | string(20) | yes | null | |
| email | string(255) | yes | null | |
| website | string(255) | yes | null | |
| bank_id | bigint | yes | null | FK banks(id), onDelete: set null |
| bank_account | string(20) | yes | null | р/с (20 цифр) |
| credit_limit | decimal(15,2) | yes | null | UZS |
| payment_terms_days | int | yes | null | дни отсрочки |
| customer_since | date | yes | null | дата первой сделки |
| notes | text | yes | null | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

**Индексы**: `inn` UNIQUE, `status`, `bank_id`, `business_type_id`, `segment`
**Связи**: `bank()` (BelongsTo Bank), `businessType()` (BelongsTo BusinessType), `contacts()` (HasMany), `users()` (BelongsToMany через customer_users), `leads()`, `quotes()`, `invoices()`, `tickets()`, `orders()`

### `banks` (банки)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(255) | no | — | Название банка |
| mfo | string(10) | yes | null | UNIQUE. МФО код банка |
| is_active | boolean | no | true | |
| timestamps | | | | |

**Индексы**: `mfo` UNIQUE, `is_active`
**Модель**: `App\Models\Customer\Bank`

### `customer_users` (пользователи клиента с ролями)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| customer_id | bigint | no | — | FK customers, cascade |
| user_id | bigint | no | — | FK users, cascade |
| role | string(20) | no | 'manager' | owner / manager / viewer |
| timestamps | | | | |

**Индексы**: UNIQUE(customer_id, user_id), `user_id`
**Модель**: `App\Models\Customer\CustomerUser`

### `contacts` (контактные лица)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| customer_id | bigint | no | — | FK customers, cascade |
| name | string(255) | no | — | ФИО |
| position | string(100) | yes | null | Директор, Закупщик и т.д. |
| phone | string(20) | yes | null | |
| email | string(255) | yes | null | |
| is_primary | boolean | no | false | главное контактное лицо |
| notes | text | yes | null | |
| timestamps | | | | |

**Индексы**: `customer_id`, `email`, `phone`
**Связи**: `customer()` (BelongsTo Customer)

---

## Leads

### `lead_sources` (справочник источников)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(100) | no | — | "Сайт rsg.uz", "Звонок", "Реклама" |
| slug | string(50) | no | — | UNIQUE |
| is_active | boolean | no | true | |
| sort_order | int | no | 0 | |
| timestamps | | | | |

**Сидер**: site, call, ad, exhibition, referral, cold-call

### `leads`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(255) | no | — | Имя контакта |
| company | string(255) | yes | null | Если ещё не Customer |
| customer_id | bigint | yes | null | FK customers, set null. Если конвертирован |
| phone | string(20) | no | — | |
| email | string(255) | yes | null | |
| source_id | bigint | no | — | FK lead_sources, restrict |
| status | string(20) | no | 'new' | new / qualified / contacted / in_negotiation / won / lost |
| score | tinyint | yes | null | 1-10 |
| budget | decimal(15,2) | yes | null | UZS |
| business_type_id | bigint | yes | null | FK business_types, set null |
| region | string(100) | yes | null | |
| manager_id | bigint | no | — | FK users (assigned_to), restrict |
| converted_at | timestamp | yes | null | когда стал customer |
| won_amount | decimal(15,2) | yes | null | сумма сделки если won |
| lost_reason | string(50) | yes | null | price / timing / competitor / other |
| notes | text | yes | null | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

**Индексы**: `status`, `source_id`, `manager_id`, `customer_id`, `business_type_id`, `phone` (для дедупликации)
**Связи**: `source()`, `manager()`, `customer()`, `businessType()`, `activities()`, `quotes()`, `convertedBy()` (если конвертация — кто это сделал)

### `lead_activities` (timeline лида)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| lead_id | bigint | no | — | FK leads, cascade |
| type | string(20) | no | — | call / email / meeting / note / status_change / quote_sent |
| title | string(255) | yes | null | краткий заголовок |
| description | text | yes | null | детали |
| meta | json | yes | null | для type=status_change хранит {from, to} |
| user_id | bigint | yes | null | кто создал, set null |
| created_at | timestamp | no | now | |
| updated_at | timestamp | no | now | |

**Индексы**: `lead_id`, `type`, `created_at`
**Связи**: `lead()`, `user()`

---

## Quotes

> ⚠️ Секция актуализирована 2026-07-06 [laravel-fullstack] по факту реальных миграций/моделей — предыдущая версия описывала поля (`discount_amount`, `lead_id` на quotes, `line_total`), которых нет в коде. См. `database/migrations/2026_04_28_150000_create_quotes_table.php`, `..._150100_create_quote_items_table.php`, `..._150200_create_quote_versions_table.php`.

### `quotes` (КП)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| number | string(50) | no | — | UNIQUE |
| customer_id | bigint | no | — | FK customers, restrict |
| manager_id | bigint | yes | null | FK users, set null |
| contact_id | bigint | yes | null | FK contacts, set null |
| currency | string(3) | no | 'UZS' | UZS / USD |
| exchange_rate | decimal(10,4) | no | 1 | UZS per 1 USD на момент создания |
| issue_date | date | yes | null | |
| status | string(30) | no | 'draft' | draft / sent / viewed / accepted / rejected / expired |
| valid_until | date | yes | null | срок действия |
| subtotal | decimal(15,2) | no | 0 | сумма позиций до общей скидки |
| discount_percent | decimal(5,2) | no | 0 | общая скидка % (global discount) |
| discount_total | decimal(15,2) | no | 0 | абсолютная сумма общей скидки (**не** `discount_amount`) |
| vat_percent | decimal(5,2) | no | 0 | НДС % (UZ = 12; дефолт колонки 0, выставляется в коде) |
| vat_amount | decimal(15,2) | no | 0 | сумма НДС |
| total | decimal(15,2) | no | 0 | финальная сумма |
| version | smallint unsigned | no | 1 | для версионирования |
| notes | text | yes | null | внутренние заметки |
| terms | text | yes | null | условия оплаты, сроки, гарантия |
| sent_at | timestamp | yes | null | |
| viewed_at | timestamp | yes | null | первый просмотр клиентом |
| accepted_at | timestamp | yes | null | |
| rejected_at | timestamp | yes | null | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

Нет колонок `lead_id` и `pdf_path` в реальной схеме (PDF генерируется on-the-fly через `PdfService`, не сохраняется).

**Индексы**: `number` UNIQUE, `customer_id`, `manager_id`, `status`
**Связи** (`App\Models\Quote\Quote`): `customer()`, `manager()` (`User`, FK `manager_id`), `items()` (HasMany QuoteItem, ordered by `sort_order`), `versions()` (HasMany QuoteVersion, latest first), `invoice()` (HasOne Invoice — один Invoice на Quote, не HasMany)
**Scopes**: `scopeForUser($userId)` (по `manager_id`), `scopeByStatus($status)`
**Прочее**: `isEditable()` — true только для статуса `draft`

### `quote_items` (позиции КП)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| quote_id | bigint | no | — | FK quotes, cascade |
| product_id | bigint | yes | null | FK products, set null (если товар удалён) |
| name | string(255) | no | — | копия названия (snapshot) |
| sku | string(100) | yes | null | копия SKU на момент создания |
| description | text | yes | null | |
| quantity | int unsigned | no | 1 | |
| unit_price | decimal(15,2) | no | — | цена за единицу |
| discount_percent | decimal(5,2) | no | 0 | скидка на позицию, % |
| final_price | decimal(15,2) | no | — | **NOT NULL, без дефолта.** Цена за единицу после применения скидки на позицию. Должен быть в `$fillable`/`$casts` модели (исправлено 2026-07-06 — раньше отсутствовал в `$fillable`, что ломало создание любого КП с позициями через mass-assignment) |
| total | decimal(15,2) | no | — | итог по строке (**не** `line_total`) |
| sort_order | smallint unsigned | no | 0 | |
| timestamps | | | | |

**Индексы**: `quote_id`, `product_id`
**Связи**: `quote()`, `product()`

### `quote_versions` (история версий КП)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| quote_id | bigint | no | — | FK quotes, cascade |
| version | smallint unsigned | no | — | номер версии |
| items_snapshot | json | no | — | снимок позиций (**не** `snapshot`) |
| total | decimal(15,2) | no | — | итог версии |
| created_by | bigint | no | — | FK users (restrict, не set null) |
| timestamps | | | | |

**Индексы**: `quote_id`
**Связи**: `quote()`, `creator()` (`User`, FK `created_by`)

---

## Invoices

> ⚠️ Секция актуализирована 2026-07-06 [laravel-fullstack] по факту реальных миграций/моделей. См. `database/migrations/2026_04_28_150300_create_invoices_table.php`, `..._150400_create_invoice_items_table.php`, `..._150500_create_payments_table.php`.

### `invoices`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| number | string(50) | no | — | UNIQUE |
| agreement_number | string | yes | null | номер договора для бухгалтерии (не в `$fillable` модели — пока не используется в коде) |
| batch_number | int | no | 1 | номер партии/спецификации для выгрузки в 1С (не в `$fillable` модели) |
| quote_id | bigint | yes | null | FK quotes, set null (если из КП) |
| customer_id | bigint | no | — | FK customers, restrict |
| manager_id | bigint | yes | null | FK users, set null |
| currency | string(3) | no | 'UZS' | |
| exchange_rate | decimal(10,4) | no | 1 | |
| status | string(30) | no | 'draft' | draft / sent / partially_paid / paid / overdue / cancelled |
| due_date | date | yes | null | срок оплаты |
| subtotal | decimal(15,2) | no | 0 | |
| tax_rate | decimal(5,2) | no | 12 | НДС %, UZ (**не** `vat_percent`) |
| tax_amount | decimal(15,2) | no | 0 | сумма НДС (**не** `vat_amount`) |
| total | decimal(15,2) | no | 0 | |
| paid_amount | decimal(15,2) | no | 0 | сумма оплачено |
| shipment_status | string(20) | no | 'none' | none / partial / complete — статус отгрузки (связка с модулем Sells; нет полей `balance`/`paid_at`/`cancelled_at`/`cancel_reason`/`pdf_path` — их не существует) |
| notes | text | yes | null | |
| sent_at | timestamp | yes | null | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

**Индексы**: `number` UNIQUE, `customer_id`, `manager_id`, `status`, `quote_id`
**Связи** (`App\Models\Invoice\Invoice`): `customer()`, `manager()`, `quote()` (BelongsTo), `items()` (HasMany InvoiceItem, ordered by `sort_order`), `payments()` (HasMany, ordered by `paid_at`), `sells()` (HasMany `App\Models\Sell\Sell`, ordered by `sold_at`), `productReturns()` (HasMany `App\Models\Sell\ProductReturn`, FK `invoice_id`)
**Accessors**: `remaining` — `total - paid_amount` (bcsub, computed on the fly, не хранится в колонке `balance`)
**Scopes**: `scopeOverdue()` (due_date < now, статус не paid/cancelled), `scopeForUser($userId)` (по `manager_id`)
**Авторизация**: `InvoicePolicy::view()` — ownership по `manager_id` для sales-manager; проверяется в `Livewire\Admin\Invoices\Show::mount()` (добавлено 2026-07-06 — ранее отсутствовало, IDOR)

### `invoice_items`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| invoice_id | bigint | no | — | FK invoices, cascade |
| product_id | bigint | yes | null | FK products, set null |
| name | string(255) | no | — | |
| sku | string(100) | yes | null | |
| quantity | int unsigned | no | 1 | |
| unit_price | decimal(15,2) | no | — | |
| tax_rate | decimal(5,2) | no | 0 | НДС на позицию (нет `discount_percent`/`final_price` — в отличие от `quote_items`) |
| total | decimal(15,2) | no | — | итог по строке (**не** `line_total`) |
| sort_order | smallint unsigned | no | 0 | |
| timestamps | | | | |

**Индексы**: `invoice_id`
**Связи**: `invoice()`, `product()`

### `payments`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| invoice_id | bigint | no | — | FK invoices, cascade |
| amount | decimal(15,2) | no | — | |
| currency | string(3) | no | 'UZS' | |
| paid_at | date | no | — | дата платежа |
| method | string(30) | no | 'bank_transfer' | bank_transfer / cash / card (**не** `'bank'`) |
| fiscal | string(200) | yes | null | фискальный чек/QR (не в `$fillable` модели — не используется в коде пока) |
| reference | string(255) | yes | null | номер платёжки |
| notes | text | yes | null | |
| recorded_by | bigint | yes | null | FK users, set null (**не** `created_by`) |
| timestamps | | | | |

**Индексы**: `invoice_id`, `paid_at`, `recorded_by`
**Связи**: `invoice()`, `recordedBy()` (`User`, FK `recorded_by`)

---

## Catalog

### `product_groups` (группы категорий)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name_ru | string(100) | no | — | "POS-оборудование" |
| name_uz | string(100) | yes | null | |
| description | text | yes | null | |
| color | string(20) | no | 'gray' | gray / blue / green / orange — для UI |
| sort_order | smallint unsigned | no | 0 | |
| is_active | boolean | no | true | |
| timestamps | | | | |

**Индексы**: `sort_order`
**Связи**: `categories()` (HasMany Category)
**Сидер**: ProductGroupsSeeder — 4 группы (POS-оборудование/Торговое оборудование/POS-материалы/Расходные материалы)
**Модель**: `App\Models\Catalog\ProductGroup`

### `categories` (иерархия товаров)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| parent_id | bigint | yes | null | FK categories (self), set null |
| group_id | bigint | yes | null | FK product_groups, set null |
| name_ru | string(255) | no | — | "POS", "Весы" |
| name_uz | string(255) | yes | null | |
| slug | string(255) | no | — | UNIQUE |
| description | text | yes | null | |
| icon | string(50) | yes | null | |
| sort_order | int | no | 0 | |
| is_active | boolean | no | true | |
| timestamps | | | | |

**Индексы**: `parent_id`, `group_id`, `slug` UNIQUE, `is_active`
**Связи**: `parent()` (BelongsTo self), `children()` (HasMany self), `products()`, `group()` (BelongsTo ProductGroup)

### `category_attributes` (определение полей характеристик для категории)

Каждая категория задаёт свой набор атрибутов товара (для POS — процессор/RAM, для весов — макс. вес/точность).

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| category_id | bigint | yes | null | FK categories, cascade. NULL = глобальный (применяется ко всем) |
| key | string(50) | no | — | slug (processor / max_weight / accuracy) |
| name | string(100) | no | — | "Процессор", "Макс. вес" |
| type | string(20) | no | 'text' | text / number / select / boolean / date |
| options | json | yes | null | для type=select: ["AMD","Intel"] |
| unit | string(20) | yes | null | "кг", "GHz", "GB" |
| is_required | boolean | no | false | |
| is_filter | boolean | no | false | использовать для фильтра в каталоге |
| sort_order | int | no | 0 | |
| timestamps | | | | |

**Индексы**: `category_id`, `key`
**Связи**: `category()`, `productValues()`

### `products`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| category_id | bigint | no | — | FK categories, restrict |
| sku | string(100) | no | — | UNIQUE |
| name | string(255) | no | — | RU-дефолт (другие языки в product_translations) |
| brand | string(100) | yes | null | |
| description | text | yes | null | RU-дефолт |
| unit | string(20) | no | 'шт' | шт / комплект / м |
| warranty_months | int | yes | null | |
| is_active | boolean | no | true | опубликован |
| is_visible_in_portal | boolean | no | true | виден в кабинете клиента |
| is_serial | boolean | no | false | true = учёт по серийным номерам; false = количественный |
| sort_order | int | no | 0 | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

**Индексы**: `category_id`, `sku` UNIQUE, `is_active`, `is_visible_in_portal`, `is_serial`
**Связи**: `category()`, `translations()`, `attributeValues()`, `prices()`, `stocks()`, `images()`, `attachments()`, `relatedProducts()` (BelongsToMany self), `serials()` (HasMany ProductSerial), `recommendations()` (HasMany BusinessTypeRecommendation)

### `product_translations` (i18n)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascade |
| locale | string(5) | no | — | ru / uz |
| name | string(255) | no | — | |
| description | text | yes | null | |
| timestamps | | | | |

**Индексы**: `product_id, locale` UNIQUE
**Связи**: `product()`

**Поведение**: products.name содержит RU-дефолт. product_translations.name(locale='uz') — узбекская версия. Для RU можно тоже хранить в translations для единообразия, либо использовать fallback из products.

### `product_attribute_values` (значения характеристик для товара)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascade |
| category_attribute_id | bigint | no | — | FK category_attributes, cascade |
| value | text | yes | null | строковое значение (универсально) |
| value_number | decimal(15,4) | yes | null | для type=number — для фильтрации/сортировки |
| timestamps | | | | |

**Индексы**: `product_id`, `category_attribute_id`, `value_number`
**UNIQUE**: `(product_id, category_attribute_id)`
**Связи**: `product()`, `attribute()` (BelongsTo CategoryAttribute)

### `product_prices` (многоуровневые цены)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascade |
| price_type | string(20) | no | — | cost / retail / wholesale / special |
| customer_id | bigint | yes | null | FK customers (для special), cascade |
| min_quantity | int | no | 1 | для wholesale |
| price | decimal(15,2) | no | — | |
| currency | string(3) | no | 'UZS' | |
| valid_from | date | yes | null | |
| valid_until | date | yes | null | |
| timestamps | | | | |

**Индексы**: `product_id, price_type`, `customer_id`
**Связи**: `product()`, `customer()`

### `product_stocks` (остатки по складам)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascade |
| warehouse | string(50) | no | 'main' | главный / чорсу / другой |
| quantity | int | no | 0 | |
| reserved | int | no | 0 | в резерве по открытым КП/инвойсам |
| timestamps | | | | |

**Индексы**: `product_id, warehouse` UNIQUE
**Связи**: `product()`

### `product_images`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascade |
| path | string(255) | no | — | путь в storage |
| alt | string(255) | yes | null | |
| is_main | boolean | no | false | главное изображение |
| sort_order | int | no | 0 | |
| timestamps | | | | |

### `business_type_recommendations` (рекомендуемые товары по типу бизнеса)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| business_type_id | bigint | no | — | FK business_types, cascade |
| product_id | bigint | no | — | FK products, cascade |
| priority | string(20) | no | 'recommended' | required / recommended / optional |
| sort_order | smallint unsigned | no | 0 | |
| notes | text | yes | null | |
| timestamps | | | | |

**Индексы**: UNIQUE(`business_type_id`, `product_id`), INDEX(`business_type_id`, `priority`)
**Связи**: `businessType()` (BelongsTo BusinessType), `product()` (BelongsTo Product)
**Scopes**: `scopeRequired()`, `scopeRecommended()`
**Сидер**: BusinessTypeRecommendationsSeeder — рекомендации для shop/supermarket/restaurant/cafe/pharmacy/warehouse
**Модель**: `App\Models\Catalog\BusinessTypeRecommendation`

### `product_attachments` (паспорта, инструкции)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascade |
| name | string(255) | no | — | "Паспорт", "Инструкция" |
| path | string(255) | no | — | |
| size | bigint | no | 0 | bytes |
| mime_type | string(100) | yes | null | |
| timestamps | | | | |

### `product_serials` (серийные номера единиц товара)

Используется только для товаров с `is_serial = true`. Содержит денормализованный кеш текущего статуса и владельца для быстрых запросов; полная история хранится в `serial_statuses` и `serial_owners`.

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| product_id | bigint | no | — | FK products, cascadeOnDelete |
| serial_number | string(100) | no | — | серийный номер единицы |
| current_status | string(20) | no | 'available' | available / sold / returned / in_repair |
| current_owner_id | bigint | yes | null | FK customers, nullOnDelete |
| notes | text | yes | null | |
| timestamps | | | | |

**Индексы**: UNIQUE(`product_id`, `serial_number`), `current_status`, `current_owner_id`
**Связи**: `product()` (BelongsTo Product), `currentOwner()` (BelongsTo Customer), `tickets()` (HasMany Ticket), `statusHistory()` (HasMany SerialStatus), `ownerHistory()` (HasMany SerialOwner)
**Scopes**: `scopeAvailable()`, `scopeSold()`, `scopeReturned()`
**Модель**: `App\Models\Catalog\ProductSerial`

### `serial_statuses` (лог переходов статусов серийного номера)

Append-only лог — только `created_at`, без `updated_at`.

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| serial_id | bigint | no | — | FK product_serials, cascadeOnDelete |
| status | string(20) | no | — | available / sold / returned / in_repair |
| changed_by | bigint | yes | null | FK users, nullOnDelete |
| notes | text | yes | null | |
| created_at | timestamp | no | now | useCurrent |

**Индексы**: `serial_id`, `status`
**Связи**: `serial()` (BelongsTo ProductSerial), `changedBy()` (BelongsTo User)
**Модель**: `App\Models\Catalog\SerialStatus`

### `serial_owners` (история владения серийным номером)

`released_at = null` означает текущего владельца.

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| serial_id | bigint | no | — | FK product_serials, cascadeOnDelete |
| customer_id | bigint | no | — | FK customers, cascadeOnDelete |
| sell_item_id | bigint | yes | null | FK sell_items, nullOnDelete |
| return_item_id | bigint | yes | null | без FK — return_items создаётся позже |
| acquired_at | timestamp | no | — | |
| released_at | timestamp | yes | null | null = текущий владелец |
| timestamps | | | | |

**Индексы**: `serial_id`, `customer_id`, `released_at`
**Связи**: `serial()` (BelongsTo ProductSerial), `customer()` (BelongsTo Customer)
**Модель**: `App\Models\Catalog\SerialOwner`

---

## Equipment Requests

### `request_categories` (справочник)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(100) | no | — | |
| slug | string(50) | no | — | UNIQUE |
| is_active | boolean | no | true | |
| timestamps | | | | |

### `equipment_requests`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| number | string(50) | no | — | UNIQUE. ER-YYYY-NNNN |
| customer_id | bigint | no | — | FK customers, restrict |
| contact_id | bigint | yes | null | FK contacts, set null |
| category_id | bigint | yes | null | FK request_categories, set null |
| title | string(255) | no | — | |
| description | text | no | — | |
| budget | decimal(15,2) | yes | null | |
| deadline | date | yes | null | |
| status | string(20) | no | 'submitted' | submitted / under_review / quoted / closed |
| manager_id | bigint | yes | null | FK users, set null |
| quote_id | bigint | yes | null | FK quotes — если конвертирован, set null |
| closed_at | timestamp | yes | null | |
| timestamps | | | | |

**Индексы**: `number` UNIQUE, `customer_id`, `status`, `manager_id`
**Связи**: `customer()`, `contact()`, `category()`, `manager()`, `quote()`, `attachments()` (полиморфно)

---

## Tickets

### `ticket_categories` (справочник)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| name | string(100) | no | — | "Hardware", "Software", "Training" |
| slug | string(50) | no | — | UNIQUE |
| sla_response_hours | int | yes | null | по умолчанию для категории |
| sla_resolution_hours | int | yes | null | |
| is_active | boolean | no | true | |
| timestamps | | | | |

### `tickets`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| number | string(50) | no | — | UNIQUE. T-NNNNN |
| customer_id | bigint | no | — | FK customers, restrict |
| contact_id | bigint | yes | null | FK contacts, set null |
| category_id | bigint | yes | null | FK ticket_categories, set null |
| subject | string(255) | no | — | |
| description | text | no | — | от клиента |
| status | string(20) | no | 'open' | open / in_progress / pending_customer / resolved / closed |
| priority | string(10) | no | 'normal' | low / normal / high / critical |
| assigned_to | bigint | yes | null | FK users, set null |
| sla_due_at | timestamp | yes | null | дедлайн SLA |
| first_response_at | timestamp | yes | null | первый ответ |
| resolved_at | timestamp | yes | null | |
| closed_at | timestamp | yes | null | |
| csat_rating | tinyint | yes | null | 1-5, оценка клиента |
| csat_comment | text | yes | null | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

**Индексы**: `number` UNIQUE, `customer_id`, `status`, `priority`, `assigned_to`, `sla_due_at`
**Связи**: `customer()`, `contact()`, `category()`, `assignee()`, `comments()`, `attachments()`

### `ticket_comments` (переписка)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| ticket_id | bigint | no | — | FK tickets, cascade |
| user_id | bigint | yes | null | FK users, set null (если автор сотрудник) |
| customer_contact_id | bigint | yes | null | FK contacts, set null (если автор клиент) |
| body | text | no | — | |
| is_internal | boolean | no | false | внутренняя заметка (не видна клиенту) |
| timestamps | | | | |

**Индексы**: `ticket_id`, `user_id`

### `ticket_attachments`

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| ticket_id | bigint | no | — | FK tickets, cascade |
| comment_id | bigint | yes | null | FK ticket_comments, set null |
| name | string(255) | no | — | оригинальное имя файла |
| path | string(255) | no | — | путь в storage |
| size | bigint | no | 0 | |
| mime_type | string(100) | yes | null | |
| timestamps | | | | |

---

## Product Returns

### `product_returns` (возвраты товара)

Soft deletes включены. Связан с инвойсом или продажей, из которых был возврат.

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| number | string(50) | no | — | UNIQUE. Format: RET-0001 |
| invoice_id | bigint | yes | null | FK invoices, nullOnDelete |
| sell_id | bigint | yes | null | FK sells, nullOnDelete |
| customer_id | bigint | no | — | FK customers, restrictOnDelete |
| manager_id | bigint | yes | null | FK users, nullOnDelete |
| ticket_id | bigint | yes | null | FK tickets, nullOnDelete |
| reason | string(30) | no | — | warranty / defect / changed_mind / other |
| status | string(20) | no | 'draft' | draft / approved / refunded / cancelled |
| refund_amount | decimal(15,2) | no | 0 | UZS |
| currency | string(3) | no | 'UZS' | |
| notes | text | yes | null | |
| refunded_at | timestamp | yes | null | |
| deleted_at | timestamp | yes | null | soft delete |
| timestamps | | | | |

**Индексы**: `number` UNIQUE, `customer_id`, `sell_id`, `status`, `ticket_id`
**Связи**: `customer()`, `manager()`, `sell()`, `invoice()`, `ticket()`, `items()` (HasMany ReturnItem)
**Модель**: `App\Models\Sell\ProductReturn`

### `return_items` (позиции возврата)

Хранит snapshot названия и SKU товара на момент возврата.

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| return_id | bigint | no | — | FK product_returns, cascadeOnDelete |
| product_id | bigint | yes | null | FK products, nullOnDelete |
| name | string(255) | no | — | snapshot названия товара |
| sku | string(100) | yes | null | snapshot SKU |
| quantity | decimal(10,3) | no | 1 | |
| serial_id | bigint | yes | null | FK product_serials, nullOnDelete |
| unit_price | decimal(15,2) | no | 0 | |
| total | decimal(15,2) | no | 0 | |
| notes | text | yes | null | |
| timestamps | | | | |

**Индексы**: `return_id`, `serial_id`
**Связи**: `productReturn()` (BelongsTo ProductReturn), `product()` (BelongsTo Product), `serial()` (BelongsTo ProductSerial)
**Модель**: `App\Models\Sell\ReturnItem`

---

## System

### `settings` (key-value настройки)

| Поле | Тип | Null | Default | Примечание |
|------|-----|------|---------|------------|
| id | bigint | no | auto | PK |
| key | string(100) | no | — | UNIQUE. Например: 'usd_rate', 'vat_percent' |
| value | text | yes | null | |
| type | string(20) | no | 'string' | string / int / decimal / json / boolean |
| group | string(50) | yes | null | general / finance / notifications |
| description | string(255) | yes | null | |
| timestamps | | | | |

### `audit_logs` (через spatie/laravel-activitylog)

Будет добавлено пакетом — стандартная схема, не описываем здесь.

### `notifications` (Laravel default)

Стандартная Laravel notification table — `php artisan notifications:table`.

---

## 🔗 Сводная схема связей (упрощённо)

```
users ─┬── leads (manager_id, owns)
       ├── customers (manager_id)
       ├── quotes (manager_id)
       ├── invoices (manager_id)
       ├── tickets (assigned_to)
       └── lead_activities (user_id)

lead_sources ── leads
leads ── lead_activities
leads ──→ customers (когда конвертируется)

customers ──┬── contacts
            ├── leads (после конвертации)
            ├── quotes
            ├── invoices
            ├── tickets
            ├── equipment_requests
            └── product_prices (special pricing)

quotes ──┬── quote_items ── products
         ├── quote_versions
         └── invoices (1:N — один КП может породить несколько инвойсов)

invoices ──┬── invoice_items ── products
           └── payments

product_groups ── categories (group_id)

categories ──┬── categories (self, parent_id)
             └── products

business_types ── business_type_recommendations ── products

products ──┬── product_prices
           ├── product_stocks
           ├── product_images
           ├── product_attachments
           ├── product_serials  (только для is_serial=true)
           ├── quote_items
           └── invoice_items

equipment_requests ──→ quotes (конвертация)
request_categories ── equipment_requests

tickets ──┬── ticket_comments
          └── ticket_attachments
ticket_categories ── tickets
```

---

## ✏️ Как обновлять этот файл

1. **Только database stream** редактирует
2. При изменении схемы:
   - Обновить нужный раздел
   - Добавить запись в `shared/changelog.md` (формат: дата, кратко что изменилось)
   - Уведомить design и backend через `streams/database/handoffs.md`
3. **Никогда не удаляй колонки** из контракта без согласования с design и backend

## 📌 Решённые вопросы

- ✅ `business_type` → справочник `business_types` (FK, редактируется в admin)
- ✅ Мультиязычность → `product_translations` (RU дефолт, UZ — отдельные строки)
- ✅ `product.specs` → `category_attributes` + `product_attribute_values` (предопределённые поля по категории)

## 📌 Открытые вопросы для согласования

- [ ] Подтвердить enum для `Customer.status` (active / vip / inactive / blocked)
- [ ] Подтвердить enum для `Lead.lost_reason` (price / timing / competitor / other)
- [ ] Категории атрибутов: наследовать от родительской категории? (например, "POS-терминалы" наследует атрибуты от "POS"). Phase 3 — пока плоско.
