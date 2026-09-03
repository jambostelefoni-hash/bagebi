<?php

namespace App\Http\Controllers;

use App\Model\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicPageController extends Controller
{
    private function defaults(): array
    {
        return [
            'home' => [
                'title' => 'მთავარი გვერდი',
                'body' => 'საიტის მთავარი გვერდის ტექსტი.',
                'meta' => $this->homeMetaDefaults('მთავარი გვერდი', 'საიტის მთავარი გვერდის ტექსტი.')
            ],
            'about' => [
                'title' => 'ჩვენ შესახებ',
                'body' => 'ჩვენ შესახებ ტექსტი.'
            ],
            'contact' => [
                'title' => 'კონტაქტი',
                'body' => "ტელეფონი: \nელ-ფოსტა: \nმისამართი:"
            ],
            'news' => [
                'title' => 'განცხადება',
                'body' => 'განცხადების ტექსტი.'
            ]
        ];
    }

    public function index()
    {
        foreach ($this->defaults() as $slug => $data) {
            PublicPage::firstOrCreate(['slug' => $slug], $data);
        }

        $pages = PublicPage::where('slug', '!=', 'faq')->orderBy('slug')->get();

        return view('public-pages.index', [
            'pages' => $pages
        ]);
    }

    public function edit($slug)
    {
        if ($slug === 'faq') {
            abort(404);
        }
        $defaults = $this->defaults();
        $page = PublicPage::firstOrCreate(
            ['slug' => $slug],
            $defaults[$slug] ?? ['title' => ucfirst($slug), 'body' => '']
        );

        $meta = [];
        if ($slug === 'home') {
            $meta = $this->homeMetaDefaults($page->title, $page->body);
            if (is_array($page->meta)) {
                $meta = array_replace($meta, $page->meta);
            }
        }

        return view('public-pages.edit', [
            'page' => $page,
            'meta' => $meta
        ]);
    }

    public function update(Request $request, $slug)
    {
        if ($slug === 'faq') {
            abort(404);
        }
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000']
        ];

        if ($slug === 'home') {
            $rules = array_merge($rules, [
                'meta.nav_brand' => ['nullable', 'string', 'max:255'],
                'meta.nav_about_label' => ['nullable', 'string', 'max:255'],
                'meta.nav_news_label' => ['nullable', 'string', 'max:255'],
                'meta.nav_rules_label' => ['nullable', 'string', 'max:255'],
                'meta.nav_contact_label' => ['nullable', 'string', 'max:255'],
                'meta.nav_status_label' => ['nullable', 'string', 'max:255'],
                'meta.nav_register_label' => ['nullable', 'string', 'max:255'],
                'meta.hero_title' => ['nullable', 'string', 'max:255'],
                'meta.hero_lead' => ['nullable', 'string', 'max:10000'],
                'meta.card_about_title' => ['nullable', 'string', 'max:255'],
                'meta.card_about_body' => ['nullable', 'string', 'max:1000'],
                'meta.card_contact_title' => ['nullable', 'string', 'max:255'],
                'meta.card_contact_body' => ['nullable', 'string', 'max:1000'],
                'meta.card_status_title' => ['nullable', 'string', 'max:255'],
                'meta.card_status_body' => ['nullable', 'string', 'max:1000']
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $page = PublicPage::where('slug', $slug)->firstOrFail();
        $page->fill($request->only(['title', 'body']));

        if ($slug === 'home') {
            $metaDefaults = $this->homeMetaDefaults($page->title, $page->body);
            $inputMeta = $request->input('meta', []);
            $meta = array_replace($metaDefaults, is_array($inputMeta) ? $inputMeta : []);
            $page->meta = array_intersect_key($meta, $metaDefaults);
        }
        $changes = $this->buildAuditChanges($page);
        $page->save();

        $this->logAudit('public_page.update', PublicPage::class, $page->id, 'Public page updated', $changes);

        return back()->with([
            'flashType' => 'success',
            'flashMessage' => 'გვერდი განახლდა წარმატებით'
        ]);
    }

    private function homeMetaDefaults(string $title, ?string $body): array
    {
        return [
            'nav_brand' => 'საბავშვო ბაღების გაერთიანება',
            'nav_about_label' => 'ჩვენ შესახებ',
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
