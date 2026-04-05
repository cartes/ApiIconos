<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DynamicCors
{
    /**
     * Consulta la BD y retorna todos los dominios registrados en allowed_domains
     * de las ApiKeys activas. Usa cache de 5 minutos para evitar queries en cada request.
     */
    private function getDbOrigins(): array
    {
        return Cache::remember('cors_allowed_origins_db', 300, function () {
            try {
                $rows = DB::table('personal_access_tokens')
                    ->whereNotNull('allowed_domains')
                    ->pluck('allowed_domains');

                return $rows
                    ->flatMap(fn ($json) => json_decode($json, true) ?: [])
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            } catch (\Throwable $e) {
                Log::warning('DynamicCors: no se pudieron cargar dominios desde la BD.', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');

        if ($origin) {
            $staticOrigins = config('cors.allowed_origins', []);
            $dbOrigins     = $this->getDbOrigins();
            $allOrigins    = array_unique(array_merge($staticOrigins, $dbOrigins));

            // Actualiza la config en memoria para que HandleCors la use en este ciclo
            config(['cors.allowed_origins' => $allOrigins]);
        }

        return $next($request);
    }
}
