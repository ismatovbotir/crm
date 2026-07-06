# API Contracts — REST endpoints

**Owner**: backend stream
**Readers**: все потоки (особенно если будет мобильное приложение или интеграции)

Контракт REST API для мобильного клиента, веб-хуков и интеграций. Web-интерфейс работает через Livewire (без API).

---

## Status: 🚧 Не разработано

API-эндпойнты будут спроектированы при необходимости (мобильное приложение / интеграция с rsg.uz формами / 1С webhook).

---

## Структура (когда появится)

### Authentication
- `POST /api/auth/login` — выдача Sanctum-токена
- `POST /api/auth/logout` — отзыв токена
- `GET /api/auth/me` — текущий пользователь

### Webhooks (входящие)
- `POST /api/webhooks/lead-form` — лид с сайта rsg.uz
- `POST /api/webhooks/payment` — уведомление от платёжной системы

### Public API (с Sanctum-токеном)
- `GET /api/leads` — список лидов
- `POST /api/leads` — создать лид
- (будут детализированы)

---

## Conventions

- Все эндпойнты возвращают JSON
- Базовый префикс: `/api/v1/`
- Авторизация: Bearer token (Sanctum) в заголовке `Authorization`
- Ошибки: стандартный Laravel ValidationException формат (`{ errors: { field: [...] } }`)
- Pagination: Laravel paginator (`{ data: [...], links, meta }`)
- Даты: ISO 8601 (`2026-04-28T14:30:00+05:00`)
- Деньги: всегда строкой с явной валютой (`{ amount: "12500000.00", currency: "UZS" }`)

---

## ✏️ Как обновлять

1. **Backend stream** проектирует и валидирует контракт
2. **Design stream** читает чтобы знать, что мобильное приложение умеет
3. **Database stream** обеспечивает чтобы модели поддерживали нужные поля
