<?php

namespace App\Http\Controllers;

use App\Model\PublicPage;
use App\Model\API\Kindergartener;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    public function home()
    {
        $page = PublicPage::where('slug', 'home')->first();

        if (!$page) {
            abort(404);
        }

        $meta = $this->homeMetaDefaults($page->title, $page->body);
        if (is_array($page->meta)) {
            $meta = array_merge($meta, $page->meta);
        }

        return view('public.home', [
            'page' => $page,
            'meta' => $meta
        ]);
    }

    public function show($slug)
    {
        return $this->showPage($slug);
    }

    public function statusTracker(Request $request)
    {
        $kid = null;
        $statusLabel = null;
        $query = $request->isMethod('post') ? $request->input('kids_personal_number') : null;
        $mobileLastFour = $request->isMethod('post') ? $request->input('mobile_last_four') : null;

        if ($request->isMethod('post')) {
            $request->validate(['kids_personal_number' => ['required', 'digits:11'], 'mobile_last_four' => ['required', 'digits:4']]);
            $kid = Kindergartener::with(['kindergarten', 'groupRange', 'activeStatus'])
                ->where('kids_personal_number', $query)
                ->where('mobile_number', 'like', '%'.$mobileLastFour)
                ->first();

            if ($kid) {
                $statusLabel = $kid->application_status_label ?: 'უცნობი';
            }
        }

        return view('public.status-tracker', [
            'kid' => $kid,
            'statusLabel' => $statusLabel,
            'query' => $query,
            'mobileLastFour' => $mobileLastFour,
        ]);
    }

    private function showPage(string $slug)
    {
        $page = PublicPage::where('slug', $slug)->first();

        if (!$page) {
            abort(404);
        }

        return view('public.page', [
            'page' => $page
        ]);
    }

    private function homeMetaDefaults(string $title, ?string $body): array
    {
        return [
            'nav_brand' => 'ბაღების გაერთიანება',
            'nav_news_label' => 'განცხადება',
            'nav_rules_label' => 'წესები',
            'nav_contact_label' => 'კონტაქტი',
            'nav_status_label' => 'სტატუსი',
            'nav_register_label' => 'ბავშვის რეგისტრაცია',
            'hero_title' => $title,
            'hero_lead' => $body ?? '',
            'card_about_title' => 'ჩვენ შესახებ',
            'card_about_body' => 'გაიგეთ მეტი ბაღების გაერთიანებაზე.',
            'card_contact_title' => 'კონტაქტი',
            'card_contact_body' => 'დაგვიკავშირდით ნებისმიერ დროს.',
            'card_status_title' => 'სტატუსის შემოწმება',
            'card_status_body' => 'შეამოწმეთ განაცხადის სტატუსი.'
        ];
    }
}
