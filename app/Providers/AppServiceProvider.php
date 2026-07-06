<?php

namespace App\Providers;

use App\Helpers\Acl;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerBladeDirectives();
    }

    /**
     * Регистрирует кастомные Blade-директивы.
     *
     * @acl('permission')   — показать блок если есть permission
     * @endacl
     *
     * Работает в preview-mode (без Spatie) и после его установки.
     * См. app/Helpers/Acl.php и CLAUDE.md → "Контроль доступа: 3 уровня защиты".
     */
    private function registerBladeDirectives(): void
    {
        Blade::if('acl', function (?string $permission) {
            return Acl::can($permission);
        });

        Blade::if('isInternal', function () {
            return Acl::isInternal();
        });

        Blade::if('isClient', function () {
            return Acl::isClient();
        });
    }
}
