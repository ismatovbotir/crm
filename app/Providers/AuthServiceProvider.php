<?php

namespace App\Providers;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Lead\Lead;
use App\Models\Quote\Quote;
use App\Models\Sell\Sell;
use App\Models\Support\Ticket;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadPolicy;
use App\Policies\ProductPolicy;
use App\Policies\QuotePolicy;
use App\Policies\SellPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Lead::class     => LeadPolicy::class,
        Customer::class => CustomerPolicy::class,
        Quote::class    => QuotePolicy::class,
        Invoice::class  => InvoicePolicy::class,
        Ticket::class   => TicketPolicy::class,
        Sell::class     => SellPolicy::class,
        Category::class => CategoryPolicy::class,
        Product::class  => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
