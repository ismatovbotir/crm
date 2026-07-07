<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\SellController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\EquipmentRequestController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Admin\Settings\UserController;
use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Admin\PdfController as AdminPdfController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\QuoteController as PortalQuoteController;
use App\Http\Controllers\Portal\InvoiceController as PortalInvoiceController;
use App\Http\Controllers\Portal\TicketController as PortalTicketController;
use App\Http\Controllers\Portal\CatalogController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\PdfController as PortalPdfController;

Route::get('/', fn () => redirect('/admin'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

// First-run setup
Route::middleware('auth')->get('/admin/setup', [SetupController::class, 'index'])->name('admin.setup');

// Admin CRM
Route::middleware(['auth', 'role:super-admin|sales-director|sales-manager|tech-support|catalog-manager|accountant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Leads
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');

        // Customers
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        // Catalog
        Route::get('/catalog/categories', [CategoryController::class, 'index'])->name('catalog.categories.index');
        Route::get('/catalog/products', [ProductController::class, 'index'])->name('catalog.products.index');
        Route::get('/catalog/products/{product}', [ProductController::class, 'show'])->name('catalog.products.show');
        Route::get('/catalog/groups', \App\Livewire\Admin\Catalog\Groups\Index::class)->name('catalog.groups.index');
        Route::get('/catalog/recommendations', \App\Livewire\Admin\Catalog\Recommendations\Index::class)->name('catalog.recommendations.index');

        // Quotes
        Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        Route::get('/quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

        // Sells
        Route::get('/sells', [SellController::class, 'index'])->name('sells.index');
        Route::get('/sells/{sell}', [SellController::class, 'show'])->name('sells.show');

        // Tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

        // Equipment Requests
        Route::get('/equipment-requests', [EquipmentRequestController::class, 'index'])->name('equipment-requests.index');
        Route::get('/equipment-requests/{equipmentRequest}', [EquipmentRequestController::class, 'show'])->name('equipment-requests.show');

        // Returns
        Route::get('/returns', \App\Livewire\Admin\Returns\Index::class)->name('returns.index');
        Route::get('/returns/create', \App\Livewire\Admin\Returns\CreateForm::class)->name('returns.create');
        Route::get('/returns/{productReturn}', \App\Livewire\Admin\Returns\Show::class)->name('returns.show');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // PDF downloads
        Route::get('/quotes/{quote}/pdf', [AdminPdfController::class, 'quote'])->name('quotes.pdf');
        Route::get('/invoices/{invoice}/pdf', [AdminPdfController::class, 'invoice'])->name('invoices.pdf');
        Route::get('/sells/{sell}/pdf', [AdminPdfController::class, 'sell'])->name('sells.pdf');

        // CSV Exports
        Route::get('/export/customers', [ExportController::class, 'customers'])->name('export.customers');
        Route::get('/export/invoices', [ExportController::class, 'invoices'])->name('export.invoices');

        // Catalog Import
        Route::get('/import/catalog/template', [ImportController::class, 'catalogTemplate'])->name('import.catalog.template');
        Route::post('/import/catalog', [ImportController::class, 'catalogImport'])->name('import.catalog');

        // Settings (super-admin only)
        Route::middleware('role:super-admin')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users');
            Route::get('/roles', [RoleController::class, 'index'])->name('roles');
        });
    });

// Customer Portal
Route::middleware(['auth', 'role:client-admin|client-user'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('/quotes', [PortalQuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/{quote}', [PortalQuoteController::class, 'show'])->name('quotes.show');
        Route::get('/invoices', [PortalInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [PortalInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/tickets', [PortalTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', \App\Livewire\Portal\Tickets\CreateForm::class)->name('tickets.create');
        Route::get('/tickets/{ticket}', [PortalTicketController::class, 'show'])->name('tickets.show');
        Route::get('/quotes/{quote}/pdf', [PortalPdfController::class, 'quote'])->name('quotes.pdf');
        Route::get('/invoices/{invoice}/pdf', [PortalPdfController::class, 'invoice'])->name('invoices.pdf');
        Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/equipment', \App\Livewire\Portal\Equipment\Index::class)->name('equipment.index');

        // Equipment Requests (Module 6 — client self-service requests, separate from serial-tracking above)
        Route::get('/equipment-requests', \App\Livewire\Portal\EquipmentRequests\Index::class)->name('equipment-requests.index');
        Route::get('/equipment-requests/create', \App\Livewire\Portal\EquipmentRequests\CreateForm::class)->name('equipment-requests.create');
        Route::get('/equipment-requests/{equipmentRequest}', \App\Livewire\Portal\EquipmentRequests\Show::class)->name('equipment-requests.show');
    });
