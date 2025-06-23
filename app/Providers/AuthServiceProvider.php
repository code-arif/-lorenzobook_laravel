<?php

namespace App\Providers;

use App\Models\Group;
use App\Policies\GroupPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */

    


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void {
        
        $this->registerPolicies();

        Gate::define('manage-members', [GroupPolicy::class, 'manageMembers']);
        Gate::define('view-any', [GroupPolicy::class, 'viewAny']);
        Gate::define('view', [GroupPolicy::class, 'view']);
        Gate::define('create', [GroupPolicy::class, 'create']);
        Gate::define('update', [GroupPolicy::class, 'update']);
        Gate::define('delete', [GroupPolicy::class, 'delete']);
    }
}
