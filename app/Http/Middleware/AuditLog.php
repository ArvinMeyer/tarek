<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog as AuditLogModel;

class AuditLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log after the request is processed
        if (auth()->check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logRequest($request);
        }

        return $response;
    }

    protected function logRequest(Request $request)
    {
        $path = $request->path();
        $module = $this->detectModule($path);
        $action = $this->detectAction($request->method(), $path);

        if ($module && $action) {
            AuditLogModel::log(
                $action,
                $module,
                $request->route('id') ?? $request->input('id'),
                null,
                $request->except(['password', 'password_confirmation', '_token'])
            );
        }
    }

    protected function detectModule(string $path): ?string
    {
        if (str_contains($path, 'quotation')) return 'quotation';
        if (str_contains($path, 'invoice')) return 'invoice';
        if (str_contains($path, 'purchase-order')) return 'purchase_order';
        if (str_contains($path, 'customer')) return 'customer';
        if (str_contains($path, 'email')) return 'email';
        if (str_contains($path, 'setting')) return 'settings';
        if (str_contains($path, 'user')) return 'user';
        
        return null;
    }

    protected function detectAction(string $method, string $path): ?string
    {
        return match($method) {
            'POST' => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default => null,
        };
    }
}

