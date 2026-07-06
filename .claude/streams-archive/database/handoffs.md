# Database Stream — Handoffs

## TO design | 2026-04-28 | RESOLVED ✅
**Запрос**: Подтвердить какие поля нужны в карточках/списках клиентов и лидов.
**Ответ (2026-04-28)**: Спроектирована полная ER-диаграмма в `shared/data-contracts.md` на основе wireframes. Все поля учтены. Дизайн может проверить и сообщить если что-то добавить.

## TO design | 2026-04-28 | OPEN
**Запрос**: Просмотри ER-диаграмму в `shared/data-contracts.md` и подтверди что для всех wireframes хватает полей. Особенно проверь:
- Customer: достаточно ли полей реквизитов, контактов
- Lead: достаточно ли полей квалификации
- Quote: учтены ли валюты, скидки, версионирование
- Product: фото, документы, цены — всё на месте?

## TO backend | 2026-04-28 | OPEN
**Запрос**: Какие scopes / accessors нужны на моделях?
**Контекст**: Сейчас в плане:
- `Lead::scopeForUser($userId)` — только лиды конкретного менеджера
- `Lead::scopeOpen()` — кроме won/lost
- `Customer::scopeActive()` — без inactive/blocked
- `Quote::scopeOpen()` — draft/sent/viewed
- `User::scopeManagers()` — только сотрудники с ролью sales-manager
Дополнить если нужно ещё.

## TO design + backend | 2026-04-28 | OPEN — НУЖНО РЕШИТЬ
Открытые вопросы по ER-диаграмме (см. конец `data-contracts.md`):
1. Подтвердить enum для `Customer.business_type`: shop / restaurant / pharmacy / warehouse / other — достаточно?
2. Подтвердить enum для `Customer.status`: active / vip / inactive / blocked
3. Подтвердить enum для `Lead.lost_reason`: price / timing / competitor / other
4. Мультиязычность товаров — нужна или хватит русского?
5. `product.specs` JSON — гибкая (любые ключи) или предопределённый список ключей?
