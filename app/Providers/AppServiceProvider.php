<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

use App\Model\Setting;
use App\Model\PublicPage;
use App\Model\RegistrationText;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    { $this->app->bind('path.public', function() {
        return base_path().'/../public_html';
    });
        //
        $setting_basic = Setting::where('slug', 'basic')->first(); $setting_date = Setting::where('slug', 'date')->first();
        if(!$setting_basic) $setting_basic = collect(); if(!$setting_date) $setting_date = collect();
        View::share('settings', ['basic' => $setting_basic->toArray(), 'date' => $setting_date->toArray()]);

        $publicBrand = 'საბავშვო ბაღების გაერთიანება';
        $publicNavLabels = [
            'news' => 'განცხადება',
            'rules' => 'წესები',
            'contact' => 'კონტაქტი',
            'status' => 'სტატუსი',
            'register' => 'ბავშვის რეგისტრაცია'
        ];
        $homePage = PublicPage::where('slug', 'home')->first();
        if ($homePage && is_array($homePage->meta) && !empty($homePage->meta['nav_brand'])) {
            $publicBrand = $homePage->meta['nav_brand'];
        }
        if ($homePage && is_array($homePage->meta)) {
            $publicNavLabels = array_merge($publicNavLabels, [
                'about' => $homePage->meta['nav_about_label'] ?? $publicNavLabels['about'],
                'news' => $homePage->meta['nav_news_label'] ?? $publicNavLabels['news'],
                'rules' => $homePage->meta['nav_rules_label'] ?? $publicNavLabels['rules'],
                'contact' => $homePage->meta['nav_contact_label'] ?? $publicNavLabels['contact'],
                'status' => $homePage->meta['nav_status_label'] ?? $publicNavLabels['status'],
                'register' => $homePage->meta['nav_register_label'] ?? $publicNavLabels['register']
            ]);
        }
        View::share('publicBrand', $publicBrand);
        View::share('publicNavLabels', $publicNavLabels);

        $publicRules = 'რეგისტრაციის წესები ჯერ არ არის დამატებული.';
        $registrationText = RegistrationText::first();
        if ($registrationText && !empty($registrationText->rules)) {
            $publicRules = $registrationText->rules;
        }
        View::share('publicRules', $publicRules);
    }
}







