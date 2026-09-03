@extends('layouts.app')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">{{ $page->title }}</h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('public-pages.update', $page->slug) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
          <label for="title">სათაური</label>
          <input
            id="title"
            name="title"
            type="text"
            class="form-control"
            value="{{ old('title', $page->title) }}"
            required
          >
        </div>

        <div class="form-group">
          <label for="body">ტექსტი</label>
          <textarea
            id="body"
            name="body"
            class="form-control"
            rows="10"
          >{{ old('body', $page->body) }}</textarea>
        </div>

        @if ($page->slug === 'home')
          <hr>
          <h5>მთავარი გვერდის ბლოკები</h5>

          <div class="form-group">
            <label for="meta_nav_brand">ლოგოს ტექსტი</label>
            <input
              id="meta_nav_brand"
              name="meta[nav_brand]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_brand', $meta['nav_brand'] ?? '') }}"
            >
          </div>

          <div class="form-group">
            <label for="meta_nav_about_label">მენიუ: ჩვენ შესახებ</label>
            <input
              id="meta_nav_about_label"
              name="meta[nav_about_label]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_about_label', $meta['nav_about_label'] ?? '') }}"
            >
          </div>

          <div class="form-group">
            <label for="meta_nav_news_label">მენიუ: განცხადება</label>
            <input
              id="meta_nav_news_label"
              name="meta[nav_news_label]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_news_label', $meta['nav_news_label'] ?? '') }}"
            >
          </div>

          <div class="form-group">
            <label for="meta_nav_rules_label">მენიუ: წესები</label>
            <input
              id="meta_nav_rules_label"
              name="meta[nav_rules_label]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_rules_label', $meta['nav_rules_label'] ?? '') }}"
            >
          </div>

          <div class="form-group">
            <label for="meta_nav_contact_label">მენიუ: კონტაქტი</label>
            <input
              id="meta_nav_contact_label"
              name="meta[nav_contact_label]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_contact_label', $meta['nav_contact_label'] ?? '') }}"
            >
          </div>

          <div class="form-group">
            <label for="meta_nav_status_label">მენიუ: სტატუსი</label>
            <input
              id="meta_nav_status_label"
              name="meta[nav_status_label]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_status_label', $meta['nav_status_label'] ?? '') }}"
            >
          </div>

          <div class="form-group">
            <label for="meta_nav_register_label">მენიუ: რეგისტრაცია</label>
            <input
              id="meta_nav_register_label"
              name="meta[nav_register_label]"
              type="text"
              class="form-control"
              value="{{ old('meta.nav_register_label', $meta['nav_register_label'] ?? '') }}"
            >
          </div>

          

          
          
        @endif

        <button type="submit" class="btn btn-success">
          <i class="far fa-save"></i> შენახვა
        </button>
      </form>
    </div>
  </div>
</section>
@endsection
