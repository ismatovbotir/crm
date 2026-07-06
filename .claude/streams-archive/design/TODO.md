# Design Stream — TODO

**Last verified**: 2026-07-06 — сверено с `resources/views/livewire/` и `resources/views/components/`.

## ✅ Всё из P1 реализовано

Все x-компоненты (`<x-card>`, `<x-button>`, `<x-badge>`, `<x-modal>`, `<x-slide-over>`, статус-бейджи), Login blade, все CRM+Portal экраны по фазам 1–10 — см. `streams/design/STATUS.md`.

## 🟡 Обнаружено, не отражено в STATUS.md

- [ ] `livewire/admin/reports/index.blade.php` и `livewire/admin/settings/users.blade.php` существуют и реализуют backend-функциональность (`Admin\Reports\Index` — KPI/воронка/топ-менеджеры, `Admin\Settings\Users` — управление пользователями/ролями), но design STATUS их не упоминает — стоит проверить, нужна ли доп. полировка вёрстки этих экранов.
- [ ] Import UI (`catalog/products/index.blade.php`) отображает `import_errors` списком через session flash — рабочее, но минималистичное решение; можно улучшить (прогресс, форматирование ошибок построчно).

## 🟢 P2 (потом, из STATUS.md "Следующее")

- [ ] Wireframes/полировка для остальных Settings-разделов (кроме Users)
- [ ] Dark theme
- [ ] Print-styles для PDF-документов (КП, инвойсы)
- [ ] Email-templates HTML (сейчас стандартный Laravel `MailMessage` markdown-стиль)
- [ ] Responsive/мобильная адаптация
