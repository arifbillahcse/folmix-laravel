<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireCustomerLogin
{
    /**
     * Route names that stay reachable without an authenticated, approved customer.
     *
     * @var array
     */
    protected $exceptRouteNames = [
        'shop.customer.session.index',
        'shop.customer.session.create',
        'shop.customers.register.index',
        'shop.customers.register.store',
        'shop.customers.forgot_password.create',
        'shop.customers.forgot_password.store',
        'shop.customers.reset_password.create',
        'shop.customers.reset_password.store',
        'shop.customers.verify',
        'shop.customers.resend.verification_email',
    ];

    /**
     * Route name prefixes that stay reachable without an authenticated, approved customer.
     *
     * @var array
     */
    protected $exceptRouteNamePrefixes = [
        'shop.eu-withdrawal.guest.',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guard('customer')->check()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, $this->exceptRouteNames, true)) {
            return $next($request);
        }

        foreach ($this->exceptRouteNamePrefixes as $prefix) {
            if ($routeName && str_starts_with($routeName, $prefix)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => trans('shop::app.customers.login-form.please-login-first'),
            ], 401);
        }

        session()->flash('warning', trans('shop::app.customers.login-form.please-login-first'));

        return redirect()->route('shop.customer.session.index');
    }
}
