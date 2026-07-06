# Stream: Backend — Scope

## ✅ Что входит

- **Livewire-компоненты**: `app/Livewire/**/*.php` (только PHP-логика)
- **API-контроллеры**: `app/Http/Controllers/Api/`
- **Form Requests**: валидация
- **Policies**: авторизация на уровне записи
- **Services**: бизнес-логика, не привязанная к одной модели
- **Notifications, Mail, Jobs, Events, Observers, Middleware**
- **Routes**: `routes/web.php`, `routes/api.php`

## 🚫 Что НЕ входит

- ❌ Blade-шаблоны → **design stream**
- ❌ Миграции и схема БД → **database stream**
- ❌ Eloquent-связи и атрибуты моделей → **database stream**

Модели — тонкие (атрибуты + связи). Бизнес-логика в Services и Livewire.

## 🤝 Точки соприкосновения

- **С database**: используем модели как API
- **С design**: договариваемся о props компонентов и событиях

## 🎯 Definition of Done

- ✅ Реализован Livewire-компонент / контроллер
- ✅ Валидация
- ✅ Авторизация (`$this->authorize(...)` + middleware)
- ✅ Тесты (минимум — Feature на главные сценарии)
- ✅ Permission-проверки в Blade и на роутах
- ✅ Запись в `shared/changelog.md`
