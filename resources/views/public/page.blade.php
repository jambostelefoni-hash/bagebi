@extends('layouts.basic')
@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;500;600;700&display=swap');

  * { box-sizing: border-box; }

  .public-page-shell {
    min-height: 100vh;
    background: #f4f7fb;
    padding: 56px 20px 100px;
    font-family: 'Noto Sans Georgian', sans-serif;
  }

  .public-container {
    max-width: 800px;
    margin: 0 auto;
    width: 100%;
  }

  /* Breadcrumb feel */
  .public-article {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 16px rgba(22,40,64,0.07);
    overflow: hidden;
  }

  .public-article-header {
    padding: 48px 52px 36px;
    border-bottom: 1px solid #eef1f6;
    position: relative;
  }

  .public-article-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #162840;
    border-radius: 8px 8px 0 0;
  }

  .article-tag {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #162840;
    background: #eef2f8;
    padding: 4px 10px;
    border-radius: 2px;
    margin-bottom: 16px;
  }

  .public-article-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #162840;
    margin: 0;
    line-height: 1.4;
    letter-spacing: -0.02em;
  }

  .public-article-body {
    padding: 40px 52px 52px;
    font-size: 0.93rem;
    color: #4a5568;
    line-height: 1.9;
    font-weight: 400;
  }

  /* Mobile */
  @media (max-width: 600px) {
    .public-page-shell {
      padding: 28px 12px 60px;
    }

    .public-article-header {
      padding: 32px 24px 24px;
    }

    .public-article-header h1 {
      font-size: 1.15rem;
    }

    .public-article-body {
      padding: 24px 24px 36px;
      font-size: 0.88rem;
    }
  }
</style>

<section class="public-page-shell">
  <div class="public-container">
    <article class="public-article">
        <span class="article-tag">{{ $page->title }}</span>
      
      </header>
      <div class="public-article-body">
        {!! nl2br(e($page->body)) !!}
      </div>
    </article>
  </div>
</section>

@endsection