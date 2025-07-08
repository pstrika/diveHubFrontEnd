<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	return redirect('Landing');
	//return view('pages.Landing', ['SEO' => true]);
    //return redirect('sign-in');
	//return redirect('Trips');
})->middleware('guest');


/* Privacy Policy */
Route::get('PrivacyPolicy', function () {
    return view('pages.PrivacyPolicy');
})->middleware('guest')->name('PrivacyPolicy');

Route::get('TermsOfUse', function () {
    return view('pages.TermsOfUse');
})->middleware('guest')->name('TermsOfUse');

Route::get('home', function () {
    return view('pages.home');
})->name('home');

Route::get('Landing', function () {
    $SEO = [
        "title" => "Florida scuba diving sites, calendars and operators",
        "desc" => "All you need to know for diving in Florida: dive operators, dive sites and wreckwiki, calendars, dive planning and more",
        "keywords" => "scuba diving florida, scuba, dive operators miami, dive operators fort lauderdale, diving florida keys, dive sites florida",
    ];

    return view('pages.Landing', compact('SEO'));
})->name('Landing');

/* Google SSO Routes */
Route::get('login/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [GoogleController::class, 'handleGoogleCallback']);

/* Routes for diveHub */
Route::get('Trips/{date}', 'App\Http\Controllers\TripsController@show')->middleware('guest')->name('Trips');
Route::get('Trips/', 'App\Http\Controllers\TripsController@show')->middleware('guest')->name('Trips');
//Route::get('Trips/', 'App\Http\Controllers\TripsController@show')->middleware('guest')->name('Trips');

Route::middleware(['auth', 'admin'])->group(function () {
    // Define admin-only routes here
    // Example:
    // Route::get('/admin/dashboard', 'AdminController@dashboard');
	Route::get('PlatformHealth/', 'App\Http\Controllers\OperatorController@showHealth')->middleware('auth')->name('PlatformHealth');

	Route::get('users-management', [UserManagementController::class, 'index'])->middleware('auth')->name('users');
	Route::get('add-new-user', [UserManagementController::class, 'create'])->middleware('auth')->name('add.user');
	Route::post('add-new-user', [UserManagementController::class, 'store'])->middleware('auth');
	Route::get('edit-user/{id}',[UserManagementController::class, 'edit'])->middleware('auth')->name('edit.user');
	Route::post('edit-user/{id}',[UserManagementController::class, 'update'])->middleware('auth');
	Route::post('users-management/{id}',[UserManagementController::class, 'destroy'])->middleware('auth')->name('delete.user');

	Route::get('DeleteDiveSite/{id}', 'App\Http\Controllers\SiteController@delete')->middleware('auth')->name('DeleteDiveSite');
	Route::get('DeleteDiveSite', 'App\Http\Controllers\SiteController@delete')->middleware('auth')->name('DeleteDiveSite');

	Route::get('DeletePic/{id}', 'App\Http\Controllers\SiteController@deletePic')->middleware('auth')->name('DeletePic');
	Route::get('DeletePic', 'App\Http\Controllers\SiteController@deletePic')->middleware('auth')->name('DeletePic');

	
});

Route::get('DecoPlanner/{id}', 'App\Http\Controllers\NDLController@show')->middleware('auth')->name('DecoPlanner');
Route::get('DecoPlanner', 'App\Http\Controllers\NDLController@show')->middleware('auth')->name('DecoPlanner');
Route::get('DecoPlannerImperial/{id}', 'App\Http\Controllers\NDLController@showImperial')->middleware('auth')->name('DecoPlannerImperial');
Route::get('DecoPlannerImperial', 'App\Http\Controllers\NDLController@showImperial')->middleware('auth')->name('DecoPlannerImperial');
Route::get('DecoPlannerMetric/{id}', 'App\Http\Controllers\NDLController@showMetric')->middleware('auth')->name('DecoPlannerMetric');
Route::get('DecoPlannerMetric', 'App\Http\Controllers\NDLController@showMetric')->middleware('auth')->name('DecoPlannerMetric');

Route::get('Weather/{location}', 'App\Http\Controllers\WeatherController@show')->middleware('auth')->name('Weather');
Route::get('Weather/', 'App\Http\Controllers\WeatherController@show')->middleware('auth')->name('Weather');
Route::get('WeatherAR/{location}', 'App\Http\Controllers\WeatherController@showAR')->middleware('auth')->name('WeatherAR');
Route::get('WeatherAR/', 'App\Http\Controllers\WeatherController@showAR')->middleware('auth')->name('WeatherAR');
Route::get('WeatherARImperial/{location}', 'App\Http\Controllers\WeatherController@showARImperial')->middleware('auth')->name('WeatherARImperial');
Route::get('WeatherARImperial/', 'App\Http\Controllers\WeatherController@showARImperial')->middleware('auth')->name('WeatherARImperial');
Route::get('WeatherARMetric/{location}', 'App\Http\Controllers\WeatherController@showARMetric')->middleware('auth')->name('WeatherARMetric');
Route::get('WeatherARMetric/', 'App\Http\Controllers\WeatherController@showARMetric')->middleware('auth')->name('WeatherARMetric');

Route::get('CalendarT/{tripType}/{date}', 'App\Http\Controllers\CalendarTController@show')->middleware('auth')->name('CalendarT');
Route::get('CalendarT/{tripType}', 'App\Http\Controllers\CalendarTController@show')->middleware('auth')->name('CalendarT');
Route::get('CalendarT/', 'App\Http\Controllers\CalendarTController@show')->middleware('auth')->name('CalendarT');
Route::get('CalendarShark/{date}', 'App\Http\Controllers\CalendarTController@showShark')->middleware('auth')->name('CalendarShark');
Route::get('CalendarShark/', 'App\Http\Controllers\CalendarTController@showShark')->middleware('auth')->name('CalendarShark');
Route::get('CalendarLobster/{date}', 'App\Http\Controllers\CalendarTController@showLobster')->middleware('auth')->name('CalendarLobster');
Route::get('CalendarLobster/', 'App\Http\Controllers\CalendarTController@showLobster')->middleware('auth')->name('CalendarLobster');
Route::get('CalendarWreck/{date}', 'App\Http\Controllers\CalendarTController@showWreck')->middleware('auth')->name('CalendarWreck');
Route::get('CalendarWreck/', 'App\Http\Controllers\CalendarTController@showWreck')->middleware('auth')->name('CalendarWreck');

/* Special routes for Hydrotherapy integration */
Route::get('CalendarHydrotherapy/{date}', 'App\Http\Controllers\CalendarTController@showHydrotherapy')->middleware('guest')->name('CalendarHydrotherapy');
Route::get('CalendarHydrotherapy/', 'App\Http\Controllers\CalendarTController@showHydrotherapy')->middleware('guest')->name('CalendarHydrotherapy');

Route::get('MyCalendar/{date}', 'App\Http\Controllers\EventController@show')->middleware('auth')->name('MyCalendar');
Route::get('MyCalendar/', 'App\Http\Controllers\EventController@show')->middleware('auth')->name('MyCalendar');

Route::get('TripDetails/{tripId}', 'App\Http\Controllers\TripDetailsController@show')->middleware('guest')->name('TripDetails');
Route::get('AddEventToCalendar/{tripId}', 'App\Http\Controllers\EventController@addEventToCalendar')->middleware('auth')->name('AddEventToCalendar');
Route::get('SetEventBook/{tripId}', 'App\Http\Controllers\EventController@setEventBook')->middleware('auth')->name('SetEventBook');
Route::get('RemoveFromCalendar/{tripId}', 'App\Http\Controllers\EventController@removeFromCalendar')->middleware('auth')->name('RemoveFromCalendar');

Route::get('Operators/', 'App\Http\Controllers\OperatorController@show')->middleware('guest')->name('Operators');
Route::get('Waivers', 'App\Http\Controllers\OperatorController@getWaivers')->middleware('guest')->name('Waivers');
Route::get('ToggleFav/{id}', 'App\Http\Controllers\OperatorController@toggleFav')->middleware('auth')->name('ToggleFav');

Route::get('OperatorDetails/{id}', 'App\Http\Controllers\OperatorController@show')->middleware('guest')->name('OperatorDetails');


Route::get('BeachDiving', 'App\Http\Controllers\SiteController@showBeach')->middleware('guest')->name('BeachDiving');


Route::get('Messages', 'App\Http\Controllers\MessageController@show')->middleware('auth')->name('Messages');
Route::post('mark-as-read', 'App\Http\Controllers\MessageController@markAsRead')->middleware('auth')->name('mark-as-read');;
Route::post('delete-message', 'App\Http\Controllers\MessageController@delete')->middleware('auth')->name('delete-message');;

Route::get('new-site/', 'App\Http\Controllers\SiteController@create')->middleware('auth')->name('new-site');
Route::post('new-site', 'App\Http\Controllers\SiteController@store')->middleware('auth')->name('new-site-store');

Route::get('new-site-uploadPics', 'App\Http\Controllers\SiteController@create')->middleware('auth')->name('new-site-uploadPics');
Route::post('new-site-uploadPics', 'App\Http\Controllers\SiteController@updateMedia')->middleware('auth')->name('new-site-uploadPics');

Route::post('upload', 'App\Http\Controllers\SiteController@upload')->middleware('auth')->name('upload');

Route::post('new-site-updatePicsDesc', 'App\Http\Controllers\SiteController@updateDesc')->middleware('auth')->name('new-site-updatePicsDesc');

Route::post('update-site-pics', 'App\Http\Controllers\SiteController@updatePics')->middleware('auth')->name('update-site-pics');

Route::get('edit-site/{id}', 'App\Http\Controllers\SiteController@showAdmin')->middleware('auth')->name('edit-site');
Route::post('update-site', 'App\Http\Controllers\SiteController@update')->middleware('auth')->name('update-site');

Route::get('edit-site-pics/{id}', 'App\Http\Controllers\SiteController@showAdminPics')->middleware('auth')->name('edit-site-pics');


Route::get('SiteDetails/{id}', 'App\Http\Controllers\SiteController@show')->middleware('guest')->name('SiteDetails');
Route::get('SiteDetails', 'App\Http\Controllers\SiteController@show')->middleware('guest')->name('SiteDetails');
Route::post('RateSite', 'App\Http\Controllers\SiteRatingController@new')->middleware('auth')->name('RateSite');
Route::post('UpdateVisited', 'App\Http\Controllers\SiteController@updateVisited')->middleware('auth')->name('UpdateVisited');
Route::get('UpdateWished/{siteId}', 'App\Http\Controllers\SiteController@updateWished')->middleware('auth')->name('UpdateWished');
Route::post('AddSiteReview/{siteId}', 'App\Http\Controllers\SiteController@addReview')->middleware('auth')->name('AddSiteReview');

Route::get('DiveSites', 'App\Http\Controllers\SiteController@showTopRated')->middleware('guest')->name('DiveSites');
Route::get('DiveSitesSearch', 'App\Http\Controllers\SiteController@searchSites')->middleware('guest')->name('DiveSitesSearch');
Route::post('DiveSitesSearch', 'App\Http\Controllers\SiteController@searchSites')->middleware('guest')->name('DiveSitesSearch');
Route::get('DiveSitesMap', 'App\Http\Controllers\SiteController@showAll')->middleware('guest')->name('DiveSitesMap');
Route::get('DiveSitesAll', 'App\Http\Controllers\SiteController@showAllSearch')->middleware('guest')->name('DiveSitesAll');
Route::get('DiveSitesAdmin', 'App\Http\Controllers\SiteController@showAllAdmin')->middleware('auth')->name('DiveSitesAdmin');
Route::post('Calculate-ndl', 'App\Http\Controllers\NDLController@calculateNDL')->middleware('guest')->name('Calculate-ndl');
Route::post('calculateDecoProfile', 'App\Http\Controllers\NDLController@calculateDecoProfile')->middleware('guest')->name('calculateDecoProfile');

Route::get('WreckSites', 'App\Http\Controllers\SiteController@showWrecks')->middleware('guest')->name('WreckSites');



Route::get('overview', 'App\Http\Controllers\UserController@getProfile')->middleware('auth')->name('overview');
Route::post('overview', 'App\Http\Controllers\UserController@updateProfile')->middleware('auth')->name('overview');
Route::post('upload-profile-pic', 'App\Http\Controllers\UserController@updateProfilePic')->middleware('auth')->name('upload-profile-pic');

//Route::get('dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('MyDashboard', 'App\Http\Controllers\MyDashboardController@showDashboard')->middleware('auth')->name('MyDashboard');

Route::get('AboutUs', function () {
	return view('pages.Contact');
})->name('AboutUs');

Route::get('sign-up', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('sign-up', [RegisterController::class, 'store'])->middleware('guest');

Route::get('sign-in', [SessionsController::class, 'create'])->middleware('guest')->name('login');
Route::post('sign-in', [SessionsController::class, 'store'])->middleware('guest');

Route::post('sign-out', [SessionsController::class, 'destroy'])->middleware('guest')->name('logout');

Route::post('verify', [SessionsController::class, 'show'])->middleware('guest');
Route::post('reset-password', [SessionsController::class, 'update'])->middleware('guest')->name('password.update');

Route::get('verify', function () {
	return view('sessions.password.verify');
})->middleware('guest')->name('verify');

Route::get('reset-password/{token}', function ($token) {
	return view('sessions.password.reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::get('user-profile', [UserController::class, 'index'])->middleware('auth')->name('user-profile');
Route::post('user-profile', [UserController::class, 'update'])->middleware('auth')->name('user.update');
Route::post('user-profile/password', [UserController::class, 'passwordUpdate'])->middleware('auth')->name('password.change');
Route::get('MyVisitedSites', 'App\Http\Controllers\SiteController@getMyVisitedSites')->middleware('auth')->name('MyVisitedSites');
Route::post('UpdateAllVisited', 'App\Http\Controllers\SiteController@updateAllVisitedSites')->middleware('auth')->name('UpdateAllVisited');


Route::get('roles', [RolesController::class, 'index'])->middleware('auth')->name('roles');
Route::post('roles/{id}', [RolesController::class, 'destroy'])->middleware('auth')->name('delete.role');
Route::get('new-role', [RolesController::class, 'create'])->middleware('auth')->name('add.role');
Route::post('new-role', [RolesController::class, 'store'])->middleware('auth');
Route::post('edit-role/{id}', [RolesController::class, 'update'])->middleware('auth');
Route::get('edit-role/{id}', [RolesController::class, 'edit'])->middleware('auth')->name('edit.role');


Route::get('category', [CategoryController::class, 'index'])->middleware('auth')->name('category');
Route::post('category/{id}', [CategoryController::class, 'destroy'])->middleware('auth')->name('delete.category');
Route::get('new-category', [CategoryController::class, 'create'])->middleware('auth')->name('add.category');
Route::post('new-category', [CategoryController::class, 'store'])->middleware('auth');
Route::post('edit-category/{id}', [CategoryController::class, 'update'])->middleware('auth');
Route::get('edit-category/{id}', [CategoryController::class, 'edit'])->middleware('auth')->name('edit.category');


Route::get('tag',[TagController::class, 'index'])->middleware('auth')->name('tag');
Route::post('tag/{id}', [TagController::class, 'destroy'])->middleware('auth')->name('delete.tag');
Route::get('new-tag', [TagController::class, 'create'])->middleware('auth')->name('add.tag');
Route::post('new-tag', [TagController::class, 'store'])->middleware('auth');
Route::post('edit-tag/{id}', [TagController::class, 'update'])->middleware('auth');
Route::get('edit-tag/{id}', [TagController::class, 'edit'])->middleware('auth')->name('edit.tag');

Route::get('items', [ItemsController::class, 'index'])->middleware('auth')->name('items');
Route::get('new-item', [ItemsController::class, 'create'])->middleware('auth')->name('add.item');
Route::post('new-item',[ItemsController::class, 'store'])->middleware('auth');
Route::get('edit-item/{id}',[ItemsController::class, 'edit'])->middleware('auth')->name('edit.item');
Route::post('edit-item/{id}',[ItemsController::class, 'update'])->middleware('auth');
Route::post('items/{id}', [ItemsController::class, 'destroy'])->middleware('auth')->name('delete.item');

Route::get('/refresh-captcha', function () {
    return response()->json(['captcha' => asset('captcha/flat')]);
});


Route::group(['middleware' => 'auth'], function () {
	Route::get('charts', function () {
		return view('pages.charts');
	})->name('charts');

	Route::get('notifications', function () {
		return view('pages.notifications');
	})->name('notifications');

	Route::get('pricing-page', function () {
		return view('pages.pricing-page');
	})->name('pricing-page');

    Route::get('rtl', function () {
		return view('pages.rtl');
	})->name('rtl');

	Route::get('sweet-alerts', function () {
		return view('pages.sweet-alerts');
	})->name('sweet-alerts');

	Route::get('widgets', function () {
		return view('pages.widgets');
	})->name('widgets');

	Route::get('vr-default', function () {
		return view('pages.vr.vr-default');
	})->name('vr-default');

	Route::get('vr-info', function () {
		return view("pages.vr.vr-info");
	})->name('vr-info');

	Route::get('new-user', function () {
		return view('pages.users.new-user');
	})->name('new-user');

    Route::get('reports', function () {
		return view('pages.users.reports');
	})->name('reports');

    Route::get('general', function () {
		return view('pages.projects.general');
	})->name('general');

	Route::get('new-project', function () {
		return view('pages.projects.new-project');
	})->name('new-project');

	Route::get('timeline', function () {
		return view('pages.projects.timeline');
	})->name('timeline');

	#Route::get('overview', function () {
	#	return view('pages.profile.overview');
	#})->name('overview');

	Route::get('projects', function () {
		return view("pages.profile.projects");
	})->name('projects');

	Route::get('billing', function () {
		return view('pages.account.billing');
	})->name('billing');

    Route::get('invoice', function () {
		return view('pages.account.invoice');
	})->name('invoice');

    Route::get('security', function () {
		return view('pages.account.security');
	})->name('security');

	Route::get('settings', function () {
		return view('pages.account.settings');
	})->name('settings');

	Route::get('referral', function () {
		return view('ecommerce.referral');
	})->name('referral');

	Route::get('details', function () {
		return view('ecommerce.orders.details');
	})->name('details');

	Route::get('list', function () {
		return view("ecommerce.orders.list");
	})->name('list');

	Route::get('edit-product', function () {
		return view('ecommerce.products.edit-product');
	})->name('edit-product');

    Route::get('new-product', function () {
		return view('ecommerce.products.new-product');
	})->name('new-product');

    Route::get('product-page', function () {
		return view('ecommerce.products.product-page');
	})->name('product-page');

    Route::get('products-list', function () {
		return view('ecommerce.products.products-list');
	})->name('products-list');

	Route::get('automotive', function () {
		return view('dashboard.automotive');
	})->name('automotive');

	Route::get('discover', function () {
		return view('dashboard.discover');
	})->name('discover');

	Route::get('sales', function () {
		return view('dashboard.sales');
	})->name('sales');

	Route::get('smart-home', function () {
		return view("dashboard.smart-home");
	})->name('smart-home');

	Route::get('404', function () {
		return view('errors.404');
	})->name('404');

    Route::get('500', function () {
		return view('errors.500');
	})->name('500');

    Route::get('basic-lock', function () {
		return view('authentication.lock.basic');
	})->name('basic-lock');

    Route::get('cover-lock', function () {
		return view('authentication.lock.cover');
	})->name('cover-lock');

    Route::get('illustration-lock', function () {
		return view('authentication.lock.illustration');
	})->name('illustration-lock');

    Route::get('basic-reset', function () {
		return view('authentication.reset.basic');
	})->name('basic-reset');

    Route::get('cover-reset', function () {
		return view('authentication.reset.cover');
	})->name('cover-reset');

    Route::get('illustration-reset', function () {
		return view('authentication.reset.illustration');
	})->name('illustration-reset');

    Route::get('basic-sign-in', function () {
		return view('authentication.sign-in.basic');
	})->name('basic-sign-in');

    Route::get('cover-sign-in', function () {
		return view('authentication.sign-in.cover');
	})->name('cover-sign-in');

    Route::get('illustration-sign-in', function () {
		return view('authentication.sign-in.illustration');
	})->name('illustration-sign-in');

    Route::get('basic-sign-up', function () {
		return view('authentication.sign-up.basic');
	})->name('basic-sign-up');

    Route::get('cover-sign-up', function () {
		return view('authentication.sign-up.cover');
	})->name('cover-sign-up');

    Route::get('illustration-sign-up', function () {
		return view('authentication.sign-up.illustration');
	})->name('illustration-sign-up');

    Route::get('basic-verification', function () {
		return view('authentication.verification.basic');
	})->name('basic-verification');

    Route::get('cover-verification', function () {
		return view('authentication.verification.cover');
	})->name('cover-verification');

    Route::get('illustration-verification', function () {
		return view('authentication.verification.illustration');
	})->name('illustration-verification');

    Route::get('calendar', function () {
		return view('applications.calendar');
	})->name('calendar');

    Route::get('crm', function () {
		return view('applications.crm');
	})->name('crm');

    Route::get('datatables', function () {
		return view('applications.datatables');
	})->name('datatables');

    Route::get('kanban', function () {
		return view('applications.kanban');
	})->name('kanban');

    Route::get('stats', function () {
		return view('applications.stats');
	})->name('stats');

    Route::get('wizard', function () {
		return view('applications.wizard');
	})->name('wizard');
});