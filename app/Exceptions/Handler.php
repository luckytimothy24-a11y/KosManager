<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Sila muat semula halaman dan cuba lagi.',
                ], 419);
            }

            return redirect($request->fullUrl())
                ->with('error', 'Sesi Anda telah berakhir. Sila cuba semula.');
        });

        // 404 — Per kontrak JSON: tidak mengekspos nama model / SQL.
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'Resource not found.'], 404);
        });

        // 403 — kontrak JSON: pesan seragam.
        $this->renderable(function (HttpException $e, $request) {
            if (! $request->expectsJson() || $e->getStatusCode() !== 403) {
                return null;
            }

            return response()->json(['message' => 'This action is unauthorized.'], 403);
        });

        // 500 — kontrak JSON: tangani error server generik tanpa membocorkan
        // stack trace, SQL, kredensial, atau path filesystem. Exception HTTP
        // yang sudah dikenal (auth/authorize/validation/throttle) diteruskan
        // ke penanganan default.
        $this->renderable(function (Throwable $e, $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof HttpException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof ValidationException) {
                return null;
            }

            return response()->json(['message' => 'Server Error.'], 500);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
