# Design Stream — Handoffs

Запросы к другим потокам и ответы от них.

Формат:
- **TO {stream} | YYYY-MM-DD | OPEN/RESOLVED**
- Что нужно
- Контекст / зачем

---

## TO database | 2026-04-28 | OPEN
**Запрос**: Финальный список полей для основных моделей (Customer, Lead, Quote, Product, Ticket).
**Контекст**: Нужно для построения форм и таблиц. Сейчас в wireframes использованы примерные поля. Когда database stream определит точную схему — обновим формы.

## TO backend | 2026-04-28 | OPEN
**Запрос**: Контракты компонентов Livewire — какие props/slots передаём в каждый дизайн-компонент.
**Контекст**: Например, `<x-stat-card label="..." value="..." trend="..." />` — backend должен знать что передать. Возможно, оформим в `shared/data-contracts.md`.
