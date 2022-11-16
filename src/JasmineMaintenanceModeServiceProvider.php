<?php

namespace Jasmine\MaintenanceMode;

use Illuminate\Support\ServiceProvider;
use Jasmine\MaintenanceMode\Http\Middleware\MaintenanceMode;
use Jasmine\MaintenanceMode\Pages\MaintenanceMode as MaintenanceModePage;

class JasmineMaintenanceModeServiceProvider extends ServiceProvider
{
    public function register()
    {
        \Jasmine::registerPage(MaintenanceModePage::class, false);
        
        app('jasmine')->registerSideBarSubMenuItem('tools', 'maintenance-mode', function () {
            return [
                'title'    => 'Maintenance Mode',
                'href'     => route('jasmine.page.edit', 'maintenance-mode'),
                'is-route' => ['r' => 'jasmine.page.edit', 'p' => ['jasminePage' => 'maintenance-mode']],
            ];
        });
    }
    
    public function boot()
    {
        app('router')->aliasMiddleware('jasmineMaintenanceMode', MaintenanceMode::class);
    }
}
