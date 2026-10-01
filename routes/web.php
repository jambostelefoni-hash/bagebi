<?php

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


Route::get('/login', 'Auth\\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\\LoginController@login');
Route::post('/logout', 'Auth\\LoginController@logout')->name('logout');
Route::get('/password/reset', 'Auth\\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\\ResetPasswordController@reset')->name('password.update');
Route::get('/password/confirm', 'Auth\\ConfirmPasswordController@showConfirmForm')->name('password.confirm');
Route::post('/password/confirm', 'Auth\\ConfirmPasswordController@confirm');

Route::get('/', 'PublicSiteController@home')->name('public.home');
Route::get('/kids-registration', 'ChildrenController@index')->name('children');
Route::view('/registration-rules', 'public.rules')->name('public.registration-rules');
Route::get('/home-public', 'PublicSiteController@home')->name('public.home.alias');
Route::get('/about', 'PublicSiteController@show')->name('public.about')->defaults('slug', 'about');
Route::get('/contact', 'PublicSiteController@show')->name('public.contact')->defaults('slug', 'contact');
Route::get('/news', 'PublicSiteController@show')->name('public.news')->defaults('slug', 'news');
Route::get('/status-tracker', 'PublicSiteController@statusTracker')->name('public.status-tracker');
Route::post('/status-tracker', 'PublicSiteController@statusTracker')->middleware('throttle:10,1')->name('public.status-tracker.search');

Route::group(['middleware' => ['auth', 'check']], function () {

  Route::get('/home', 'HomeController@index')->name('home');
  Route::view('/structure', 'hubs.structure')->middleware('role:union_admin')->name('structure.index');
  Route::view('/control-center', 'hubs.control-center')->middleware('role:union_admin')->name('control-center.index');
  Route::view('/guide', 'guide.index')->middleware('role:union_admin')->name('guide.index');
  Route::get('/profile', 'ProfileController@edit')->name('profile.edit');
  Route::patch('/profile', 'ProfileController@update')->name('profile.update');
  Route::post('/guided-tour/complete', 'GuidedTourController@complete')->name('guided-tour.complete');

  Route::prefix('auth')->namespace('Auth')->middleware('role:union_admin')->name('users.')->group(function () {
    Route::get('register', 'RegisterController@showRegistrationForm')->name('create');
    Route::post('register', 'RegisterController@register')->name('register');
  });

  Route::prefix('users')->name('users.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'UserController@index')->name('list');
    Route::get('show/{id?}', 'UserController@show')->name('show');
    Route::post('store', 'UserController@store')->name('store');
    Route::delete('destroy/{id}', 'UserController@destroy')->name('destroy');
  });

  Route::prefix('regions')->name('regions.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'RegionController@index')->name('list');
    Route::get('show/{id?}', 'RegionController@show')->name('show');
    Route::post('store', 'RegionController@store')->name('store');
    Route::delete('destroy/{id}', 'RegionController@destroy')->name('destroy');
  });

  Route::prefix('municipalities')->name('municipalities.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'MunicipalityController@index')->name('list');
    Route::get('show/{id?}', 'MunicipalityController@show')->name('show');
    Route::post('store', 'MunicipalityController@store')->name('store');
    Route::delete('destroy/{id}', 'MunicipalityController@destroy')->name('destroy');
  });

  Route::prefix('prioriteties')->name('prioriteties.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'PriorityController@index')->name('list');
    Route::get('show/{id?}', 'PriorityController@show')->name('show');
    Route::post('store', 'PriorityController@store')->name('store');
    Route::delete('destroy/{id}', 'PriorityController@destroy')->name('destroy');
  });

  Route::prefix('kindergartens')->name('kindergartens.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'KindergartenController@index')->name('list');
    Route::get('show/{id?}', 'KindergartenController@show')->name('show');
    Route::post('store', 'KindergartenController@store')->name('store');
    Route::delete('destroy/{id}', 'KindergartenController@destroy')->name('destroy');
  });

  Route::prefix('group-age-ranges')->name('group-age-ranges.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'GroupAgeRangeController@index')->name('list');
    Route::get('show/{id?}', 'GroupAgeRangeController@show')->name('show');
    Route::post('store', 'GroupAgeRangeController@store')->name('store');
    Route::delete('destroy/{id}', 'GroupAgeRangeController@destroy')->name('destroy');
  });

  Route::namespace('API')->prefix('kindergarteners')->name('kindergarteners.')->group(function () {
    Route::get('', 'KindergartenerController@index')->name('index');
    Route::get('data', 'KindergartenerController@dataTable')->name('data');
    Route::get('show/{id?}', 'KindergartenerController@show')->name('show');
    Route::post('store', 'KindergartenerController@store')->name('store');
    Route::post('order', 'KindergartenerController@order')->middleware('role:union_admin')->name('order');
    Route::delete('destroy/{id}', 'KindergartenerController@destroy')->name('destroy')->middleware('role:union_admin');
    Route::get('export', 'KindergartenerController@export')->name('export');
  });

  Route::prefix('settings')->name('settings.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'SettingController@index')->name('index');
    Route::post('store', 'SettingController@store')->name('store');
    Route::get('date', 'SettingController@date')->name('date');
    Route::post('date-store', 'SettingController@dateStore')->name('date-store');
    Route::post('learning-start', 'SettingController@learningStart')->name('learningStart');
    Route::post('learning-end', 'SettingController@learningEnd')->name('learningEnd');
    Route::post('learning', 'SettingController@learning')->name('learning');
    Route::get('porting-preview', 'SettingController@portingPreview')->name('porting-preview');
  });

  Route::prefix('registration-texts')->name('registration-texts.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'RegistrationTextController@index')->name('index');
    Route::post('store', 'RegistrationTextController@store')->name('store');
    Route::get('rules', 'RegistrationTextController@rules')->name('rules');
    Route::post('rules', 'RegistrationTextController@storeRules')->name('rules.store');
  });

  Route::prefix('public-pages')->name('public-pages.')->middleware('role:union_admin')->group(function () {
    Route::get('', 'PublicPageController@index')->name('index');
    Route::get('{slug}/edit', 'PublicPageController@edit')->name('edit');
    Route::put('{slug}', 'PublicPageController@update')->name('update');
  });

  Route::prefix('audit-logs')->name('audit-logs.')->middleware('role:union_admin')->group(function () {
  Route::get('', 'AuditLogController@index')->name('index');
  Route::get('export', 'AuditLogController@export')->name('export');
  });
  Route::get('/operations', 'OperationsController@index')->middleware('role:union_admin')->name('operations.index');
  Route::get('/registration-analytics', 'RegistrationAnalyticsController@index')->middleware('role:union_admin')->name('analytics.registration');
  Route::get('/system-health', 'SystemHealthController@index')->middleware('role:union_admin')->name('system-health.index');
  Route::get('/data-quality', 'DataQualityController@index')->middleware('role:union_admin')->name('data-quality.index');
  Route::post('/data-quality/scan', 'DataQualityController@scan')->middleware('role:union_admin')->name('data-quality.scan');
  Route::get('/data-quality/{issue}/edit', 'DataQualityController@edit')->middleware('role:union_admin')->name('data-quality.edit');
  Route::post('/operations/process-waiting-list', 'OperationsController@processWaitingList')->middleware('role:union_admin')->name('operations.process-waiting-list');
  Route::post('/operations/notifications/{id}/resend', 'OperationsController@resendNotification')->middleware('role:union_admin')->name('operations.notifications.resend');

  Route::get('/attendance', 'AttendanceController@index')->name('attendance.index');
  Route::post('/attendance', 'AttendanceController@store')->name('attendance.store');
  Route::post('/attendance/evaluate', 'AttendanceController@evaluate')->middleware('role:union_admin')->name('attendance.evaluate');
  Route::get('/attendance/export/{format}', 'AttendanceController@export')->name('attendance.export');
  Route::patch('/applications/{id}/status', 'ApplicationStatusController@update')->name('applications.status');
  Route::get('/reinstatement-requests', 'ReinstatementController@index')->middleware('role:union_admin')->name('reinstatement.index');
  Route::post('/reinstatement-requests/{id}/review', 'ReinstatementController@review')->middleware('role:union_admin')->name('reinstatement.review');
  Route::get('/reinstatement-requests/{id}/document', 'ReinstatementController@download')->middleware('role:union_admin')->name('reinstatement.download');
  Route::get('/work-calendar', 'WorkCalendarController@index')->middleware('role:union_admin')->name('calendar.index');
  Route::post('/work-calendar', 'WorkCalendarController@store')->middleware('role:union_admin')->name('calendar.store');
  Route::delete('/work-calendar/{id}', 'WorkCalendarController@destroy')->middleware('role:union_admin')->name('calendar.destroy');

});

Route::get('/placement-offers/{token}', 'PlacementOfferController@show')->name('placement-offers.show');
Route::post('/placement-offers/{token}', 'PlacementOfferController@respond')->middleware('throttle:10,1')->name('placement-offers.respond');
Route::get('/reinstatement/{token}', 'ReinstatementController@show')->name('reinstatement.show');
Route::post('/reinstatement/{token}', 'ReinstatementController@store')->middleware('throttle:5,1')->name('reinstatement.store');
Route::get('/s/{code}', 'ShortActionLinkController@show')->where('code', '[A-Za-z0-9]{20}')->name('short-action-links.show');
