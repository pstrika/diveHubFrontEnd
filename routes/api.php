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
// 'logbook' = registered members only, no guest accounts (LogbookGate);
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

    // Photos, marine life, equipment (2026-10-17) - LogbookExtrasApiController,
    // contract in DiversHub-Uploader docs/PHOTOS-SPECIES-EQUIPMENT.md.
    $x = \App\Http\Controllers\Api\LogbookExtrasApiController::class;
    $u = '[0-9a-fA-F-]{36}';
    Route::get('/me/features', [$x, 'features']);
    Route::post('/me/share-sightings', [$x, 'shareSightings']);
    Route::get('/dives/{dive}/photos', [$x, 'photos'])->where('dive', $u);
    Route::post('/dives/{dive}/photos', [$x, 'upload'])->where('dive', $u);
    Route::patch('/photos/{photo}', [$x, 'updatePhoto'])->where('photo', $u);
    Route::delete('/photos/{photo}', [$x, 'deletePhoto'])->where('photo', $u);
    Route::post('/photos/{photo}/share', [$x, 'sharePhoto'])->where('photo', $u);
    Route::put('/photos/{photo}/species', [$x, 'setPhotoSpecies'])->where('photo', $u);
    Route::get('/photos/{photo}/{size}', [$x, 'photoFile'])->where(['photo' => $u, 'size' => 'web|thumb']);
    Route::get('/species', [$x, 'species']);
    Route::post('/species', [$x, 'createSpecies']);
    Route::patch('/species/{uuid}', [$x, 'updateSpecies'])->where('uuid', $u);
    Route::delete('/species/{uuid}', [$x, 'deleteSpecies'])->where('uuid', $u);
    Route::put('/dives/{dive}/species', [$x, 'setDiveSpecies'])->where('dive', $u);
    Route::get('/equipment', [$x, 'equipment']);
    Route::post('/equipment/items', [$x, 'createItem']);
    Route::match(['post', 'patch'], '/equipment/items/{uuid}', [$x, 'updateItem'])->where('uuid', $u);
    Route::delete('/equipment/items/{uuid}', [$x, 'deleteItem'])->where('uuid', $u);
    Route::get('/equipment/items/{uuid}/photo', [$x, 'itemPhotoFile'])->where('uuid', $u);
    Route::post('/equipment/configs', [$x, 'saveConfig']);
    Route::patch('/equipment/configs/{uuid}', [$x, 'saveConfig'])->where('uuid', $u);
    Route::delete('/equipment/configs/{uuid}', [$x, 'deleteConfig'])->where('uuid', $u);
    Route::put('/dives/{dive}/equipment', [$x, 'setDiveEquipment'])->where('dive', $u);
});

// App sign-in token exchange (docs/APP-AUTH.md) - see AppAuthController.
// No auth on the exchange itself - the one-time code from AppLogin::login
// IS the credential; throttled tighter than the route-wide 'api' limiter
// since it's reachable without a token at all.
Route::middleware('throttle:10,1')->post('/app/token', [\App\Http\Controllers\AppAuthController::class, 'exchangeToken']);
Route::middleware('auth.api:sanctum')->delete('/app/token', [\App\Http\Controllers\AppAuthController::class, 'revokeToken']);
