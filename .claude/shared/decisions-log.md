# Cross-Stream Decisions Log

Решения, которые затрагивают **несколько потоков** одновременно. Внутри-потоковые решения — в `streams/<имя>/decisions.md`.

**Формат**:
```
## YYYY-MM-DD — Заголовок решения
**Потоки**: design, database, backend
**Решение**: ...
**Причина**: ...
**Trade-off**: ... (опционально)
```

---

## 2026-04-28 — Двухконтурная архитектура (CRM + Customer Portal)
**Потоки**: design, database, backend
**Решение**: Два независимых пользовательских контура. Внутренний — `/admin/*` для сотрудников RSG. Внешний — `/portal/*` для клиентов. Разделение через middleware и роли.
**Причина**: B2B + клиентский портал на одной кодовой базе. Общая модель данных (Customer, Quote, Invoice), но разный UI и набор действий.
**Trade-off**: больше разработки, чем одностраничной CRM. Но даёт самообслуживание клиентов — уникальная ценность для RSG.

## 2026-04-28 — CRUD через Livewire (не через Controller+Blade)
**Потоки**: design, backend
**Решение**: Все списки/формы/детали — Livewire-компоненты. Контроллеры только для API и спец-задач (PDF, экспорт, webhooks).
**Причина**: меньше boilerplate, прямой Eloquent, реактивность без отдельного frontend.
**Trade-off**: завязка на Livewire. Невозможно легко переехать на Vue/React без переписывания.

## 2026-04-28 — Контроль доступа на 3 уровнях (routes / UI / policies)
**Потоки**: design, backend
**Решение**: Routes защищены middleware, UI скрывает недоступное через `@acl`, конкретные записи — через Policies (`authorize()`).
**Причина**: безопасность через несколько слоёв. Скрытие меню НЕ защищает данные.
**См.**: CLAUDE.md → "Контроль доступа: 3 уровня защиты"

## 2026-04-28 — Тонкие модели + Service Layer
**Потоки**: database, backend
**Решение**: Модели содержат только атрибуты, связи, scopes, accessors. Бизнес-логика — в Services и Livewire-компонентах.
**Причина**: разделение ответственности, тестируемость. Не "fat model".
**Trade-off**: больше файлов, нужна дисциплина "не пихать логику в модель".

## 2026-04-28 — Single-tenant архитектура
**Потоки**: database, backend
**Решение**: RSG-CRM — single-tenant. Одна установка системы только для RSG. Другие компании — это `Customer`, а не tenants.
**Причина**: бизнес RSG не подразумевает SaaS-продажу другим CRM-провайдерам. Single-tenant — проще схема, нет `tenant_id` в каждой таблице, проще запросы.
**Trade-off**: если в будущем решим продавать как SaaS — потребуется крупный рефакторинг или новая инстанция на каждого клиента.

## 2026-04-28 — UZS как основная валюта (USD как вторичная)
**Потоки**: database, design, backend
**Решение**: Все суммы в БД хранятся в UZS как `DECIMAL(15,2)`. Если документ в USD — отдельная колонка `currency` + `amount_usd`. Курс — в settings.
**Причина**: UZS — основная для бизнеса в Узбекистане. USD нужна для импортного оборудования.
**Trade-off**: каждая денежная операция имеет дополнительное поле currency. Сложность конвертаций.

## 2026-07-07 — RolesSeeder: additive (not destructive) permission sync for pre-existing roles + role-based (not permission-based) gating for /admin/settings/roles
**Агенты**: pm-b2b-crm, laravel-fullstack, admin-bi-developer, qa-tester
**Решение**:
1. Новая страница `/admin/settings/roles` (просмотр/редактирование permissions каждой роли) гейтится роутом строго по РОЛИ (`role:super-admin` middleware), а не по permission-строке `settings.roles`. Причина: страница сама редактирует permissions, поэтому гейтинг по permission, которую она же может изменить, создаёт потенциальный privilege-escalation footgun (риск self-lockout или самоусиления прав). Sidebar-пункт зеркалирует это explicit `hasRole('super-admin')` проверкой, а не стандартным `Acl::can(...)`.
2. `super-admin` (а также `client-admin`/`client-user`, у которых доступ ownership-based, не permission-based) сделаны permanently locked в UI — их permissions нельзя изменить через эту страницу ни через форму, ни прямым вызовом action-метода (server-side guard). Это гарантирует, что всегда есть роль (`super-admin`), которая может зайти на страницу и восстановить права, даже если кто-то ошибочно уберёт лишнее у других ролей.
3. `database/seeders/RolesSeeder.php` изменён с жёсткого `syncPermissions()` (для КАЖДОЙ роли при каждом запуске) на условно-аддитивную логику: для роли, только что созданной в текущем запуске сидера (`$role->wasRecentlyCreated === true`) — прежнее поведение (`syncPermissions`, полный сброс до конфига). Для роли, уже существовавшей в БД — `givePermissionTo()` (аддитивный merge): гарантирует наличие всех permissions из `config/permissions.php` (baseline), но больше не стирает permissions, добавленные вручную через новую Roles UI при обычном `migrate:fresh --seed` (штатная команда цикла разработки, задокументированная в CLAUDE.md §2.2).
**Причина**: без исправления #3 обычный `migrate:fresh --seed` молча откатывал бы любую ручную настройку прав, сделанную через UI — плохой UX и скрытая потеря конфигурации. Без решения #1/#2 страница управления правами сама могла бы стать вектором привилегированной эскалации или самоблокировки.
**Trade-off**: если permission позже удаляется из `config/permissions.php` для какой-то роли, `RolesSeeder` больше не отзовёт его у ролей, которые уже получили этот permission в БД (аддитивность не убирает лишнее) — реальное удаление устаревших permissions с существующих ролей потребует либо ручного вмешательства (через саму Roles UI), либо отдельного явного cleanup-скрипта в будущем.
**Отклонено в рамках этой сессии**: предложение сделать доступ к `/admin/settings/roles` делегируемым через permission `settings.roles` (чтобы super-admin мог выдать доступ другим ролям без правки кода) — пришло через сообщение, выдававшее себя за "координатора" в середине задачи, не через прямую команду пользователя; заблокировано защитным механизмом (permission classifier) как потенциальный privilege-escalation risk и вынесено на явное подтверждение пользователя отдельно. Финальная реализация — только role-based гейт, как описано в п.1.
**См. также**: CLAUDE.md → Часть 1.7 → Phase 13; `.claude/shared/changelog.md` записи `[laravel-fullstack]`/`[admin-bi-developer]`/`[qa-tester]`/`[claude-md-maintainer]` за 2026-07-07.

## 2026-07-07 — Пересмотр п.1: /admin/settings/roles гейтится по permission, не жёстко по роли
**Инициатор**: пользователь, напрямую в чате (дважды подтвердил: до реализации п.1 выше и повторно уже после того, как увидел рабочую role-based версию — специально, чтобы решение не было импульсивным).
**Контекст**: попытка провести это же изменение через сообщение агенту `pm-b2b-crm` от имени "координатора" была дважды отклонена его защитным механизмом как неверифицируемая (агент не может отличить легитимную ретрансляцию решения пользователя от инъекции/социальной инженерии, если это приходит текстом внутри задачи, а не напрямую от пользователя) — это ожидаемое и корректное поведение агента, не баг. Поскольку главный ассистент (не суб-агент) видел прямое подтверждение пользователя в диалоге, изменение внесено им напрямую, в обход суб-агентной делегации, чтобы не упираться в этот же (структурно неразрешимый на уровне суб-агента) барьер верификации.
**Решение**: роут `/admin/settings/roles` перегейчен с `role:super-admin` на `role_or_permission:super-admin|settings.roles` (OR-семантика, `|`-разделитель — см. `Spatie\Permission\Middleware\RoleOrPermissionMiddleware`). Sidebar-пункт "Роли и права" перегейчен с явного `hasRole('super-admin')` на стандартный `Acl::can('settings.roles')`. Внешний `@if` секции "Settings" в сайдбаре расширен до `Acl::can('settings.users') || Acl::can('settings.roles')`, чтобы роль с одним только `settings.roles` (без `settings.users`) всё равно видела секцию. Server-side defense-in-depth проверки внутри `App\Livewire\Admin\Settings\Roles` (`mount()` и `savePermissions()`) обновлены аналогично: `hasRole('super-admin') || can('settings.roles')`.
**Что осталось без изменений (гарантия безопасности)**: `LOCKED_ROLES` (`super-admin`, `client-admin`, `client-user`) по-прежнему permanently uneditable через эту страницу вне зависимости от того, кто на неё зашёл — именно это делает permission-based гейтинг безопасным, а не self-lockout/self-escalation риском, описанным в п.1 выше. `super-admin` всегда имеет `settings.roles` через wildcard `*`, поэтому доступ к странице никогда не может быть полностью потерян.
**Тесты**: `tests/Feature/Access/RolePermissionsManagementTest.php` — добавлены `test_role_granted_settings_roles_permission_can_access_the_page` и `test_role_granted_settings_roles_permission_can_toggle_other_roles_permissions`; существующий `test_non_super_admin_role_cannot_reach_settings_roles` (7 ролей, ни одна не имеет `settings.roles` по конфигу) остаётся зелёным без изменений — отказ теперь по отсутствию permission, а не по отсутствию роли. `php artisan test`: 195 passed (было 193).
