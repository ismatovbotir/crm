# Database Stream — TODO

**Last verified**: 2026-07-06 — сверено с `database/migrations/`, `app/Models/`, `database/seeders/`, `composer.json`/`vendor/`.

## 🔴 P0 (блокеры)

- [ ] `spatie/laravel-permission` отсутствует в `composer.json`/`vendor/`, хотя миграция `2026_04_28_110307_create_permission_tables.php` создаёт его таблицы, а `User` использует `HasRoles`. Нужно: `composer require spatie/laravel-permission`.
- [ ] `barryvdh/laravel-dompdf` отсутствует — используется в `App\Services\PdfService`.

## ✅ Все миграции/модели/сидеры на месте

Подтверждено по факту (39 миграций в `database/migrations/`):
- Auth/roles, Customers/Leads/Contacts/Banks
- Catalog: categories (+ group_id), products, prices, stocks, images, attachments, attributes, groups, serials (+ statuses/owners), business-type recommendations
- Quotes/QuoteItems/QuoteVersions, Invoices/InvoiceItems/Payments
- Tickets/TicketCategories/TicketComments/TicketAttachments, EquipmentRequests
- Sells/SellItems, ProductReturns/ReturnItems

**Модели** — все домены на месте: `Customer\*`, `Lead\*`, `Catalog\*`, `Quote\*`, `Invoice\*`, `Support\*`, `Sell\*` (см. `streams/database/STATUS.md` для полного списка).

**Сидеры** — все 13 файлов присутствуют и подключены в `DatabaseSeeder` (Roles, DemoUsers, BusinessTypes, LeadSources, ProductGroups, Catalog, Product, BusinessTypeRecommendations, Admin, TicketCategories, Banks, DemoLeads).

## 🟢 P2 (опционально)

- [ ] Миграция `notifications` (стандартная Laravel database-notifications таблица) — нужна только если решим хранить in-app уведомления в БД, а не только email/Telegram
- [ ] Таблицы для аудит-лога (если подключат `spatie/laravel-activitylog` — тоже отсутствует в vendor)
