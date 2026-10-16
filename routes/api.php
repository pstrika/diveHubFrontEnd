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
// 'throttle:logbook-sync' (600/min per user, RouteServiceProvider) replaces
// the api group's generic 60/min on the dive routes - a fresh install pulls
// every dive it doesn't hold, and 66 dives already tripped the default.
Route::withoutMiddleware('throttle:api')->middleware(['auth.api:sanctum', 'logbook', 'throttle:logbook-sync'])->post('/dives', [\App\Http\Controllers\Api\DiveUploadController::class, 'store']);
// Batch fetch for that first pull: GET /dives?uuids=a,b,c (max 20) - same
// per-dive object as /dives/{uuid}, unknown uuids simply absent.
Route::withoutMiddleware('throttle:api')->middleware(['auth.api:sanctum', 'logbook', 'throttle:logbook-sync'])->get('/dives', [\App\Http\Controllers\Api\DiveSyncController::class, 'index']);

// Pull side of phone sync (SYNC-CONTRACT.md) - see DiveSyncController.
// /dives/changes MUST be registered before /dives/{uuid}, or Laravel
// would match the literal "changes" as a {uuid} param instead.
Route::withoutMiddleware('throttle:api')->middleware(['auth.api:sanctum', 'logbook', 'throttle:logbook-sync'])->get('/dives/changes', [\App\Http\Controllers\Api\DiveSyncController::class, 'changes']);
// Gap detector for the feed: every live dive's {id, uuid, updated_at}.
Route::withoutMiddleware('throttle:api')->middleware(['auth.api:sanctum', 'logbook', 'throttle:logbook-sync'])->get('/dives/uuids', [\App\Http\Controllers\Api\DiveSyncController::class, 'uuids']);
Route::withoutMiddleware('throttle:api')->middleware(['auth.api:sanctum', 'logbook', 'throttle:logbook-sync'])->get('/dives/{uuid}', [\App\Http\Controllers\Api\DiveSyncController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F-]{36}');

// The web logbook for the app (LogbookApiController, 2026-10-15): option
// lists, site/operator catalogs, buddy search, and the one PATCH that logs
// anything against a dive. Same gate + limiter as the dive routes.
Route::withoutMiddleware('throttle:api')->middleware(['auth.api:sanctum', 'logbook', 'throttle:logbook-sync'])->group(function () {
    Route::get('/logbook-options', [\App\Http\Controllers\Api\LogbookApiController::class, 'options']);
    Route::get('/sites', [\App\Http\Controllers\Api\LogbookApiController::class, 'sites']);
    Route::get('/operators', [\App\Http\Controllers\Api\LogbookApiController::class, 'operators']);
    Route::get('/users/search', [\App\Http\Controllers\Api\LogbookApiController::class, 'searchUsers']);
    Route::patch('/dives/{uuid}/conditions', [\App\Http\Controllers\Api\LogbookApiController::class, 'updateConditions'])
        ->where('uuid', '[0-9a-fA-F-]{36}');
    // Web-style delete from the app (Pablo, 2026-10-16): soft delete +
    // change feed, so every other phone drops it too.
    Route::delete('/dives/{uuid}', [\App\Http\Controllers\Api\LogbookApiController::class, 'destroy'])
        ->where('uuid', '[0-9a-fA-F-]{36}');
    // Same dive, another computer (web 12.14.0): the diver's answer.
    Route::post('/dives/{uuid}/combine', [\App\Http\Controllers\Api\LogbookApiController::class, 'combine'])
        ->where('uuid', '[0-9a-fA-F-]{36}');
    Route::post('/dives/{uuid}/not-same', [\App\Http\Controllers\Api\LogbookApiController::class, 'notSame'])
        ->where('uuid', '[0-9a-fA-F-]{36}');
    // The diver's own sites (UserSiteApiController).
    Route::get('/user-sites', [\App\Http\Controllers\Api\UserSiteApiController::class, 'index']);
    Route::post('/user-sites', [\App\Http\Controllers\Api\UserSiteApiController::class, 'store']);
    Route::patch('/user-sites/{id}', [\App\Http\Controllers\Api\UserSiteApiController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/user-sites/{id}', [\App\Http\Controllers\Api\UserSiteApiController::class, 'destroy'])->where('id', '[0-9]+');
});

// App sign-in token exchange (docs/APP-AUTH.md) - see AppAuthController.
// No auth on the exchange itself - the one-time code from AppLogin::login
// IS the credential; throttled tighter than the route-wide 'api' limiter
// since it's reachable without a token at all.
Route::middleware('throttle:10,1')->post('/app/token', [\App\Http\Controllers\AppAuthController::class, 'exchangeToken']);
Route::middleware('auth.api:sanctum')->delete('/app/token', [\App\Http\Controllers\AppAuthController::class, 'revokeToken']);
