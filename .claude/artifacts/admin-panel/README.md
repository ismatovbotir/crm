# RSG-CRM Admin Panel — Design Artifacts

Артефакты дизайна админ-панели RSG-CRM. Стиль вдохновлён [react-free.tailwind-admin.com](https://react-free.tailwind-admin.com/) (light version).

## 📁 Структура

```
admin-panel/
├── README.md                       # Этот файл — навигация
├── design-system.md                # Цвета, типографика, отступы, иконки
├── layout-structure.md             # Wireframe: сайдбар + хедер + контент
├── components.md                   # Список переиспользуемых компонентов
├── pages-inventory.md              # Перечень всех страниц админки
├── setup-instructions.md           # Как установить и запустить
├── wireframes/                     # Детальные mock'ы экранов
│   ├── login.md                    # Страница входа
│   ├── dashboard.md                # Главная админки
│   ├── leads-list.md               # Список лидов (+ канбан)
│   ├── lead-detail.md              # Карточка лида
│   ├── lead-form.md                # Форма создания/редактирования лида
│   ├── customers.md                # Клиенты (список + карточка)
│   ├── quote-create.md             # Создание КП (самая сложная форма)
│   ├── tickets.md                  # Тикеты (список + карточка)
│   ├── catalog-products.md         # Каталог товаров
│   └── portal-home.md              # Главная клиентского портала
└── files/                          # Готовый к копированию код
    ├── tailwind.config.js
    └── views/
        ├── layouts/
        │   └── admin.blade.php
        └── admin/
            ├── partials/
            │   ├── sidebar.blade.php
            │   └── header.blade.php
            └── dashboard.blade.php
```

## 📋 Что покрыто

### Базовая система
- ✅ Цветовая палитра (light theme + 4 семантических цвета)
- ✅ Типографика (Inter font, размеры заголовков)
- ✅ Spacing, тени, скругления
- ✅ Базовые компоненты (buttons, badges, cards)

### Layout
- ✅ Двухколоночный layout (sidebar + content)
- ✅ Sticky header с поиском и user menu
- ✅ Адаптивное поведение (collapse sidebar на mobile)

### Страницы (10 wireframe'ов)
- ✅ Login
- ✅ Dashboard (с KPI и графиками)
- ✅ Leads (список + канбан + карточка + форма)
- ✅ Customers (список + карточка)
- ✅ Quotes/КП (форма создания со всей сложной механикой)
- ✅ Tickets (список + карточка с перепиской)
- ✅ Catalog Products (карточный/списочный вид)
- ✅ Customer Portal (главная)

### Готовый код
- ✅ Tailwind config с фирменной палитрой
- ✅ Базовый layout (Blade)
- ✅ Sidebar + Header partials
- ✅ Sample dashboard с KPI и Chart.js

## 🚀 Применение в проекте

См. [setup-instructions.md](setup-instructions.md) — пошаговая инструкция:
1. Установить npm-пакеты
2. Скопировать `tailwind.config.js` в корень
3. Скопировать `views/*` в `resources/views/`
4. Запустить `npm run dev`

## 🎯 Что осталось спроектировать (Phase 2+)

- Invoice форма (по аналогии с Quote)
- Equipment Request список и карточка
- Reports (Sales / Funnel / Managers)
- Settings → Users/Roles/Permissions UI
- Portal: каталог + корзина + чекаут
- Portal: Quote view (вью КП клиентом с акцептом)

Эти экраны можно проектировать по мере приближения к соответствующей фазе разработки.
