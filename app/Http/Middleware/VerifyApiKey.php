<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'API Key is missing.'], 401);
        }

        // Support plain text token format "id|hash"
        $accessToken = Sanctum::$personalAccessTokenModel::findToken($token);

        if (! $accessToken || ! $this->isValidToken($accessToken)) {
            return response()->json(['message' => 'Invalid or expired API Key.'], 401);
        }

        // Validate CORS Origin if the token has strict domains configured
        /** @var ApiKey $accessToken */
        $domains = $accessToken->domains;
        $origin = $request->header('Origin') ?? $request->header('Referer');

        if (! empty($domains) && ! in_array('*', $domains)) {
            if (! $origin) {
                return response()->json(['message' => 'Origin header required for this API Key.'], 403);
            }

            // Simple origin check, removing trailing slashes
            $normalizedOrigin = rtrim($origin, '/');
            $allowed = false;

            foreach ($domains as $domain) {
                if (rtrim($domain, '/') === $normalizedOrigin) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                return response()->json(['message' => 'Origin not allowed for this API Key.'], 403);
            }
        }

        // Initialize Tenancy if the token belongs to a Tenant
        if ($accessToken->tokenable_type === Tenant::class && $accessToken->tokenable_id) {
            $tenant = Tenant::find($accessToken->tokenable_id);
            if ($tenant) {
                tenancy()->initialize($tenant);
            } else {
                return response()->json(['message' => 'Tenant associated with this API Key not found.'], 403);
            }
        }

        // Optional: Update last_used_at
        $accessToken->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }

    /**
     * Determine if the provided access token is valid.
     *
     * @param  PersonalAccessToken  $accessToken
     * @return bool
     */
    protected function isValidToken($accessToken)
    {
        if (! $accessToken->expires_at) {
            return true;
        }

        return $accessToken->expires_at->isFuture();
    }
}
