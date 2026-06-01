<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    /**
     * Guard API routes with a static key (header X-Api-Key or Authorization: Bearer).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configured = trim((string) config('services.codex.api_key'));

        if ($configured === '') {
            return response()->json([
                'message' => 'API indisponivel: CODEX_API_KEY nao foi configurada no servidor.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $provided = $this->extractKey($request);

        if ($provided === null || ! hash_equals($configured, $provided)) {
            return response()->json([
                'message' => 'Chave de API ausente ou invalida.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    private function extractKey(Request $request): ?string
    {
        $header = trim((string) $request->header('X-Api-Key', ''));

        if ($header !== '') {
            return $header;
        }

        $bearer = $request->bearerToken();

        return $bearer !== null && trim($bearer) !== '' ? trim($bearer) : null;
    }
}
