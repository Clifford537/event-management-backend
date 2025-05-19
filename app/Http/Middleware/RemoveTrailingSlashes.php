<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class RemoveTrailingSlashes
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->getPathInfo();
        if (preg_match('/.+\/$/', $path)) {
            return redirect(rtrim($path, '/'), 301);
        }
        return $next($request);
    }
}