<?php

namespace Jasmine\MaintenanceMode\Http\Middleware;


use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try {
            $conf = MaintenanceModePage::jLoad()?->content ?? [];
        } catch (ModelNotFoundException $e) {
            return $next($request);
        }


        // Check if maintenance mode enabled
        if (!isset($conf['status']) || $conf['status'] != '1') return $next($request);
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
