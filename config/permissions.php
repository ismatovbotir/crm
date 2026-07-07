<?php

/**
 * RSG-CRM — единая карта ролей и прав (Spatie Permission).
 *
 * Источник истины для:
 *   - database/seeders/RolesSeeder.php (создаёт permissions + роли)
 *   - app/Livewire/Admin/Setup.php (first-run инициализация)
 *   - сайдбара (resources/views/admin/partials/sidebar.blade.php)
 *   - App\Helpers\Acl
 *
 * См. CLAUDE.md → "2.4. Структура permissions (Spatie)" и "Базовые роли (внутренние)".
 *
 * Формат:
 *   'permissions' => ['<group>' => ['<permission.key>' => '<человеко-читаемое описание>']]
 *   'roles'       => ['<role-name>' => ['guard' => 'web', 'permissions' => [...] | ['*']]]
 */

return [

    'permissions' => [

        'leads' => [
            'leads.view'   => 'Просмотр лидов',
            'leads.create' => 'Создание лидов',
            'leads.update' => 'Редактирование лидов',
            'leads.delete' => 'Удаление лидов',
            'leads.assign' => 'Назначение ответственного менеджера',
        ],

        'customers' => [
            'customers.view'   => 'Просмотр клиентов',
            'customers.create' => 'Создание клиентов',
            'customers.update' => 'Редактирование клиентов',
            'customers.delete' => 'Удаление клиентов',
            'customers.export' => 'Экспорт клиентов (CSV)',
        ],

        'quotes' => [
            'quotes.view'   => 'Просмотр коммерческих предложений',
            'quotes.create' => 'Создание КП',
            'quotes.update' => 'Редактирование КП',
            'quotes.delete' => 'Удаление КП',
            'quotes.send'   => 'Отправка КП клиенту',
        ],

        'invoices' => [
            'invoices.view'   => 'Просмотр инвойсов',
            'invoices.create' => 'Создание инвойсов',
            'invoices.update' => 'Редактирование инвойсов',
            'invoices.cancel' => 'Отмена инвойсов',
            'invoices.export' => 'Экспорт инвойсов (для бухгалтерии)',
        ],

        'sells' => [
            'sells.view' => 'Просмотр продаж (отгрузок)',
        ],

        'returns' => [
            'returns.view' => 'Просмотр возвратов оборудования',
        ],

        'catalog' => [
            'catalog.products.view'     => 'Просмотр каталога товаров',
            'catalog.products.create'   => 'Создание товаров',
            'catalog.products.update'   => 'Редактирование товаров',
            'catalog.products.delete'   => 'Удаление товаров',
            'catalog.prices.view-cost'  => 'Просмотр закупочной цены',
            'catalog.import'            => 'Импорт каталога (CSV/Excel)',
        ],

        'tickets' => [
            'tickets.view'   => 'Просмотр тикетов',
            'tickets.update' => 'Редактирование / переписка по тикетам',
            'tickets.assign' => 'Назначение ответственного по тикету',
            'tickets.close'  => 'Закрытие тикетов',
        ],

        'equipment_requests' => [
            'equipment-requests.view' => 'Просмотр заявок на оборудование',
        ],

        'reports' => [
            'reports.sales'     => 'Отчёты по продажам',
            'reports.managers'  => 'Отчёты по активности менеджеров',
            'reports.financial' => 'Финансовые отчёты',
        ],

        'settings' => [
            'settings.users'  => 'Управление пользователями',
            'settings.roles'  => 'Управление ролями и правами',
            'settings.system' => 'Системные настройки (справочники, шаблоны)',
        ],

    ],

    'roles' => [

        'super-admin' => [
            'guard'       => 'web',
            'permissions' => ['*'],
        ],

        'sales-director' => [
            'guard'       => 'web',
            'permissions' => [
                'leads.view', 'leads.create', 'leads.update', 'leads.delete', 'leads.assign',
                'customers.view', 'customers.create', 'customers.update', 'customers.delete', 'customers.export',
                'quotes.view', 'quotes.create', 'quotes.update', 'quotes.delete', 'quotes.send',
                'invoices.view', 'invoices.export',
                'sells.view',
                'returns.view',
                'tickets.view',
                'equipment-requests.view',
                'catalog.products.view',
                'reports.sales', 'reports.managers', 'reports.financial',
            ],
        ],

        'sales-manager' => [
            'guard'       => 'web',
            'permissions' => [
                'leads.view', 'leads.create', 'leads.update', 'leads.delete',
                'customers.view', 'customers.create', 'customers.update', 'customers.delete',
                'quotes.view', 'quotes.create', 'quotes.update', 'quotes.delete', 'quotes.send',
                'invoices.view',
                'sells.view',
                'returns.view',
                'catalog.products.view',
            ],
        ],

        'tech-support' => [
            'guard'       => 'web',
            'permissions' => [
                'tickets.view', 'tickets.update', 'tickets.assign', 'tickets.close',
                'equipment-requests.view',
                'customers.view',
            ],
        ],

        'catalog-manager' => [
            'guard'       => 'web',
            'permissions' => [
                'catalog.products.view', 'catalog.products.create', 'catalog.products.update',
                'catalog.products.delete', 'catalog.prices.view-cost', 'catalog.import',
            ],
        ],

        'accountant' => [
            'guard'       => 'web',
            'permissions' => [
                'invoices.view', 'invoices.create', 'invoices.update', 'invoices.cancel', 'invoices.export',
                'sells.view',
                'customers.view',
                'reports.financial',
            ],
        ],

        // Клиентские роли (Customer Portal) — доступ контролируется ownership-check
        // в Livewire-компонентах (customer_id === auth user's customer), не через module permissions.
        'client-admin' => [
            'guard'       => 'web',
            'permissions' => [],
        ],

        'client-user' => [
            'guard'       => 'web',
            'permissions' => [],
        ],

    ],

];
