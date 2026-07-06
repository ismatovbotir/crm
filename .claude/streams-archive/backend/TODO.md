# Backend Stream — TODO

**Last verified**: 2026-07-06 — сверено с реальным кодом (`app/Livewire`, `app/Policies`, `app/Services`, `app/Http/Controllers`, `composer.json`/`vendor/`), не только с changelog.

## 🔴 P0 (блокеры — приложение не запустится в проде без этого)

- [ ] `composer require spatie/laravel-permission` — пакета нет ни в `composer.json`, ни в `vendor/`, хотя `App\Models\User` использует `HasRoles`, `Setup.php` и `Settings\Users` — классы `Spatie\Permission\Models\Role/Permission`, а миграция `2026_04_28_110307_create_permission_tables.php` их таблицы создаёт. Роли/права сейчас работают только если пакет кто-то ставил вручную вне git-состояния репозитория.
- [ ] `composer require barryvdh/laravel-dompdf` — `PdfService` вызывает `Barryvdh\DomPDF\Facade\Pdf`, пакета нет в vendor. Кнопки "Скачать PDF" (КП/Инвойс) упадут с ошибкой класса.
- [ ] После установки обоих — `php artisan migrate:fresh --seed`.

## ✅ Функциональность реализована (детали по фазам — `streams/backend/STATUS.md`)

Подтверждено по факту в коде, Phase 1–10 полностью на месте:
- Auth, `Acl` helper, Blade-директивы `@acl/@isInternal/@isClient`
- Leads, Customers — полный CRUD + Policies
- Catalog: Categories, Products, Serials, Groups, Recommendations
- Quotes/Invoices: unified `Documents\CreateForm`, `Quotes\EditForm`, PDF, конвертация КП→Инвойс, частичные оплаты
- Tickets: внутр./публ. комментарии, serial lookup, external equipment registration
- Sells, Returns — полный цикл возврата с serial tracking (`SerialService`, `ReturnService`)
- EquipmentRequests
- Portal: Dashboard, Quotes, Invoices, Tickets, Catalog, Profile, Equipment ("Мои устройства")
- Reports (`Admin\Reports\Index` — KPI, funnel, top-manager, overdue invoices)
- Settings\Users — управление пользователями и ролями через UI
- Export (CSV: leads/customers/invoices) + Import (CSV каталог, шаблон, отчёт об ошибках)
- Notifications: `QuoteViewedNotification`, `QuoteAcceptedNotification`, `NewTicketNotification` (email); `TelegramService` (используется в `Portal\Tickets\CreateForm`, `Portal\Quotes\Show`)

## 🟡 P1 (реальные пробелы)

- [ ] Тесты — `tests/Feature` и `tests/Unit` содержат только сгенерированный `ExampleTest.php`. CLAUDE.md §2.7 требует покрытие минимум: создание лидов, конвертация КП→Инвойс, генерация инвойсов, права доступа. Ничего из этого не написано.
- [ ] `streams/backend/STATUS.md` не упоминает Sells, EquipmentRequests, Reports, Settings\Users, Export/Import, TelegramService как готовые модули — стоит актуализировать при следующем `/backend`-сеансе.

## 🟢 P2 (опционально)

- [ ] API Sanctum endpoints для мобильного приложения
- [ ] Аудит-лог (`spatie/laravel-activitylog`) — тоже отсутствует в vendor/composer.json, потребует отдельной установки
- [ ] `.env`: `MAIL_MAILER=log` сейчас (письма не отправляются реально) — настроить SMTP для prod
