<?php

namespace Jasmine\MaintenanceMode\Http\Middleware;


use Jasmine\MaintenanceMode\Pages\MaintenanceMode as MaintenanceModePage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MaintenanceMode
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed|void
     */
    public function handle($request, \Closure $next)
    {
        $conf = MaintenanceModePage::jLoad()?->content ?? [];

        // Check if maintenance mode enabled
        if ($conf['status'] != '1') return $next($request);
        // If authenticated continue
        if (\Auth::guard(config('jasmine.auth.guard'))->check()) return $next($request);

        // If has valid token
        $token = $request->get('mm-token', $request->cookie('jasmine_mm'));
        if ($token && \Arr::first($conf['bypass_tokens'] ?? [],
                fn($bt) => str_contains($bt['url'], '?mm-token=' . $token))) {
            \Cookie::queue('jasmine_mm', $token, 60 * 24 * 30 * 12);
            return $next($request);
        }

        throw new HttpException(503, 'Service Unavailable');
    }
}
