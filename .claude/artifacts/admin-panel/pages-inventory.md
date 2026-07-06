# Pages Inventory — RSG-CRM Admin

Полный перечень страниц админ-панели по модулям.

## 📊 Dashboard

| URL | Страница | Назначение |
|-----|----------|-----------|
| `/admin` | Dashboard | Главная: KPI, графики, последние лиды/тикеты |

**Виджеты дашборда**:
- 4 KPI карточки: Лиды (за месяц), Конверсия %, Продажи, Открытые тикеты
- График: продажи по неделям (line chart)
- Список: последние 10 лидов
- Список: 5 ближайших задач/follow-up
- Pie chart: лиды по источникам

## 👥 Leads (Лиды)

| URL | Страница |
|-----|----------|
| `/admin/leads` | Список лидов с фильтрами |
| `/admin/leads/create` | Создание лида |
| `/admin/leads/{id}` | Карточка лида (детальная) |
| `/admin/leads/{id}/edit` | Редактирование |

**Список**: фильтры (статус, источник, ответственный, период), поиск, экспорт
**Карточка**: общая инфо, timeline активностей, задачи, связанные КП

## 🏢 Customers (Клиенты)

| URL | Страница |
|-----|----------|
| `/admin/customers` | Список клиентов |
| `/admin/customers/create` | Создание |
| `/admin/customers/{id}` | Карточка клиента |
| `/admin/customers/{id}/edit` | Редактирование |
| `/admin/customers/{id}/contacts` | Контактные лица |

**Карточка**: реквизиты, контактные лица, история сделок, инвойсы, тикеты

## 📄 Quotes (КП)

| URL | Страница |
|-----|----------|
| `/admin/quotes` | Список КП |
| `/admin/quotes/create` | Создание КП |
| `/admin/quotes/{id}` | Просмотр КП |
| `/admin/quotes/{id}/edit` | Редактирование |
| `/admin/quotes/{id}/preview` | Preview PDF |

**Действия**: отправить клиенту, сгенерировать PDF, конвертировать в инвойс, дублировать

## 💰 Invoices (Инвойсы)

| URL | Страница |
|-----|----------|
| `/admin/invoices` | Список инвойсов |
| `/admin/invoices/create` | Создание |
| `/admin/invoices/{id}` | Карточка инвойса |
| `/admin/invoices/{id}/payments` | Платежи по инвойсу |

## 📦 Catalog (Каталог)

| URL | Страница |
|-----|----------|
| `/admin/catalog/categories` | Дерево категорий |
| `/admin/catalog/products` | Список товаров |
| `/admin/catalog/products/create` | Создание товара |
| `/admin/catalog/products/{id}` | Карточка товара |
| `/admin/catalog/import` | Импорт CSV/Excel |

## 🎫 Tickets (Тикеты)

| URL | Страница |
|-----|----------|
| `/admin/tickets` | Список тикетов |
| `/admin/tickets/{id}` | Карточка тикета (с перепиской) |

**Фильтры**: статус, приоритет, категория, исполнитель, SLA-нарушения

## 📋 Equipment Requests (Заявки)

| URL | Страница |
|-----|----------|
| `/admin/equipment-requests` | Список заявок |
| `/admin/equipment-requests/{id}` | Карточка заявки |

## 📈 Reports (Отчёты)

| URL | Страница |
|-----|----------|
| `/admin/reports/sales` | Отчёт по продажам |
| `/admin/reports/leads` | Отчёт по лидам |
| `/admin/reports/managers` | Отчёт по менеджерам |
| `/admin/reports/funnel` | Воронка продаж |

## ⚙ Settings (Настройки)

| URL | Страница |
|-----|----------|
| `/admin/settings/general` | Общие |
| `/admin/settings/users` | Пользователи системы |
| `/admin/settings/roles` | Роли и права |
| `/admin/settings/templates` | Шаблоны (КП, инвойсов) |
| `/admin/settings/notifications` | Настройки уведомлений |
| `/admin/settings/integrations` | Интеграции (1С, Telegram) |
| `/admin/settings/audit-log` | Журнал действий |

## 🔢 Итого страниц

~ 35-40 уникальных страниц в админке.

## 📅 Phase 1 (минимально живой prototype)

Для первого работающего MVP достаточно:
- Login
- Dashboard (с заглушками для KPI)
- Leads (список + создание + просмотр)
- Customers (список + создание + просмотр)
- Settings → Users + Roles

≈ 8-10 страниц для запуска.
