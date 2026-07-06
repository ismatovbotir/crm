# Backend Stream — Handoffs

## TO design | 2026-05-11 | RESOLVED
**Запрос**: Создать Blade-шаблон `resources/views/livewire/admin/documents/create-form.blade.php`
**Выполнено**: blade создан 2026-05-11, 3-зонный layout, все условия по `$type` реализованы

## TO database | 2026-04-28 | OPEN
**Запрос**: Уточнить состав полей моделей `Lead` и `Customer`.
**Контекст**: Нужно чтобы спроектировать формы (CreateForm) и валидацию (Form Requests). Особенно интересуют: source как enum или связь? Lead.score — int 1-10? Lead.budget — DECIMAL?

## TO design | 2026-04-28 | OPEN
**Запрос**: Окончательные wireframes для slide-over форм Lead.
**Контекст**: В wireframes/lead-form.md есть базовая версия. Если в дизайне поменяется состав полей — backend быстро адаптирует.
