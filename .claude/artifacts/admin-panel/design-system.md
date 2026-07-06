# Design System — RSG-CRM Admin Panel

Спецификация визуального языка админ-панели. Light theme.

## 🎨 Цветовая палитра

### Базовые (нейтральные)
```
gray-50    #F9FAFB   — фон страницы
gray-100   #F3F4F6   — фон карточек, полей
gray-200   #E5E7EB   — бордеры, разделители
gray-300   #D1D5DB   — disabled-состояния
gray-500   #6B7280   — вторичный текст, иконки
gray-700   #374151   — основной текст
gray-900   #111827   — заголовки
white      #FFFFFF   — карточки, сайдбар
```

### Акцентные (фирменные)
```
primary-50    #EFF6FF   — hover/active в меню
primary-100   #DBEAFE   — фон бейджей
primary-500   #3B82F6   — primary buttons, ссылки
primary-600   #2563EB   — hover на кнопках
primary-700   #1D4ED8   — pressed state
```

### Семантические (статусы)
```
success-500   #10B981   — успех, paid, won, active
warning-500   #F59E0B   — внимание, pending, draft
danger-500    #EF4444   — ошибка, lost, overdue
info-500      #06B6D4   — информация, в работе
```

## 📐 Типографика

**Шрифт**: `Inter` (загружать через Google Fonts) или `system-ui` fallback

```
Заголовок страницы (H1):  text-2xl font-semibold (24px / 600)
Заголовок секции (H2):    text-xl font-semibold  (20px / 600)
Заголовок карточки (H3):  text-lg font-medium    (18px / 500)
Подзаголовок:             text-sm font-medium    (14px / 500)
Body text:                text-sm                (14px / 400)
Caption / labels:         text-xs                (12px / 400)
KPI numbers:              text-3xl font-bold     (30px / 700)
```

## 📏 Отступы и радиусы

```
Padding карточек:         p-6 (24px)
Padding мелких элементов: p-3 / p-4
Gap между секциями:       gap-6 (24px)
Border radius карточек:   rounded-lg (8px)
Border radius кнопок:     rounded-md (6px)
Border radius badges:     rounded-full
```

## 🌟 Тени

```
Карточка (default):   shadow-sm
Карточка (hover):     shadow-md
Dropdown / modal:     shadow-lg
```

## 🔘 Кнопки

### Primary
```html
<button class="bg-primary-600 text-white px-4 py-2 rounded-md font-medium hover:bg-primary-700 transition">
  Сохранить
</button>
```

### Secondary
```html
<button class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-md font-medium hover:bg-gray-50 transition">
  Отмена
</button>
```

### Danger
```html
<button class="bg-danger-500 text-white px-4 py-2 rounded-md font-medium hover:bg-danger-600 transition">
  Удалить
</button>
```

### Icon-only
```html
<button class="p-2 rounded-md hover:bg-gray-100 text-gray-500 hover:text-gray-700">
  <svg class="w-5 h-5">...</svg>
</button>
```

## 🏷 Бейджи статусов

```
New:           bg-primary-100 text-primary-700
Qualified:     bg-info-100 text-info-700
In Negotiation:bg-warning-100 text-warning-700
Won / Paid:    bg-success-100 text-success-700
Lost / Failed: bg-danger-100 text-danger-700
Draft:         bg-gray-100 text-gray-700
```

## 📊 Карточки KPI

```
┌─────────────────────────────┐
│ ICON  Метрика               │
│       1,234 ▲ 12% ←─ trend  │
│       Прошлый месяц         │
└─────────────────────────────┘
```

- Иконка слева в круге `bg-primary-100 text-primary-600 p-3 rounded-full`
- Число `text-3xl font-bold text-gray-900`
- Подпись `text-sm text-gray-500`
- Trend `text-success-500` или `text-danger-500`

## 🖼 Иконки

Использовать **Heroicons v2** (outline для меню, solid для статусов).

Подключение:
- Inline SVG в Blade-партиалах (быстрее, без runtime overhead)
- Размеры: `w-5 h-5` для меню, `w-4 h-4` в строках таблиц, `w-6 h-6` в KPI

## 📱 Responsive breakpoints

```
sm:  640px   — телефоны (минимально поддерживаемый)
md:  768px   — планшеты (основной для портала)
lg:  1024px  — десктоп (основной для админки)
xl:  1280px  — широкий десктоп (оптимальный)
```

**Поведение**:
- На `< lg`: сайдбар скрывается, открывается через гамбургер
- На `>= lg`: сайдбар фиксированный 260px

## ✨ Анимации

- Все переходы: `transition` (по умолчанию 150ms)
- Дропдауны: `transition ease-out duration-100` (открытие), `ease-in duration-75` (закрытие)
- Hover на кнопках: только цвет, без масштабирования
