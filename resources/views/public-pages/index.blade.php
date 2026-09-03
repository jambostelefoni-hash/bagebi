@extends('layouts.app')

@section('content')
<div class="modern-header">
    <div class="container-fluid header-content">
        <div>
            <h1>საჯარო გვერდები</h1>
            <p>სიღნაღის მუნიციპალიტეტის სკოლამდელი აღზრდის დაწესებულებათა გაერთიანება</p>
        </div>
        <div class="header-emblem">
            <img src="{{ asset('images/city-badge.png') }}" alt="City Emblem">
        </div>
    </div>
</div>

<section class="content">
  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-hover text-nowrap">
        <thead>
          <tr>
            <th>Slug</th>
            <th>სათაური</th>
            <th>მოქმედება</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($pages as $page)
            @if ($page->slug !== 'about')
            <tr>
              <td>{{ $page->slug }}</td>
              <td>{{ $page->title }}</td>
              <td>
                <a href="{{ route('public-pages.edit', $page->slug) }}" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-edit"></i> რედაქტირება
                </a>
              </td>
            </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
