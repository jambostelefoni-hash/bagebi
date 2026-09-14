<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedDefaultPublicPages extends Migration
{
    public function up()
    {
        $now = now();
        $pages = [
            'home' => [
                'title' => 'საბავშვო ბაღების გაერთიანება',
                'body' => 'ერთიანი, მარტივი და უსაფრთხო სივრცე ბავშვების რეგისტრაციისა და ბაღების მართვისთვის.',
                'meta' => [
                    'nav_brand' => 'საბავშვო ბაღების გაერთიანება',
                    'nav_about_label' => 'ჩვენ შესახებ',
                    'nav_news_label' => 'განცხადება',
                    'nav_rules_label' => 'წესები',
                    'nav_contact_label' => 'კონტაქტი',
                    'nav_status_label' => 'სტატუსი',
                    'nav_register_label' => 'ბავშვის რეგისტრაცია',
                    'hero_title' => 'საბავშვო ბაღების გაერთიანება',
                    'hero_lead' => 'ერთიანი, მარტივი და უსაფრთხო სივრცე ბავშვების რეგისტრაციისა და ბაღების მართვისთვის.',
                    'card_about_title' => 'ჩვენ შესახებ',
                    'card_about_body' => 'გაიგეთ მეტი ბაღების გაერთიანებაზე.',
                    'card_contact_title' => 'კონტაქტი',
                    'card_contact_body' => 'დაგვიკავშირდით ნებისმიერ დროს.',
                    'card_status_title' => 'სტატუსის შემოწმება',
                    'card_status_body' => 'შეამოწმეთ განაცხადის სტატუსი.'
                ]
            ],
            'about' => ['title' => 'ჩვენ შესახებ', 'body' => 'ინფორმაცია საბავშვო ბაღების გაერთიანების შესახებ.'],
            'contact' => ['title' => 'კონტაქტი', 'body' => "ტელეფონი: \nელ-ფოსტა: \nმისამართი:"],
            'news' => ['title' => 'განცხადება', 'body' => 'განცხადების ტექსტი.'],
        ];

        foreach ($pages as $slug => $page) {
            DB::table('public_pages')->insertOrIgnore(
                [
                    'slug' => $slug,
                    'title' => $page['title'],
                    'body' => $page['body'],
                    'meta' => isset($page['meta']) ? json_encode($page['meta'], JSON_UNESCAPED_UNICODE) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down()
    {
        DB::table('public_pages')->whereIn('slug', ['home', 'about', 'contact', 'news'])->delete();
    }
}
