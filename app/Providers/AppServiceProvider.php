<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Opportunity;
use App\Policies\CompanyPolicy;
use App\Policies\ContactPolicy;
use App\Policies\CrmTaskPolicy;
use App\Policies\OpportunityPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(CrmTask::class, CrmTaskPolicy::class);
        Gate::policy(Opportunity::class, OpportunityPolicy::class);
    }
}
