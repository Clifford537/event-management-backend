<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('org_slug');
        Log::info('Tenant middleware', ['org_slug' => $slug]);

        $organization = Organization::where('slug', $slug)->first();

        if (!$organization) {
            Log::warning('Organization not found', ['slug' => $slug]);
            return response()->json(['error' => 'Organization not found'], 404);
        }

        $request->attributes->add(['organization' => $organization]);

        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->organization_id) {
                Log::warning('User not associated with any organization', ['user_id' => $user->id]);
                return response()->json(['error' => 'User not associated with any organization'], 403);
            }
            if ($user->organization_id !== $organization->id) {
                Log::warning('User unauthorized for organization', [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                ]);
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        return $next($request);
    }
}