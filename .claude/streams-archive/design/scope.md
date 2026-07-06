# Stream: Design — Scope

## ✅ Что входит в этот поток

- **UI/UX дизайн**: wireframes, mock'ы, design system
- **Blade-шаблоны**: layouts, partials, страницы
- **Tailwind**: config, кастомные классы, темы
- **Alpine.js**: интерактивность UI (без бизнес-логики)
- **Иконки и графика**: heroicons, Chart.js настройки
- **Адаптивность**: responsive design, breakpoints
- **UI-компоненты**: x-card, x-button, x-badge и пр.

## 🚫 Что НЕ входит

- ❌ Eloquent-модели и их связи → **database stream**
- ❌ Миграции БД → **database stream**
- ❌ Livewire-логика (методы, валидация, queries) → **backend stream**
- ❌ Контроллеры, сервисы, политики → **backend stream**

## 📁 Файлы этого потока

```
resources/
├── css/app.css
├── js/app.js
└── views/                     ← все Blade
    ├── layouts/
    ├── admin/
    ├── portal/
    └── components/             ← x-компоненты
app/View/Components/            ← классы для x-компонентов (без логики)
tailwind.config.js
postcss.config.js
.claude/artifacts/admin-panel/  ← дизайн-документация
```

## 🤝 Точки соприкосновения

- **С database**: ждём финальный список полей моделей → отрисовываем формы
- **С backend**: договариваемся о данных, которые компонент получает от Livewire (props, slots)

## 🎯 Definition of Done для дизайн-задачи

- ✅ Wireframe в `.claude/artifacts/admin-panel/wireframes/`
- ✅ Blade-шаблон работает в браузере
- ✅ Адаптивность проверена (mobile, tablet, desktop)
- ✅ Соответствует design-system.md
- ✅ Permission-checks (`@acl`) на месте, где нужны
