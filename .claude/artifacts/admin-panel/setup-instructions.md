# Setup Instructions — Admin Panel

Инструкция по установке зависимостей и запуску админ-панели.

## 1. Установить зависимости (через Laragon Terminal)

```bash
# Tailwind CSS + плагины
npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms @tailwindcss/typography

# Alpine.js для интерактивности
npm install alpinejs

# Иконки (Heroicons как Blade компоненты)
composer require blade-ui-kit/blade-heroicons

# Chart.js для графиков
npm install chart.js

# Initialize Tailwind config
npx tailwindcss init -p
```

## 2. Конфигурация Tailwind

Заменить `tailwind.config.js` содержимым из `.claude/artifacts/admin-panel/files/tailwind.config.js` (создаётся скриптом setup).

## 3. CSS entrypoint

Заменить `resources/css/app.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

## 4. JS entrypoint

Заменить `resources/js/app.js`:
```js
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

## 5. Запуск

```bash
# Терминал 1: Laravel
php artisan serve

# Терминал 2: Vite (HMR)
npm run dev
```

Открыть: `http://localhost:8000/admin`

## 6. Готовые шаблоны

Все Blade-шаблоны лежат в `.claude/artifacts/admin-panel/files/`:
- `tailwind.config.js` — конфиг с palette
- `views/layouts/admin.blade.php` — layout
- `views/admin/partials/sidebar.blade.php` — сайдбар
- `views/admin/partials/header.blade.php` — хедер
- `views/admin/dashboard.blade.php` — пример дашборда
- `views/components/*.blade.php` — UI-компоненты

Скопировать в проект:
```bash
cp -r .claude/artifacts/admin-panel/files/views/* resources/views/
cp .claude/artifacts/admin-panel/files/tailwind.config.js ./
```
