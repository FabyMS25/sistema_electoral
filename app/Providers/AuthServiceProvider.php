<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('administrador')) {
                return true;
            }
        });

        // Gate dinámico para permisos
        Gate::define('has-permission', function ($user, $permission, $scope = null, $scopeId = null) {
            return $user->hasPermissionTo($permission, $scope, $scopeId);
        });


        // Registrar cada permiso como un Gate
        // try {
        //     if (app()->runningInConsole()) {
        //         return;
        //     }
            
        //     $permissions = Permission::all();
        //     foreach ($permissions as $permission) {
        //         Gate::define($permission->name, function (User $user) use ($permission) {
        //             return $user->hasPermission($permission->name);
        //         });
        //     }
        // } catch (\Exception $e) {
        //     // La tabla de permisos puede no existir durante la migración
        //     // Ignoramos el error silenciosamente
        // }
    }
}