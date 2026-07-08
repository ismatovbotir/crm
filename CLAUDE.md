# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

# 📋 Часть 1. Бизнес-контекст и Техническое задание

## 1.1. О компании

**Компания**: RSG (rsg.uz)
**Сфера деятельности**:
- Продажа торгового оборудования (POS-системы, кассовые аппараты, весы, сканеры, принтеры этикеток и чеков)
- Внедрение и автоматизация учёта для ритейла (магазины, рестораны, склады, аптеки)

**Регион**: Узбекистан (валюты UZS, USD)
**Тип бизнеса**: B2B (продажи компаниям)

## 1.2. Цель проекта

Создать комплексную **CRM + клиентский портал**, которая закрывает полный жизненный цикл клиента:

```
Лид → Квалификация → Коммерческое предложение → Согласование → Инвойс
  ↓
Оплата → Поставка оборудования → Внедрение/настройка → Тех. поддержка → Повторные продажи
```

## 1.3. Архитектура продукта (двухконтурная система)

### 🔒 Внутренний контур (CRM для сотрудников RSG)
- Управление лидами и сделками
- Создание коммерческих предложений (КП) и инвойсов
- Управление каталогом оборудования
- Обработка заявок и тикетов
- Внутренние операции (отчёты, дашборды, задачи)

### 🌐 Внешний контур (Клиентский портал)
- Личный кабинет клиента
- Просмотр своих КП и инвойсов
- Самостоятельный заказ из каталога
- Создание заявок на оборудование
- Тикет-система для тех. поддержки

---

## 1.4. Роли пользователей и матрица доступа

### Внутренние роли (сотрудники RSG):

| Роль | Доступ |
|------|--------|
| **Super Admin** | Полный доступ ко всем модулям, настройка системы |
| **Sales Director** | Управление командой продаж, все отчёты, утверждение крупных КП |
| **Sales Manager** | Свои лиды, клиенты, КП, инвойсы. Просмотр каталога. |
| **Technical Support** | Тикеты, заявки на оборудование, база знаний |
| **Catalog Manager** | Управление каталогом товаров, цены, остатки |
| **Warehouse Manager** | Склад, отгрузки, остатки |
| **Accountant** | Инвойсы, платежи, финансовые отчёты |

### Внешние роли (клиенты):

| Роль | Доступ |
|------|--------|
| **Client Admin** | Управление учётной записью своей компании, добавление сотрудников |
| **Client User** | Просмотр КП/инвойсов, заказы из каталога, тикеты |

---

## 1.5. Функциональные модули (Core Modules)

### 📌 Модуль 1: Lead Management (Управление лидами)

**Назначение**: Захват и квалификация потенциальных клиентов.

**Функциональность**:
- Создание лидов: вручную, через форму с сайта, импортом, через API
- Источники лидов: сайт, реклама, выставка, рекомендация, холодный звонок, входящий звонок
- Статусы лидов: `New → Qualified → Contacted → In Negotiation → Won / Lost`
- Назначение менеджера (auto/manual)
- Лента активности (timeline): звонки, письма, встречи, заметки
- Задачи и напоминания (follow-up)
- Конвертация лида в клиента + сделку
- Score / приоритет лида

**Ключевые сущности**:
- `Lead` — id, ФИО, компания, телефон, email, источник, статус, ответственный, score, заметки
- `LeadActivity` — лог взаимодействий
- `LeadSource` — справочник источников

---

### 📌 Модуль 2: Customer Management (Клиенты)

**Назначение**: Единая база компаний-клиентов и их контактных лиц.

**Функциональность**:
- Карточка компании (реквизиты, ИНН, адрес, тип бизнеса, сегмент)
- Контактные лица компании (несколько на одного клиента)
- История всех взаимодействий
- Связанные сущности: лиды, КП, инвойсы, заказы, тикеты
- Сегментация: тип бизнеса (магазин / ресторан / склад / аптека), объём закупок (A/B/C)
- Кредитные лимиты и условия оплаты

**Ключевые сущности**:
- `Customer` — компания
- `Contact` — контактные лица
- `CustomerSegment` — сегменты

---

### 📌 Модуль 3: Commercial Offers / Коммерческие предложения (КП)

**Назначение**: Создание и отправка КП клиентам.

**Функциональность**:
- Создание КП с позициями из каталога товаров
- Шаблоны КП для разных типов клиентов / оборудования
- Скидки: на позицию и общая
- Учёт валют (UZS, USD) с авто-конвертацией по курсу
- Версионирование: правки фиксируются, история сохраняется
- Статусы: `Draft → Sent → Viewed → Accepted / Rejected → Expired`
- Срок действия КП (валидность)
- Генерация PDF (фирменный шаблон с реквизитами RSG)
- Отправка по email с трекингом открытия
- Уведомления менеджеру при просмотре/принятии клиентом
- Конвертация принятого КП в инвойс одним кликом

**Ключевые сущности**:
- `Quote` (КП) — номер, клиент, дата, валюта, статус, срок действия, итог, версия
- `QuoteItem` — позиции (товар, количество, цена, скидка)
- `QuoteVersion` — снимки версий

---

### 📌 Модуль 4: Invoicing (Инвойсы / счета)

**Назначение**: Выставление счетов и контроль оплат.

**Функциональность**:
- Генерация инвойса из КП или вручную
- Учёт НДС (12% для Узбекистана) и без НДС
- Несколько платежей по одному инвойсу (частичная оплата)
- Статусы: `Draft → Sent → Partially Paid → Paid → Overdue → Cancelled`
- PDF-генерация с реквизитами
- Привязка отгрузочных документов (накладная, счёт-фактура)
- Уведомления о просрочке оплаты
- Экспорт для бухгалтерии (1С / Excel)

**Ключевые сущности**:
- `Invoice` — инвойс
- `InvoiceItem` — позиции
- `Payment` — платежи по инвойсу

---

### 📌 Модуль 5: Product Catalog (Каталог оборудования)

**Назначение**: Единый каталог товаров для внутреннего и клиентского использования.

**Функциональность**:
- Иерархические категории (POS / Весы / Сканеры / Принтеры / ...)
- Карточка товара: название, артикул (SKU), описание, тех. характеристики, фото (несколько), документы (паспорт, инструкция)
- Цены:
  - Закупочная (видна только админу/закупщику)
  - Розничная (RRP)
  - Оптовая
  - Специальная для конкретного клиента
- Складские остатки (stock)
- Бренды и производители
- Совместимость и связанные товары (cross-sell)
- Видимость: товар может быть скрыт из клиентского кабинета
- Импорт/экспорт CSV/Excel

**Ключевые сущности**:
- `Category` — иерархия (parent_id, тиерархия)
- `Product` — товар
- `ProductPrice` — цены по типам
- `ProductStock` — остатки
- `ProductImage` / `ProductAttachment`

---

### 📌 Модуль 6: Equipment Request System (Заявки на оборудование)

**Назначение**: Приём от клиентов запросов на конкретное или нестандартное оборудование (которого может не быть в каталоге).

**Функциональность**:
- Клиент создаёт заявку через портал (что нужно, сроки, бюджет)
- Заявка попадает менеджеру → конвертируется в КП
- Категоризация (тип оборудования)
- Назначение ответственного менеджера
- Статусы: `Submitted → Under Review → Quoted → Closed`
- Переписка по заявке: внутренние (только сотрудники) и публичные (видны клиенту) комментарии — аналогично тикетам
- Прикреплённые файлы (тех. требования, фото)

**Ключевые сущности**:
- `EquipmentRequest`
- `EquipmentRequestComment`
- `RequestCategory`

---

### 📌 Модуль 7: Ticket System (Тикет-система / тех. поддержка)

**Назначение**: Учёт обращений клиентов в тех. поддержку (после продажи).

**Функциональность**:
- Создание тикетов: клиентом через портал или сотрудником вручную
- Категории: настройка ПО, замена оборудования, обучение, гарантия, прочее
- Приоритеты: Low / Medium / High / Critical
- SLA и время реакции (по приоритету)
- Назначение ответственного (assignee)
- Переписка внутри тикета: внутренние и публичные комментарии
- Вложения (файлы, скриншоты)
- Статусы: `Open → In Progress → Pending Customer → Resolved → Closed`
- Удовлетворённость (CSAT) — оценка клиентом после закрытия
- Автоматические уведомления при изменении статуса

**Ключевые сущности**:
- `Ticket` — основной
- `TicketComment` — переписка
- `TicketCategory`
- `TicketAttachment`

---

### 📌 Модуль 8: Customer Portal (Клиентский кабинет)

**Назначение**: Самообслуживание клиента.

**Функциональность**:
- Регистрация / вход (email + пароль, опционально 2FA)
- Главная: статусы текущих сделок, открытых тикетов, последних заказов
- **Мои КП**: список КП, просмотр, скачивание PDF, акцепт/отказ онлайн
- **Мои инвойсы**: список, скачивание PDF, статусы оплаты
- **Каталог**: просмотр товаров, фильтры, поиск
- **Корзина и заказы**: добавление в корзину, оформление заказа (создаётся как заявка для менеджера)
- **Заявки**: история заявок на оборудование
- **Тикеты**: создание и переписка по тикетам
- **Профиль компании**: реквизиты, контактные лица, сотрудники
- **Управление сотрудниками**: Client Admin может добавлять Client User'ов

---

### 📌 Модуль 9: Internal Operations (Внутренние операции)

**Назначение**: Аналитика, управление, контроль.

**Функциональность**:
- **Дашборд с KPI**: лиды за период, конверсия, продажи, средний чек, количество открытых тикетов, лидеры по продажам
- **Отчёты**: продажи (период/менеджер/категория), клиенты (по сегментам), активность сотрудников, воронка продаж
- **Уведомления**: in-app, email, Telegram-бот (новые лиды, просмотр КП клиентом, новые тикеты, просрочки)
- **Календарь задач**: задачи с привязкой к лидам/клиентам, напоминания
- **Аудит-лог**: кто что менял (для критичных сущностей)
- **Импорт/экспорт**: лиды, клиенты, каталог
- **Настройки системы**: справочники, шаблоны документов, налоги, валюты, курсы

---

## 1.6. Высокоуровневая модель данных

```
┌─────────┐                         ┌──────────┐
│ Users   │ (сотрудники RSG)        │ Roles    │
└────┬────┘                         └────┬─────┘
     │                                    │
     │       ┌────────────────────────────┘
     │       │
     ▼       ▼
┌──────────────────────┐       ┌─────────────────────┐
│ Permissions (Spatie) │       │ Customers (компании)│
└──────────────────────┘       └──────┬──────────────┘
                                       │ 1:N
                                       ▼
                                ┌──────────┐
                                │ Contacts │
                                └──────────┘

┌────────┐    ┌────────┐    ┌─────────┐    ┌──────────┐
│ Leads  │ →→ │ Quotes │ →→ │Invoices │ →→ │ Payments │
└────────┘    └───┬────┘    └─────────┘    └──────────┘
                   │
                   │ N:M
                   ▼
                ┌──────────┐    ┌────────────┐
                │ Products │ ←→ │ Categories │
                └────┬─────┘    └────────────┘
                     │
                     ├──→ ProductPrices
                     ├──→ ProductStock
                     └──→ ProductImages

┌──────────────────┐    ┌─────────┐
│ EquipmentRequests│    │ Tickets │ →→ TicketComments
└──────────────────┘    └─────────┘    →→ TicketAttachments
```

---

## 1.7. Этапы разработки (Roadmap)

### Phase 0: Foundation ✅ (Завершено)
- Laravel scaffold (13.18 + Livewire 4.3 + Sanctum пакет установлен)
- Структура проекта, документация, .claude конфигурация

### Phase 1: Auth & Roles ✅ (Завершено)
- Установка `spatie/laravel-permission`
- Базовые роли (Super Admin, Sales Manager, Client Admin, Client User)
- Регистрация / логин для внутренних и внешних пользователей
- Middleware и guards для разделения контуров

### Phase 2: Customer & Lead Management ✅ (Завершено)
- Модели `Customer`, `Contact`, `Lead`, `LeadActivity`
- CRUD интерфейсы (Livewire) — Index/Create/Edit/Show для Leads и Customers
- Воронка продаж, статусы, назначение менеджеров
- Banks table, customer_users pivot (many-to-many), AdminSeeder

### Phase 3: Catalog & Pricing ✅ (Завершено)
- Миграции: categories, products, category_attributes, product_attribute_values, product_prices, product_stocks, product_images, product_attachments
- Модели в `App\Models\Catalog\`: Category, Product, ProductPrice, ProductStock, ProductImage, ProductAttachment, CategoryAttribute, ProductAttributeValue
- Livewire CRUD: Categories Index/Create/Edit, Products Index/Create/Edit/Show
- Blade views для всех компонентов, 4 badge x-компонента
- CatalogSeeder: 9 категорий POS-оборудования

### Phase 4: Quotes (КП) & Invoicing ✅ (Завершено)
- Миграции: quotes, quote_items, quote_versions, invoices, invoice_items, payments
- Модели в `App\Models\Quote\` и `App\Models\Invoice\`
- Livewire: Quotes Index/Create/Show, Invoices Index/Show
- Конвертация принятого КП → Инвойс с НДС 12% одним кликом
- Учёт частичных платежей, авто-обновление статуса инвойса
- QuotePolicy, InvoicePolicy зарегистрированы

### Phase 5 (была "Customer Portal") → реализованы Tickets ✅ (Завершено)
- Миграции: ticket_categories, tickets, ticket_comments, ticket_attachments, equipment_requests
- Модели в `App\Models\Support\`: Ticket, TicketCategory, TicketComment, TicketAttachment, EquipmentRequest
- Livewire: Tickets Index/Create/Show (с внутренними/публичными комментариями, assignee)
- TicketCategoriesSeeder: 6 категорий с SLA-временем
- TicketPolicy зарегистрирован

### Phase 6: Customer Portal ✅ (Завершено)
- `resources/views/layouts/portal.blade.php` — отдельный layout с sidebar, header, Alpine.js
- `resources/views/portal/partials/sidebar.blade.php`, `header.blade.php`
- Роуты `/portal/*` с middleware `role:client-admin|client-user`
- `User::customers()` BelongsToMany + `Customer::quotes/invoices/tickets()` HasMany добавлены
- Portal Livewire компоненты в `App\Livewire\Portal\`:
  - `Dashboard` — KPI (открытые КП, неоплаченные инвойсы, открытые тикеты), последние активности
  - `Quotes\Index` / `Quotes\Show` — просмотр КП, принятие/отклонение, авто-пометка viewed
  - `Invoices\Index` / `Invoices\Show` — просмотр инвойсов, прогресс оплаты
  - `Tickets\Index` / `Tickets\Show` — список/просмотр тикетов, добавление публичных комментариев
  - `Tickets\CreateForm` — создание тикета из портала (только публичные)
  - `Catalog\Index` — каталог с фильтрами (только `is_visible_portal=true` товары)
  - `Profile\Index` — профиль компании, контактные лица, пользователи портала
- Безопасность: ownership check `customer->id === model->customer_id` во всех Show-компонентах

### Phase 7: Dashboard, PDF, Notifications ✅ (Завершено)
- `app/Livewire/Admin/Dashboard.php` — реальные KPI: лиды месяца, клиенты, выручка, тикеты + Chart.js
- `resources/views/livewire/admin/dashboard.blade.php` — Chart.js (линейный + doughnut), последние лиды
- `app/Services/PdfService.php` — generateQuote/generateInvoice через barryvdh/laravel-dompdf
- `app/Http/Controllers/Admin/PdfController.php` + `Portal/PdfController.php`
- `resources/views/pdf/quote.blade.php` + `pdf/invoice.blade.php` — inline-CSS шаблоны для dompdf
- PDF-кнопки добавлены в admin/portal show-страницы КП и инвойсов
- Email-уведомления: `QuoteViewedNotification`, `QuoteAcceptedNotification`, `NewTicketNotification`
- Уведомления срабатывают в Portal\Quotes\Show (viewed/accepted) и Portal\Tickets\CreateForm
- **Требует**: `composer require barryvdh/laravel-dompdf` + настройка MAIL_* в .env

### Phase 8: Polish & Launch ✅ (Завершено)
- PDF download кнопки на всех show-страницах (admin + portal)
- Eager loading во всех Index компонентах (N+1 устранён)
- **Pending (требует ручных шагов)**:
  - `composer require barryvdh/laravel-dompdf` — PDF
  - `composer require spatie/laravel-activitylog` — аудит-лог (опционально)
  - Настроить `.env`: MAIL_*, QUEUE_CONNECTION=redis (для очередей)
  - `php artisan migrate:fresh --seed` — полная пересборка БД
  - `php artisan queue:work` — запуск обработчика очереди

### Phase 9: UI Polish ✅ (Завершено — 2026-05-05)
- `<x-modal>` и `<x-slide-over>`: кнопки Cancel+Save перенесены в хедер справа, X кнопка убрана
- Leads таблица: кнопка "Удалить" убрана; edit-modal теперь использует `@entangle('showEditForm')` вместо `@if` — надёжный показ без race condition
- **Исправление документационного дрейфа (2026-07-07)**: эта запись изначально содержала ещё два пункта про "Leads kanban" (фикс горизонтального скролла/`max-height`, фикс toggle `type="button"`/`$queryString`/`render()`) — аудит модуля Leads от 2026-07-07 грепом по всему коду подтвердил, что никакого Kanban-режима на момент этой фазы физически не существовало (`Index.php`/Blade были чисто табличными). Пункты были ошибочно задокументированы для несуществующего функционала и удалены отсюда. Реальная первая реализация Leads Kanban — см. Phase 14 (2026-07-07).

### Phase 10: Documents unified form + Quote edit ✅ (Завершено — 2026-05-11)
- `DatabaseSeeder`: demo seeders обёрнуты в `app()->environment(['local','development'])` guard
- `app/Livewire/Admin/Documents/CreateForm.php` — единый компонент для создания КП и Инвойса (`$type = 'quote'|'invoice'`): per-line discount (percent/sum + final_price), global discount (percent/sum), recommendations (quote only), conditional validation/save
- `resources/views/livewire/admin/documents/create-form.blade.php` — 3-зонный layout (header flex-shrink-0 / items flex-1 min-h-0 overflow-y-auto / footer flex-shrink-0); шапка: клиент+даты (строка 1), валюта+скидка+поиск товара (строки 2-3); футер: 2 строки — итоги (justify-end w-72) и условия+примечания
- Quotes Index, Invoices Index, Customers Show переведены на `admin.documents.create-form`
- `app/Livewire/Admin/Quotes/EditForm.php` — полная переработка: новая модель скидок (global_discount_type/value), customer typeahead, recommendations, блокировка если есть инвойс (`invoice()->exists()`)
- `resources/views/livewire/admin/quotes/edit-form.blade.php` — такой же 3-зонный layout, баннер сброса статуса для sent/viewed
- `QuoteController::edit()` + show.blade.php: кнопка "Редактировать" скрыта и роут закрыт если `$quote->invoice` существует
- `admin/quotes/edit.blade.php`: карточка получила `height: calc(100vh - 9rem)` для sticky-зон
- Скидка: `updatedGlobalDiscountType()` сбрасывает значение до 0 при смене типа; поле значения переведено на `wire:model.blur`

### Phase 11: Test Coverage & Infra Hardening ✅ (Завершено — 2026-07-06)
- **Тесты**: покрытие расширено с 27 до 103 тестов (94 passed / 8 failed / 1 error — непрошедшие намеренно документируют найденные баги, не флейки). Новые файлы: `tests/Feature/Catalog/{CategoryManagementTest,ProductManagementTest}.php`, `tests/Feature/Tickets/{TicketManagementTest,EquipmentRequestTest}.php`, `tests/Feature/Access/{PortalOwnershipTest,RouteSmokeTest}.php` (HTTP smoke-тест всех admin+portal маршрутов под демо-ролями). Новые фабрики: `CategoryFactory`, `ProductFactory`, `TicketFactory`, `EquipmentRequestFactory`, `TicketCommentFactory`
- **Очереди переведены на Redis**: `.env` → `QUEUE_CONNECTION=redis` (было `database`), подтверждено сквозной проверкой (dispatch → `queue:work redis --once` → обработано)
- **`spatie/laravel-activitylog` (v5.0.0) установлен и подключён**: трейт `LogsActivity` + `getActivitylogOptions()` добавлены в `Customer`, `Lead`, `Quote`, `Invoice` (`logOnlyDirty()`); таблица `activity_log`
- **`TELEGRAM_BOT_TOKEN=`** добавлен как пустой placeholder в `.env`/`.env.example` — сама интеграция бота ещё не реализована
- `php artisan migrate:fresh --seed` подтверждён рабочим end-to-end на актуальной схеме; попутно добавлено состояние `vip()` в `CustomerFactory` (использовалось `DemoLeadsSeeder`, но отсутствовало)
- **Открытый вопрос**: 6 багов найдено во время написания тестов (Catalog policies, Products EditForm null-crash, Tickets/EquipmentRequests authorization gaps, Portal multi-company ownership edge case) — задокументированы failing-тестами, ожидают отдельного фикса

### Phase 12: Equipment Request — portal self-service + реальная конвертация в КП ✅ (Завершено — 2026-07-06)
- **Portal self-service**: `App\Livewire\Portal\EquipmentRequests\{CreateForm,Index,Show}` — клиент подаёт заявку на оборудование (`subject`, `description`, `budget`, `needed_by`), видит пагинированную историю заявок своей компании, открывает карточку заявки с перепиской. Роуты `portal.equipment-requests.{index,create,show}` — в существующей группе `role:client-admin|client-user`, без новых permissions (портал по-прежнему гейтится только route-middleware). Пункт "Заявки на оборудование" добавлен в `resources/views/portal/partials/sidebar.blade.php`.
- **Реальная конвертация в КП**: `App\Livewire\Admin\EquipmentRequests\Show::convertToQuote()` (была заглушкой — просто редирект на список КП без создания записи) теперь создаёт настоящий `Quote` (номер `КП-YYYY-####`, notes/terms предзаполнены из заявки, `equipment_request_id` проставлен), идемпотентна (повторный клик не дублирует КП), автоматически переводит `EquipmentRequest.status` в `quoted`, редиректит в `Quotes\EditForm` для добавления позиций.
- **Схема**: `quotes.equipment_request_id` — настоящий nullable FK (`nullOnDelete`) на `equipment_requests` (было — индексированная колонка без DB-constraint из-за порядка миграций; файл миграции `equipment_requests` переупорядочен раньше `quotes`/`invoices`/`tickets`, чтобы FK стал реальным). `Quote::equipmentRequest()` / `EquipmentRequest::quote()` — связи в обе стороны. Кросс-ссылки ("Оформлено из заявки #X" / ссылка на итоговый КП) — на admin и portal show-страницах обеих сущностей.
- **Переписка по заявке**: новая таблица `equipment_request_comments` + модель `App\Models\Support\EquipmentRequestComment` (структурно как `TicketComment`: `is_internal` разделяет внутренние заметки и видимые клиенту ответы). `EquipmentRequest::comments()`/`publicComments()` добавлены. Admin-форма может постить оба типа комментариев; portal — только публичные (жёстко в коде, без переключателя на клиенте).
- **Вне скоупа этой фазы** (не реализовано, аспирационные пункты Модуля 6 остаются открытыми): вложения файлов к заявке (`EquipmentRequestAttachment`), категоризация (`category_id`/`RequestCategory`). Новый `Order`/Cart-объект тоже не создавался — "заказ" из Модуля 8 по-прежнему реализуется только через конвертацию заявки в КП, описанную выше.

### Phase 13: Roles Management UI (`/admin/settings/roles`) ✅ (Завершено — 2026-07-07)
- Новая страница только для `super-admin`: `App\Livewire\Admin\Settings\Roles` — просмотр всех ролей системы и редактирование их permissions чеклистом, сгруппированным по `config('permissions.permissions')`; `savePermissions()` делает `$role->syncPermissions([...])` с белым списком permission-ключей из конфига. Роут `admin.settings.roles` добавлен в уже существующую группу `role:super-admin` (не в группу с permission-гейтом) — намеренно: страница, редактирующая permissions, не должна гейтиться permission-строкой, которую сама же может изменить (риск self-lockout/privilege-escalation). Контроллер `App\Http\Controllers\Admin\Settings\RoleController`, Blade `resources/views/admin/settings/roles.blade.php` + `resources/views/livewire/admin/settings/roles.blade.php`, пункт "Роли и права" в сайдбаре (гейтится явной проверкой `hasRole('super-admin')`, а не через `@acl`, по той же причине).
- **Locked-роли**: `super-admin`, `client-admin`, `client-user` показываются в списке, но не редактируются через эту UI (серверный guard, 403 при попытке). `super-admin` навсегда остаётся wildcard `*` — гарантированный путь восстановления доступа. `client-admin`/`client-user` остаются без module permissions — их доступ в портале ownership-based, а не через Spatie permissions.
- **`database/seeders/RolesSeeder.php` — новая аддитивная семантика**: для роли, уже существующей в БД до текущего запуска сидера, `syncPermissions()` заменён на `givePermissionTo()` — обычный `migrate:fresh --seed` (см. §2.2) больше не стирает ручные правки permissions, сделанные через `/admin/settings/roles`. Для реально новой роли поведение не изменилось (`syncPermissions()`, точный снимок конфига). `config/permissions.php` остаётся гарантированным **минимумом** для каждой роли; обратная сторона — если permission позже удаляется из конфига, сидер больше не отзовёт его у ролей, уже получивших его в БД (осознанный trade-off).
- Тесты: `tests/Feature/Access/RolePermissionsManagementTest.php` (19 тестов) — доступ только super-admin, персист toggle permission, guard на locked-ролях, рендер всех 8 ролей, регрессия на аддитивность сидера. `php artisan test`: **184 passed** (было 165).

### Phase 14: Leads Kanban (реальная реализация) + чистка мёртвого CSV-экспорта ✅ (Завершено — 2026-07-07)
- **Удалён мёртвый код CSV-экспорта лидов**: роут `admin.export.leads` (`routes/web.php`), метод `ExportController::leads()` (`app/Http/Controllers/Admin/ExportController.php`), permission `leads.export` (`config/permissions.php`, из группы `leads` и из роли `sales-director`). Blade-кнопка была убрана ранее (см. Phase 13/changelog 2026-07-07, `admin-bi-developer`); `customers.export`/`invoices.export` не затронуты.
- **Leads Kanban реализован с нуля** (см. примечание в Phase 9 — предыдущая запись про "Leads kanban" описывала фиксы к несуществовавшему на тот момент функционалу; это первая фактическая реализация):
  - `app/Livewire/Admin/Leads/Index.php`: `viewMode` (`table`/`kanban`), `setViewMode()`, `scopedQuery()` — общий ownership+search scope, используемый и таблицей, и канбаном; `kanbanColumns()` — 6 колонок (`new`/`qualified`/`contacted`/`in_negotiation`/`won`/`lost`; статус `client` намеренно не получает колонку — терминальный/системный статус); `moveLeadStatus()` — авторизация через `LeadPolicy::update`, whitelist допустимых статусов, идемпотентный no-op при повторном той же колонке, запись `LeadActivity` при реальном изменении статуса (зеркалит существующий `Show::changeStatus()`).
  - `resources/views/livewire/admin/leads/index.blade.php`: toggle таблица/канбан, состояние персистится в `localStorage` (ключ `rsg-admin-leads-view-mode`, по аналогии с `rsg-admin-sidebar-collapsed`); сама доска — HTML5 drag-and-drop + Alpine, вызывает `$wire.moveLeadStatus(id, status)`.
  - Ownership-скоуп (sales-manager видит только своих лидов, director/super-admin — всех) применяется одинаково в table- и kanban-режимах через общий `scopedQuery()`.
- Тесты: `tests/Feature/Leads/LeadKanbanTest.php` — 9 новых тестов (ownership scoping канбана, исключение статуса `client` из колонок, happy path/авторизация/whitelist/идемпотентность `moveLeadStatus()`). `php artisan test`: **193 passed** (было 165 на начало сессии; часть прироста — параллельная работа над roles-permissions из Phase 13, часть — эта задача).

### Phase 15: Admin branding — фирменная тема RSG (#D00000) ✅ (Завершено — 2026-07-08)
- **Семантическая цветовая палитра определена впервые**: `resources/css/app.css` (Tailwind v4, `@theme`) получил полную шкалу (50-900) для `--color-primary-*`, `--color-success-*`, `--color-warning-*`, `--color-danger-*`, `--color-info-*`. До этого классы вроде `bg-primary-600`, уже использовавшиеся примерно в 70 blade-файлах (admin и portal), физически не генерировали CSS — токены нигде не были определены (пробел с самого начала проекта). Дефолт для `primary` — нейтральный Tailwind blue, наследуется всеми layout'ами.
- **Брендовый красный `#D00000` применён только к admin-контуру**, через CSS-класс `.admin-theme` (переопределяет только `--color-primary-*` каскадом CSS custom properties), который вешается на `<html>` только в `resources/views/layouts/admin.blade.php`. Портал (`layouts/portal.blade.php`) и guest/login (`layouts/guest.blade.php`) на этот класс не переведены и остаются на нейтральном дефолте — паттерн специально спроектирован так, чтобы будущие layout'ы явно выбирали, наследовать бренд-тему или нет (см. `.claude/shared/decisions-log.md`, запись 2026-07-08).
- `success/warning/danger/info` остаются семантически нейтральными везде (в т.ч. внутри `.admin-theme`) — статусное цветовое кодирование не путается с брендовым primary-красным.
- Логотип в `resources/views/admin/partials/sidebar.blade.php`: inline SVG-иконка "штрихкод" + текст "RSG" (`text-primary-600`) вместо прежнего квадрата с буквой "R".
- `resources/views/admin/partials/header.blade.php`, `*-status-badge.blade.php`, `components/badge.blade.php` не изменялись — уже были полностью на семантических классах.
- **Открытый follow-up (не сделан)**: Chart.js-конфиги в `resources/views/livewire/admin/dashboard.blade.php` и `resources/views/livewire/admin/reports/index.blade.php` используют hardcoded hex (`#3B82F6`) вместо новых токенов — графики не подхватывают бренд-красный автоматически.
- `php artisan test`: 195 passed, 0 failed — регрессий нет (изменение чисто CSS/Blade).

---

# 🔀 Часть 1.5. Архитектура агентов (PM + субагенты)

Работа над проектом организована не по потокам, а по **ролям**: один PM-агент с доменной экспертизой B2B CRM декомпозирует задачи и делегирует их специализированным субагентам. Все агенты связаны через общую базу знаний (`.claude/shared/`).

> История: до 2026-07-06 использовалось разделение на 3 потока (design/database/backend). Архив — `.claude/streams-archive/`.

## 🤖 Агенты

| Агент | Роль | Границы |
|---|---|---|
| **`pm-b2b-crm`** | PM с доменной экспертизой B2B CRM. Декомпозирует запрос, делегирует субагентам, следит за соответствием бизнес-требованиям (Часть 1) | Не пишет код сам — только читает, планирует и делегирует через `Agent` tool |
| **`laravel-fullstack`** | Весь Laravel backend + DB для любого модуля: миграции, модели, Livewire CRUD, services, policies, API-контроллеры, jobs, notifications | Кроме модуля Тикетов/EquipmentRequests (→ `ticket-system`) и Blade/отчётов (→ `admin-bi-developer`) |
| **`admin-bi-developer`** | Все Blade/Tailwind/Alpine проекта (admin-панель И клиентский портал) + Dashboard/Reports/KPI/Chart.js логика и вёрстка | Не трогает миграции, бизнес-логику вне отчётов, модуль Тикетов |
| **`ticket-system`** | Вертикальный срез модуля тех.поддержки: Tickets/TicketComments/TicketCategories/TicketAttachments/EquipmentRequests — миграции + модели + Livewire + Blade целиком | Только Support-домен (Модули 6–7) |
| **`swagger-docs`** | OpenAPI/Swagger документация для `routes/api.php` | Только документация, не реализация |
| **`qa-tester`** | Feature/Unit тесты, регрессии, проверка прав доступа | Не правит продовый код напрямую |
| **`claude-md-maintainer`** | **Единственный** агент, редактирующий этот файл (`CLAUDE.md`) | Только `CLAUDE.md`, никакого кода |

## 📁 Структура

```
.claude/
├── agents/
│   ├── pm-b2b-crm.md, laravel-fullstack.md, admin-bi-developer.md
│   ├── ticket-system.md, swagger-docs.md, qa-tester.md
│   └── claude-md-maintainer.md
├── shared/
│   ├── glossary.md           # Общий словарь (Lead, Customer, КП...)
│   ├── data-contracts.md     # Структура моделей (owner: laravel-fullstack, ticket-system для Support)
│   ├── api-contracts.md      # API endpoints (owner: laravel-fullstack, документирует swagger-docs)
│   ├── changelog.md          # Общий журнал изменений (append-only)
│   └── decisions-log.md      # Кросс-агентные решения
└── streams-archive/          # Архив старого stream-based workflow
```

## 🔄 Как это работает

1. Пользователь описывает задачу главному Claude (или сразу `pm-b2b-crm` через `Agent` tool)
2. `pm-b2b-crm` читает `CLAUDE.md` и `.claude/shared/*`, разбивает задачу по границам агентов из таблицы выше и вызывает нужных субагентов
3. Каждый субагент по завершении:
   - Добавляет запись в `.claude/shared/changelog.md`: `## YYYY-MM-DD — [agent-name] Что сделано`
   - Обновляет `data-contracts.md` / `api-contracts.md`, если менял схему/API
   - **Не редактирует `CLAUDE.md` сам** — сообщает об изменении в отчёте
4. Если работа затронула то, что описано в `CLAUDE.md` (роли, модули, архитектура, workflow, tech stack) — `pm-b2b-crm` вызывает `claude-md-maintainer` для точечного обновления документа
5. Кросс-агентные архитектурные решения → `.claude/shared/decisions-log.md`

## 🎯 Использование на практике

### Вариант A: Через PM (рекомендуется для многошаговых задач)
- Вызываешь `pm-b2b-crm` с бизнес-формулировкой задачи
- PM декомпозирует и делегирует нужным субагентам, следит за целостностью

### Вариант B: Напрямую через Agent tool
- Главный Claude вызывает конкретного субагента напрямую, если задача узко умещается в его границы
- Например: "laravel-fullstack добавь поле X в Customer" + "admin-bi-developer добавь фильтр Y в дашборд"

### Вариант C: Параллельные сессии
- Открываешь несколько окон Claude Code в одной папке проекта, в каждом работаешь с разными агентами
- Связь через `shared/changelog.md` и `shared/decisions-log.md`

---

# 🛠 Часть 2. Техническая документация для разработки

## 2.1. Project Overview

**RSG-CRM** — Laravel 13 application (CRM + customer portal) для компании RSG (торговое оборудование и автоматизация ритейла).

- **Backend**: Laravel 13.18 + Livewire 4.3
- **API**: пакет `laravel/sanctum` установлен, но `routes/api.php` пока пустой — API-контроллеров и эндпоинтов не реализовано
- **Frontend Assets**: Vite (JS/CSS bundling)
- **Database**: MySQL
- **PHP**: 8.4+

## 2.2. Development Commands

### Setup & Installation

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### Running the Application

```bash
php artisan serve            # Backend on http://localhost:8000
npm run dev                  # Vite dev server with HMR
npm run build                # Production assets build
```

### Database & Migrations

```bash
php artisan make:migration create_<table>_table
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh --seed
```

> **Правило миграций**: Всегда изменяй существующий файл миграции. Никогда не создавай `add_*` или `extend_*` миграции для добавления полей к существующим таблицам. Это проект в активной разработке — просто правим исходную миграцию и делаем `migrate:fresh --seed`.

### Code Generation

```bash
php artisan make:model ModelName -mf       # Model + migration + factory
php artisan make:controller ControllerName
php artisan make:livewire ComponentName
php artisan make:request StoreXRequest
php artisan make:policy ProductPolicy --model=Product
```

### Testing & Quality

```bash
php artisan test
php artisan test tests/Feature/LeadTest.php
php artisan test --filter testLeadCanBeCreated
composer pint
composer pint --test
```

### Useful

```bash
php artisan tinker
php artisan route:list
php artisan cache:clear && php artisan config:clear && php artisan view:clear
```

## 2.3. Project Structure

```
app/
├── Console/                    # Artisan-команды (импорты, рассылки, очистка)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/              # Контроллеры внутренней CRM
│   │   ├── Portal/             # Контроллеры клиентского кабинета
│   │   └── Api/                # API endpoints
│   ├── Middleware/             # Кастомное middleware (роли, контуры)
│   ├── Requests/               # Form Request validation
│   └── Resources/              # API Resources (трансформация ответов)
├── Livewire/
│   ├── Admin/                  # Livewire компоненты CRM
│   └── Portal/                 # Livewire компоненты портала
├── Models/                     # Eloquent модели
├── Policies/                   # Авторизация на уровне моделей
├── Services/                   # Бизнес-логика (PdfService, NotificationService)
├── Notifications/              # Email/Telegram уведомления
└── Providers/

config/                          # Конфиги (app, database, permission, services...)
database/
├── migrations/                  # Схемы БД
├── factories/                   # Фабрики для тестов
└── seeders/                     # Сиды (роли, демо-данные)
resources/
├── views/
│   ├── layouts/                 # Базовые layout (admin.blade.php, portal.blade.php)
│   ├── admin/                   # Шаблоны CRM
│   ├── portal/                  # Шаблоны клиентского кабинета
│   └── livewire/                # Livewire-компоненты
├── css/  &  js/                 # Vite assets
routes/
├── web.php                      # Веб-роуты (CRM + portal)
├── api.php                      # API
├── channels.php                 # Broadcasting
└── console.php                  # Artisan
tests/
├── Feature/                     # Интеграционные тесты
└── Unit/                        # Юнит-тесты
```

## 2.4. Architecture & Key Patterns

### 🔐 Контроль доступа: 3 уровня защиты

Любая страница, действие или элемент UI защищаются через **3 независимых уровня**. Пропуск любого из них — это уязвимость.

**Уровень 1: Routes** (главная защита)
Каждая admin-группа роутов закрыта middleware. Без правильной роли — 403:
```php
Route::middleware(['auth', 'role:sales-manager|sales-director'])
    ->prefix('admin/leads')
    ->group(function () {
        Route::get('/', \App\Livewire\Admin\Leads\Index::class)->name('admin.leads.index');
    });
```

**Уровень 2: UI** (скрываем недоступное)
Меню, кнопки, ссылки — только для тех, у кого есть permission:
```blade
@can('leads.create')
    <button>+ Создать лид</button>
@endcan
```

В сайдбаре каждый пункт описан с полем `permission`. Цикл показывает только разрешённые.

**Уровень 3: Policies** (доступ к конкретным записям)
"Видеть лидов" — да. "Видеть лида Васи" — только если он свой. Это решает Policy:
```php
// app/Policies/LeadPolicy.php
public function view(User $user, Lead $lead): bool
{
    if ($user->hasRole(['super-admin', 'sales-director'])) return true;
    return $lead->assigned_to === $user->id;
}
```

В Livewire-компонентах перед действиями обязательно `$this->authorize(...)`:
```php
public function mount(Lead $lead) {
    $this->authorize('view', $lead);
    $this->lead = $lead;
}

public function deleteLead(Lead $lead) {
    $this->authorize('delete', $lead);
    $lead->delete();
}
```

**Дополнительно: фильтрация в выборках**
Permission `leads.view` показывает меню. Что попадает в список — это уже логика компонента:
```php
public function getLeadsProperty() {
    $query = Lead::query();
    if (!auth()->user()->hasRole(['super-admin', 'sales-director'])) {
        $query->where('assigned_to', auth()->id());
    }
    return $query->paginate(15);
}
```

### 📋 Структура permissions (Spatie)

Permissions именуются как `module.action`:

```
leads.view          customers.view         quotes.view         invoices.view
leads.create        customers.create       quotes.create       invoices.create
leads.update        customers.update       quotes.update       invoices.update
leads.delete        customers.delete       quotes.delete       invoices.cancel
leads.assign                               quotes.send

catalog.products.view       catalog.products.update      catalog.prices.view-cost
tickets.view                tickets.update               tickets.assign  tickets.close
reports.sales               reports.managers             reports.financial
settings.users              settings.roles               settings.system
```

### Базовые роли (внутренние)

| Роль | Описание | Ключевые permissions |
|------|----------|----------------------|
| `super-admin` | Полный доступ | все * |
| `sales-director` | Управление продажами | leads.*, customers.*, quotes.*, invoices.view, reports.* |
| `sales-manager` | Линейный менеджер | leads.* (own), customers.* (own), quotes.* (own) |
| `tech-support` | Тех. поддержка | tickets.*, customers.view |
| `catalog-manager` | Управление каталогом | catalog.* |
| `accountant` | Бухгалтерия | invoices.*, reports.financial |

**Клиентские роли** (для портала):
- `client-admin` — управляет аккаунтом своей компании
- `client-user` — обычный пользователь от клиента

### 🗂 Источник истины для прав: `config/permissions.php`

Все permissions и роли описаны в `config/permissions.php`. Это единый источник для:
- Сидера ролей (`database/seeders/RolesSeeder.php`)
- Сайдбара (массив пунктов меню с `permission`)
- UI управления ролями (`/admin/settings/roles`, реализована — только `super-admin`, гейтится по роли, а не по permission; locked-роли и аддитивная семантика сидера — см. Roadmap Phase 13)
- Документации

При добавлении нового permission:
1. Добавить в `config/permissions.php` → `permissions[<module>]`
2. Привязать к нужным ролям в `roles[*].permissions`
3. Запустить `php artisan db:seed --class=RolesSeeder`
4. При необходимости добавить в `sidebar.blade.php` или защитить кнопку через `@acl(...)`

### 🛠 Helper: `App\Helpers\Acl`

Единый интерфейс проверки прав в коде и Blade. Работает **в preview-mode** (до установки Spatie) и после.

```php
use App\Helpers\Acl;

if (Acl::can('quotes.send')) { ... }      // в PHP
Acl::isInternal();                          // сотрудник RSG?
Acl::isClient();                            // клиент?
```

В Blade — кастомные директивы (зарегистрированы в `AppServiceProvider`):
```blade
@acl('leads.create')
    <button>+ Создать лид</button>
@endacl

@isInternal
    {{-- блок только для сотрудников RSG --}}
@endisInternal

@isClient
    {{-- блок только для клиентов в портале --}}
@endisClient
```

**Поведение в preview-mode**: пока Spatie не установлен и нет авторизованного юзера, в `local` env helper возвращает `true` — это позволяет дизайну выглядеть полным во время разработки, до настройки auth.

### ⚠️ Ключевое правило: CRUD → Livewire

**Все CRUD-операции в этом проекте реализуются через Livewire-компоненты, а не через классические Laravel-контроллеры с redirect/Blade-формами.**

Это касается:
- Списков (index): таблицы, фильтры, поиск, пагинация, сортировка
- Создания (create) и редактирования (edit): формы со всей валидацией
- Удаления (delete): с подтверждением, soft-delete где применимо
- Просмотра (show): особенно если есть интерактивные элементы (tabs, inline-edit)

**Почему так**:
- Реактивность UI без написания JS
- Прямой доступ к Eloquent в компонентах (без API-слоя)
- Live-валидация и feedback пользователю
- Меньше boilerplate (Controller + Request + Resource + view + JS заменяется одним компонентом)

**Контроллеры используем только для**:
- API endpoints (`routes/api.php`) — для мобильного приложения, интеграций, webhooks
- Не-CRUD действий: PDF generation, экспорт CSV/Excel, OAuth callbacks, webhook receivers

**Структура Livewire-компонента CRUD**:
```
app/Livewire/Admin/Leads/
├── Index.php           # Список (таблица, фильтры, поиск, пагинация)
├── CreateForm.php      # Форма создания (slide-over)
├── EditForm.php        # Форма редактирования
└── Show.php            # Просмотр карточки

resources/views/livewire/admin/leads/
├── index.blade.php
├── create-form.blade.php
├── edit-form.blade.php
└── show.blade.php
```

**Шаблон роутинга** (Livewire-компоненты как полноценные страницы):
```php
// routes/web.php
Route::middleware(['auth', 'role:internal'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/leads', \App\Livewire\Admin\Leads\Index::class)->name('leads.index');
    Route::get('/leads/{lead}', \App\Livewire\Admin\Leads\Show::class)->name('leads.show');
});
```

Создание и редактирование — обычно через slide-over (Alpine + Livewire) на той же странице, без отдельных URL для форм.

### Двухконтурная архитектура

**Внутренний контур (CRM)**:
- Routes: `/admin/*`
- Layout: `resources/views/layouts/admin.blade.php`
- Middleware: `auth` + `role:internal`

**Внешний контур (Portal)**:
- Routes: `/portal/*`
- Layout: `resources/views/layouts/portal.blade.php`
- Middleware: `auth` + `role:client`
- Guard: можно использовать отдельный guard для клиентов либо общий с разделением по ролям

### Authentication

- **Web (CRM + Portal)**: session-based + Sanctum для SPA
- **Mobile / API**: Sanctum personal access tokens

### Authorization

- **Spatie Permission** для ролей и прав
- **Policies** для доступа к конкретным записям (например, менеджер видит только своих клиентов)

### Eloquent Models

- Models организованы по доменным поддиректориям в `app/Models/`:
  - `App\Models\User` — остаётся в корне (Laravel convention)
  - `App\Models\BusinessType` — общий справочник, остаётся в корне
  - `App\Models\Customer\` — Customer, Contact, Bank, CustomerUser
  - `App\Models\Lead\` — Lead, LeadActivity, LeadSource
  - `App\Models\Catalog\` — Product, Category, ProductPrice, ProductStock (Phase 3)
  - `App\Models\Quote\` — Quote, QuoteItem, QuoteVersion (Phase 4)
  - `App\Models\Invoice\` — Invoice, InvoiceItem, Payment (Phase 4)
  - `App\Models\Support\` — Ticket, TicketComment, TicketAttachment, EquipmentRequest (Phase 5), EquipmentRequestComment (Phase 12)
- **Правило**: каждый новый model создаётся в поддиректории своего домена
- Каждый model с factory должен переопределять `newFactory()`:
  ```php
  protected static function newFactory(): \Database\Factories\XxxFactory
  {
      return \Database\Factories\XxxFactory::new();
  }
  ```
- Используем relationships (`hasMany`, `belongsTo`, `belongsToMany`)
- Soft deletes для критичных сущностей (Customer, Lead, Quote, Invoice)
- Scopes для часто используемых фильтров (`scopeActive`, `scopeForUser`)
- Observers для автоматического логирования

### Livewire Components

- `app/Livewire/Admin/` — компоненты CRM
- `app/Livewire/Portal/` — компоненты портала
- Каждый компонент = PHP класс + Blade view
- Используем `wire:click`, `wire:model.live`, события для межкомпонентного общения
- **Все CRUD реализуются здесь** — см. правило выше
- Для тяжёлых списков: `WithPagination` trait + indexed запросы
- Для форм: `rules()` метод для валидации, `messages()` для кастомных сообщений
- Slide-over формы открываются через `$this->dispatch('open-create-form')` event-driven подход

### Service Layer

Сложная бизнес-логика выносится в сервисы (`app/Services/`):
- `QuoteService` — создание и расчёт КП
- `InvoiceService` — генерация инвойсов
- `PdfService` — генерация PDF
- `NotificationService` — email/Telegram уведомления

### Frontend Asset Pipeline (Vite)

- Entry: `resources/js/app.js`, `resources/css/app.css`
- В Blade: `@vite(['resources/js/app.js', 'resources/css/app.css'])`
- Dev: `npm run dev` (HMR)
- Prod: `npm run build` → `public/build/`

### Routing

- **Web** (`routes/web.php`): группы `admin`, `portal`
- **API** (`routes/api.php`): `auth:sanctum`

Пример:
```php
Route::middleware(['auth', 'role:internal'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('leads', LeadController::class);
    Route::resource('customers', CustomerController::class);
});

Route::middleware(['auth', 'role:client'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/quotes', [PortalQuoteController::class, 'index']);
});
```

## 2.5. Database Configuration

В `.env`:
- **DB_CONNECTION**: mysql
- **DB_HOST / DB_PORT**: 127.0.0.1 / 3306
- **DB_DATABASE**: имя БД (например, `rsg_crm`)
- **DB_USERNAME / DB_PASSWORD**: креды

## 2.6. Environment Configuration

Ключевые переменные `.env`:
- `APP_NAME=RSG-CRM`
- `APP_ENV=local | production`
- `APP_DEBUG=true | false`
- `APP_KEY=...` (`php artisan key:generate`)
- `APP_URL=http://localhost:8000`
- `MAIL_*` для отправки писем
- `TELEGRAM_BOT_TOKEN` — пустой placeholder уже присутствует в `.env`/`.env.example`; сама интеграция бота ещё не реализована (планируется)

## 2.7. Testing

- **Feature tests**: `tests/Feature/` — HTTP-уровень
- **Unit tests**: `tests/Unit/` — отдельные классы
- Используем `RefreshDatabase` trait для авто-rollback
- Фабрики моделей для генерации тестовых данных
- Покрывать тестами как минимум: создание лидов, конвертацию в КП, генерацию инвойсов, права доступа

## 2.8. Performance & Caching

- **Eager loading** (`with()`) против N+1
- **Индексы** на foreign keys и часто фильтруемых полях
- **Кеширование** справочников (категории, роли, настройки)
- **Очереди** для тяжёлых задач (отправка email, генерация PDF, импорты) — Redis Queue (`QUEUE_CONNECTION=redis` в `.env`, подтверждено сквозной проверкой с 2026-07-06)
- В production: `php artisan config:cache && php artisan route:cache && php artisan view:cache`

## 2.9. Security & Compliance

- Все формы — через CSRF и Form Requests с валидацией
- Авторизация через Policies + Spatie Permission
- Soft deletes для критичных данных
- Аудит-лог изменений — `spatie/laravel-activitylog` установлен и подключён (`LogsActivity` + `logOnlyDirty()`) к `Customer`, `Lead`, `Quote`, `Invoice`; таблица `activity_log` (остальные модели — по мере необходимости)
- Хранение реквизитов клиентов (ИНН, банк) — с учётом ПДн

## 2.10. Common Gotchas

1. **APP_KEY not set** → `php artisan key:generate`
2. **Permission denied на роутах** → проверь Spatie Permission кеш: `php artisan permission:cache-reset`
3. **Кеш конфига перекрывает .env** → `php artisan config:clear`
4. **Vite assets не подгружаются** → запусти `npm run dev` или `npm run build`
5. **Дублирование лидов** → проверять по телефону/email уникальность с трим/нормализацией
6. **Пути для CRM vs Portal** → не смешивать роуты, использовать разные prefix/middleware

## 2.11. Цветовая / дизайн-система

- Все цветовые токены определены один раз в `resources/css/app.css` (Tailwind v4, блок `@theme`): шкалы (50-900) `--color-primary-*`, `--color-success-*`, `--color-warning-*`, `--color-danger-*`, `--color-info-*`. Это единственный источник для utility-классов вида `bg-primary-600`, `text-danger-700` и т.д. — других мест, где определяются цвета, в проекте нет.
- `primary` по умолчанию — нейтральный Tailwind blue, наследуется всеми layout'ами (admin/portal/guest).
- **`.admin-theme`** — CSS-класс на `<html>` в `resources/views/layouts/admin.blade.php`, переопределяющий каскадом только `--color-primary-*` на брендовый RSG-красный (`#D00000`). Это паттерн контурного брендинга: новый layout/контур (например, будущий партнёрский портал) должен явно решить, наследовать ли `.admin-theme` или остаться на нейтральном дефолте — автоматически это не наследуется.
- `success`/`warning`/`danger`/`info` — общесистемная семантика статусов (успех/предупреждение/ошибка/инфо), не завязанная на бренд; не путать `danger` (обычный tailwind red) с брендовым `primary`-красным.
- Подробности и обоснование — `.claude/shared/decisions-log.md`, запись «2026-07-08 — Семантическая цветовая палитра определена впервые + паттерн `.admin-theme`...»; Roadmap — Часть 1.7 → Phase 15.
