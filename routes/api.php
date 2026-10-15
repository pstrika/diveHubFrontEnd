<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth.api:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth.api:sanctum')->post('/blog-articles', [\App\Http\Controllers\Api\BlogArticleController::class, 'store']);

// Native dive-computer uploader apps (iOS first) - see DiveUploadController's
// docblock for the batch/idempotency contract.
// 'logbook' = early-access gate (config/divehub.php logbook_allowed_emails);
// the token exchange below stays ungated - its one-time code can only come
// from the already-gated web AppLogin route.
Route::middleware(['auth.api:sanctum', 'logbook'])->post('/dives', [\App\Http\Controllers\Api\DiveUploadController::class, 'store']);

// Pull side of phone sync (SYNC-CONTRACT.md) - see DiveSyncController.
// /dives/changes MUST be registered before /dives/{uuid}, or Laravel
// would match the literal "changes" as a {uuid} param instead.
Route::middleware(['auth.api:sanctum', 'logbook'])->get('/dives/changes', [\App\Http\Controllers\Api\DiveSyncController::class, 'changes']);
Route::middleware(['auth.api:sanctum', 'logbook'])->get('/dives/{uuid}', [\App\Http\Controllers\Api\DiveSyncController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F-]{36}');

// App sign-in token exchange (docs/APP-AUTH.md) - see AppAuthController.
// No auth on the exchange itself - the one-time code from AppLogin::login
// IS the credential; throttled tighter than the route-wide 'api' limiter
// since it's reachable without a token at all.
Route::middleware('throttle:10,1')->post('/app/token', [\App\Http\Controllers\AppAuthController::class, 'exchangeToken']);
Route::middleware('auth.api:sanctum')->delete('/app/token', [\App\Http\Controllers\AppAuthController::class, 'revokeToken']);
