<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

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
    {
        // Keep Laravel's standard /public directory. The old public_html path
        // breaks deployments where the application is installed as one folder.
        $setting_basic = collect();
        $setting_date = collect();
        if (Schema::hasTable('settings')) {
            $setting_basic = Setting::where('slug', 'basic')->first() ?: collect();
            $setting_date = Setting::where('slug', 'date')->first() ?: collect();
        }
        View::share('settings', ['basic' => $setting_basic->toArray(), 'date' => $setting_date->toArray()]);

        $publicBrand = 'საბავშვო ბაღების გაერთიანება';
        $publicNavLabels = [
            'about' => 'ჩვენ შესახებ',
            'news' => 'განცხადება',
            'rules' => 'წესები',
            'contact' => 'კონტაქტი',
            'status' => 'სტატუსი',
            'register' => 'ბავშვის რეგისტრაცია'
        ];
        $homePage = Schema::hasTable('public_pages')
            ? PublicPage::where('slug', 'home')->first()
            : null;
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
        $registrationText = Schema::hasTable('registration_texts')
            ? RegistrationText::first()
            : null;
        if ($registrationText && !empty($registrationText->rules)) {
            $publicRules = $registrationText->rules;
        }
        View::share('publicRules', $publicRules);
    }
}






