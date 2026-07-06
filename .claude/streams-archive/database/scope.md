# Stream: Database — Scope

## ✅ Что входит в этот поток

- **Схема БД**: ER-диаграмма, нормализация, индексы
- **Миграции**: `database/migrations/*.php`
- **Eloquent-модели**: `app/Models/*.php`
- **Связи моделей**: hasMany, belongsTo, belongsToMany, polymorphic
- **Фабрики**: `database/factories/*.php`
- **Сидеры**: `database/seeders/*.php`
- **Soft deletes, observers, scopes** на уровне моделей
- **Документация структуры данных** → `shared/data-contracts.md`

## 🚫 Что НЕ входит

- ❌ Blade-шаблоны и формы → **design stream**
- ❌ Livewire-компоненты и их методы → **backend stream**
- ❌ Контроллеры, политики, сервисы → **backend stream**
- ❌ Бизнес-логика создания/обработки данных → **backend stream**

Модели должны быть "тонкими": атрибуты, связи, scopes, accessors. Без бизнес-логики.

## 📁 Файлы этого потока

```
database/
├── migrations/
├── factories/
└── seeders/
app/Models/                    ← только модели и связи
config/permissions.php          ← shared (но database готовит сидер ролей)
```

## 🤝 Точки соприкосновения

- **С design**: предоставляем финальную структуру полей → дизайн строит формы
- **С backend**: модели — это API для Livewire/сервисов; договариваемся о scopes и accessors

## 🎯 Definition of Done для database-задачи

- ✅ Миграция создана и обратима (есть `down()`)
- ✅ Модель с правильными `$fillable`, связями, кастами типов
- ✅ Фабрика для тестов
- ✅ Сидер с базовыми данными (если применимо)
- ✅ Запись в `shared/data-contracts.md`
- ✅ Запись в `shared/changelog.md`
