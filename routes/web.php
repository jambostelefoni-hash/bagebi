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


Auth::routes();

Route::get('/', 'PublicSiteController@home')->name('public.home');
Route::get('/kids-registration', 'ChildrenController@index')->name('children');
Route::get('/home-public', 'PublicSiteController@home')->name('public.home.alias');
Route::get('/about', 'PublicSiteController@show')->name('public.about')->defaults('slug', 'about');
Route::get('/contact', 'PublicSiteController@show')->name('public.contact')->defaults('slug', 'contact');
Route::get('/news', 'PublicSiteController@show')->name('public.news')->defaults('slug', 'news');
Route::get('/status-tracker', 'PublicSiteController@statusTracker')->name('public.status-tracker');
Route::namespace('API')->prefix('kindergarteners')->name('kindergarteners.')->group(function () {
  Route::get('export', 'KindergartenerController@export')->name('export');
});

Route::group(['middleware' => ['auth', 'check']], function () {

  Route::get('/home', 'HomeController@index')->name('home');

  Route::prefix('auth')->namespace('Auth')->group(function () {
    Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'RegisterController@register')->name('register');
  });

  Route::prefix('users')->name('users.')->group(function () {
    Route::get('', 'UserController@index')->name('list');
    Route::get('show/{id?}', 'UserController@show')->name('show');
    Route::post('store', 'UserController@store')->name('store');
    Route::get('destroy{id?}', 'UserController@destroy')->name('destroy');
  });

  Route::prefix('regions')->name('regions.')->group(function () {
    Route::get('', 'RegionController@index')->name('list');
    Route::get('show/{id?}', 'RegionController@show')->name('show');
    Route::post('store', 'RegionController@store')->name('store');
    Route::get('destroy{id?}', 'RegionController@destroy')->name('destroy');
  });

  Route::prefix('municipalities')->name('municipalities.')->group(function () {
    Route::get('', 'MunicipalityController@index')->name('list');
    Route::get('show/{id?}', 'MunicipalityController@show')->name('show');
    Route::post('store', 'MunicipalityController@store')->name('store');
    Route::get('destroy{id?}', 'MunicipalityController@destroy')->name('destroy');
  });

  Route::prefix('prioriteties')->name('prioriteties.')->group(function () {
    Route::get('', 'PriorityController@index')->name('list');
    Route::get('show/{id?}', 'PriorityController@show')->name('show');
    Route::post('store', 'PriorityController@store')->name('store');
    Route::get('destroy{id?}', 'PriorityController@destroy')->name('destroy');
  });

  Route::prefix('kindergartens')->name('kindergartens.')->group(function () {
    Route::get('', 'KindergartenController@index')->name('list');
    Route::get('show/{id?}', 'KindergartenController@show')->name('show');
    Route::post('store', 'KindergartenController@store')->name('store');
    Route::get('destroy{id?}', 'KindergartenController@destroy')->name('destroy');
  });

  Route::namespace('API')->prefix('kindergarteners')->name('kindergarteners.')->group(function () {
    Route::get('', 'KindergartenerController@index')->name('index');
    Route::get('show/{id?}', 'KindergartenerController@show')->name('show');
    Route::post('store', 'KindergartenerController@store')->name('store');
    Route::post('order', 'KindergartenerController@order')->name('order');
    Route::get('destroy{id?}', 'KindergartenerController@destroy')->name('destroy');
  });

  Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('', 'SettingController@index')->name('index');
    Route::post('store', 'SettingController@store')->name('store');
    Route::get('date', 'SettingController@date')->name('date');
    Route::post('date-store', 'SettingController@dateStore')->name('date-store');
    Route::post('learning-start', 'SettingController@learningStart')->name('learningStart');
    Route::post('learning-end', 'SettingController@learningEnd')->name('learningEnd');
    Route::post('learning', 'SettingController@learning')->name('learning');
  });

  Route::prefix('registration-texts')->name('registration-texts.')->group(function () {
    Route::get('', 'RegistrationTextController@index')->name('index');
    Route::post('store', 'RegistrationTextController@store')->name('store');
    Route::get('rules', 'RegistrationTextController@rules')->name('rules');
    Route::post('rules', 'RegistrationTextController@storeRules')->name('rules.store');
  });

  Route::prefix('public-pages')->name('public-pages.')->group(function () {
    Route::get('', 'PublicPageController@index')->name('index');
    Route::get('{slug}/edit', 'PublicPageController@edit')->name('edit');
    Route::put('{slug}', 'PublicPageController@update')->name('update');
  });

  Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
    Route::get('', 'AuditLogController@index')->name('index');
  });

});






