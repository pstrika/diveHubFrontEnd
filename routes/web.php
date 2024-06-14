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
    return redirect('sign-in');
})->middleware('guest');


/* Routes for diveHub */
Route::get('Trips/{date}', 'App\Http\Controllers\TripsController@show')->middleware('auth')->name('Trips');
Route::get('Trips/', 'App\Http\Controllers\TripsController@show')->middleware('auth')->name('Trips');

Route::get('Weather/{location}', 'App\Http\Controllers\WeatherController@show')->middleware('auth')->name('Weather');
Route::get('Weather/', 'App\Http\Controllers\WeatherController@show')->middleware('auth')->name('Weather');

Route::get('CalendarT/{tripType}/{date}', 'App\Http\Controllers\CalendarTController@show')->middleware('auth')->name('CalendarT');
Route::get('CalendarT/{tripType}', 'App\Http\Controllers\CalendarTController@show')->middleware('auth')->name('CalendarT');
Route::get('CalendarT/', 'App\Http\Controllers\CalendarTController@show')->middleware('auth')->name('CalendarT');
Route::get('CalendarShark/{date}', 'App\Http\Controllers\CalendarTController@showShark')->middleware('auth')->name('CalendarShark');
Route::get('CalendarShark/', 'App\Http\Controllers\CalendarTController@showShark')->middleware('auth')->name('CalendarShark');
Route::get('CalendarLobster/{date}', 'App\Http\Controllers\CalendarTController@showLobster')->middleware('auth')->name('CalendarLobster');
Route::get('CalendarLobster/', 'App\Http\Controllers\CalendarTController@showLobster')->middleware('auth')->name('CalendarLobster');

Route::get('TripDetails/{tripId}', 'App\Http\Controllers\TripDetailsController@show')->middleware('auth')->name('TripDetails');

Route::get('Operators/', 'App\Http\Controllers\OperatorController@show')->middleware('auth')->name('Operators');
Route::get('Waivers', 'App\Http\Controllers\OperatorController@getWaivers')->middleware('auth')->name('Waivers');

Route::get('OperatorDetails/{id}', 'App\Http\Controllers\OperatorController@show')->middleware('auth')->name('OperatorDetails');

Route::get('BeachDiving', 'App\Http\Controllers\SiteController@showBeach')->middleware('auth')->name('BeachDiving');

Route::get('PlatformHealth/', 'App\Http\Controllers\OperatorController@showHealth')->middleware('auth')->name('PlatformHealth');

Route::get('new-site/', 'App\Http\Controllers\SiteController@create')->middleware('auth')->name('new-site');
Route::post('new-site', 'App\Http\Controllers\SiteController@store')->middleware('auth')->name('new-site-store');

Route::get('new-site-uploadPics', 'App\Http\Controllers\SiteController@create')->middleware('auth')->name('new-site-uploadPics');
Route::post('new-site-uploadPics', 'App\Http\Controllers\SiteController@updateMedia')->middleware('auth')->name('new-site-uploadPics');

Route::post('upload', 'App\Http\Controllers\SiteController@upload')->middleware('auth')->name('upload');

Route::post('new-site-updatePicsDesc', 'App\Http\Controllers\SiteController@updateDesc')->middleware('auth')->name('new-site-updatePicsDesc');

Route::get('SiteDetails/{id}', 'App\Http\Controllers\SiteController@show')->middleware('auth')->name('SiteDetails');
Route::get('SiteDetails', 'App\Http\Controllers\SiteController@show')->middleware('auth')->name('SiteDetails');
Route::post('RateSite', 'App\Http\Controllers\SiteRatingController@new')->middleware('auth')->name('RateSite');

Route::get('DiveSites', 'App\Http\Controllers\SiteController@showTopRated')->middleware('auth')->name('DiveSites');
Route::get('DiveSitesSearch', 'App\Http\Controllers\SiteController@searchSites')->middleware('auth')->name('DiveSitesSearch');
Route::post('DiveSitesSearch', 'App\Http\Controllers\SiteController@searchSites')->middleware('auth')->name('DiveSitesSearch');
Route::get('DiveSitesMap', 'App\Http\Controllers\SiteController@showAll')->middleware('auth')->name('DiveSitesMap');
Route::get('DiveSitesAdmin', 'App\Http\Controllers\SiteController@showAllAdmin')->middleware('auth')->name('DiveSitesAdmin');
Route::get('DeleteDiveSite/{id}', 'App\Http\Controllers\SiteController@delete')->middleware('auth')->name('DeleteDiveSite');

Route::get('DeleteDiveSite', 'App\Http\Controllers\SiteController@delete')->middleware('auth')->name('DeleteDiveSite');

Route::get('overview', 'App\Http\Controllers\UserController@getProfile')->middleware('auth')->name('overview');
Route::post('overview', 'App\Http\Controllers\UserController@updateProfile')->middleware('auth')->name('overview');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::get('sign-up', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('sign-up', [RegisterController::class, 'store'])->middleware('guest');

Route::get('sign-in', [SessionsController::class, 'create'])->middleware('guest')->name('login');
Route::post('sign-in', [SessionsController::class, 'store'])->middleware('guest');

Route::post('sign-out', [SessionsController::class, 'destroy'])->middleware('auth')->name('logout');

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


Route::get('users-management', [UserManagementController::class, 'index'])->middleware('auth')->name('users');
Route::get('add-new-user', [UserManagementController::class, 'create'])->middleware('auth')->name('add.user');
Route::post('add-new-user', [UserManagementController::class, 'store'])->middleware('auth');
Route::get('edit-user/{id}',[UserManagementController::class, 'edit'])->middleware('auth')->name('edit.user');
Route::post('edit-user/{id}',[UserManagementController::class, 'update'])->middleware('auth');
Route::post('users-management/{id}',[UserManagementController::class, 'destroy'])->middleware('auth')->name('delete.user');

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