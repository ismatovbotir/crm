> ⚠️ АРХИВ (2026-07-06): stream-based workflow заменён на ролевую модель PM + субагенты. См. `CLAUDE.md` → "Часть 1.5. Архитектура агентов" и `.claude/agents/`. Этот файл сохранён только для истории.

# Database Stream — STATUS

**Last updated**: 2026-05-10
**Phase**: Phase 8+ (product groups + business-type recommendations)

## ✅ Сделано

### Phase 1 (Auth + Roles)
- ✅ Spatie Permission установлен
- ✅ Migration: расширение users (phone, is_active, avatar_path, last_login_at)
- ✅ Model User: HasRoles, scopeActive, scopeManagers
- ✅ RolesSeeder (создаёт permissions + 8 ролей из config/permissions.php)
- ✅ DemoUsersSeeder (6 демо-пользователей)

### Phase 2 (Customers + Leads)
- ✅ 6 миграций: business_types, customers, contacts, lead_sources, leads, lead_activities
- ✅ 6 тонких моделей с связями и scopes
- ✅ 2 сидера справочников: BusinessTypesSeeder, LeadSourcesSeeder

### Документация
- ✅ Полная ER-диаграмма в `shared/data-contracts.md` (28+ таблиц)
- ✅ Все решения зафиксированы в decisions.md и shared/decisions-log.md

## ✅ Все фазы выполнены

### Phase 1: users, roles (Spatie Permission)
### Phase 2: business_types, customers, contacts, lead_sources, leads, lead_activities
### Phase 3: categories, category_attributes, products, product_attribute_values, product_prices, product_stocks, product_images, product_attachments
### Phase 8+ (2026-05-09): product_serials migration + ProductSerial model; is_serial column on products
### Phase 8+ refactor (2026-05-09): serial tracking redesign + product returns
- product_serials: removed customer_id/sell_item_id/sold_at/status(enum); added current_status + current_owner_id
- New: serial_statuses (append-only log), serial_owners (ownership history)
- New: product_returns, return_items
- New models: SerialStatus, SerialOwner, ProductReturn, ReturnItem
- Updated: Sell + Invoice models with productReturns() relation
### Phase 4: quotes, quote_items, quote_versions, invoices, invoice_items, payments
### Phase 5: ticket_categories, tickets, ticket_comments, ticket_attachments, equipment_requests
### Phase 6: banks, customer_users (pivot)

### Phase 8+ product groups (2026-05-10)
- Миграция `product_groups` (139900)
- Правка `categories` — добавлен group_id FK + index (140000)
- Миграция `business_type_recommendations` (140150)
- Модели: `ProductGroup`, `BusinessTypeRecommendation`
- Обновлены: `Category` (group_id + group()), `BusinessType` (recommendations()), `Product` (recommendations())
- Сидеры: `ProductGroupsSeeder` (4 группы), `BusinessTypeRecommendationsSeeder` (6 типов бизнеса)
- `DatabaseSeeder` обновлён

**Модели** в `App\Models\`:
- `User`, `BusinessType`
- `Customer\{Customer, Contact, Bank, CustomerUser}`
- `Lead\{Lead, LeadActivity, LeadSource}`
- `Catalog\{Category, Product, ProductPrice, ProductStock, ProductImage, ProductAttachment, CategoryAttribute, ProductAttributeValue, ProductSerial, SerialStatus, SerialOwner, ProductGroup, BusinessTypeRecommendation}`
- `Quote\{Quote, QuoteItem, QuoteVersion}`
- `Invoice\{Invoice, InvoiceItem, Payment}`
- `Support\{Ticket, TicketCategory, TicketComment, TicketAttachment, EquipmentRequest}`
- `Sell\{Sell, SellItem, ProductReturn, ReturnItem}`

**Сидеры**: RolesSeeder, DemoUsersSeeder, BusinessTypesSeeder, LeadSourcesSeeder, ProductGroupsSeeder, CatalogSeeder, ProductSeeder, BusinessTypeRecommendationsSeeder, AdminSeeder, TicketCategoriesSeeder

## 🔄 В работе

— нет

## ⏭ Следующее (опционально)

- Миграции для системы уведомлений (notifications таблица — Laravel стандарт)
- Аудит-лог таблицы (если подключить `spatie/laravel-activitylog`)

## 📝 Команды

```bash
php artisan migrate:fresh --seed   # полная пересборка БД
```

Логин: `admin@rsg.uz` / `password`
