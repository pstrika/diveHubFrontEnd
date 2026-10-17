<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            // Redirect to the login page
            return redirect()->route('login');
        }

        return parent::render($request, $exception);
    }

    /**
     * API routes always answer a missing/expired token with a JSON 401,
     * never a redirect to the sign-in page - even an image request that
     * doesn't ask for JSON (iOS session, 2026-10-17: GET /api/photos/{uuid}/web
     * without a token got a 302).
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        if ($request->is('api/*')) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }
        return parent::unauthenticated($request, $exception);
    }
}
