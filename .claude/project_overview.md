---
name: Project Overview - RSG-CRM
description: Бизнес-цели, функциональные модули и текущий статус CRM-системы для RSG (rsg.uz)
type: project
---

# RSG-CRM Project Overview

**Status**: Foundation phase (по состоянию на 2026-04-27)

## Компания и сфера

- **Компания**: RSG (rsg.uz)
- **Бизнес**: продажа торгового оборудования (POS, весы, сканеры, принтеры) и автоматизация учёта для ритейла (магазины, рестораны, склады, аптеки)
- **Регион**: Узбекистан (валюты UZS, USD)
- **Тип**: B2B

## Цель проекта

Полный цикл управления клиентом:
**Лид → КП → Инвойс → Поставка → Тех. поддержка → Повторные продажи**

## Двухконтурная архитектура

1. **Внутренний контур (CRM)** — для сотрудников RSG (продавцы, тех. поддержка, склад, бухгалтерия)
2. **Клиентский портал** — для клиентов RSG (просмотр КП, самостоятельные заказы, тикеты)

## Функциональные модули (полный TZ — в CLAUDE.md)

1. **Lead Management** — захват и квалификация лидов
2. **Customer Management** — клиенты, контактные лица, сегменты
3. **Commercial Offers (КП)** — создание, версионирование, PDF, отправка с трекингом
4. **Invoicing** — инвойсы, платежи, НДС, экспорт в 1С
5. **Product Catalog** — иерархия категорий, многоуровневые цены, остатки
6. **Equipment Requests** — заявки от клиентов на нестандартное оборудование
7. **Ticket System** — тех. поддержка с SLA и приоритетами
8. **Customer Portal** — личный кабинет клиента
9. **Internal Operations** — дашборды, отчёты, уведомления, аудит

## Роли пользователей

**Внутренние**: Super Admin, Sales Director, Sales Manager, Technical Support, Catalog Manager, Warehouse Manager, Accountant
**Внешние**: Client Admin, Client User

## Ключевые сущности (Data Model)

- `User`, `Role`, `Permission` (Spatie)
- `Customer` (компания), `Contact` (контактное лицо)
- `Lead` → `Quote` (КП) → `Invoice` → `Payment`
- `Product`, `Category`, `ProductPrice`, `ProductStock`
- `EquipmentRequest`
- `Ticket`, `TicketComment`

## Roadmap

| Phase | Описание | Статус |
|-------|----------|--------|
| 0 | Foundation (Laravel scaffold) | ✅ Done |
| 1 | Auth + Roles (Spatie Permission) | 🔄 Текущий |
| 2 | Customer & Lead Management | ⬜ |
| 3 | Catalog & Pricing | ⬜ |
| 4 | Quotes + Invoicing | ⬜ |
| 5 | Customer Portal | ⬜ |
| 6 | Tickets + Equipment Requests | ⬜ |
| 7 | Reports + Notifications + Integrations (1C, Telegram) | ⬜ |
| 8 | Polish & Launch | ⬜ |

## Стек

Laravel 10.10 + Livewire 4.2 + Sanctum + MySQL + Vite + PHPUnit + Pint
