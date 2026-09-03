@extends('layouts.app')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">რეგისტრაციის ტექსტი</h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('registration-texts.store') }}">
        @csrf
        

        <div class="form-group">
          <label for="subtitle">შეიყვანეთ ტექსტი</label>
          <input
            type="text"
            id="subtitle"
            name="subtitle"
            class="form-control"
            value="{{ old('subtitle', $model->subtitle) }}"
          >
        </div>

        

        <button type="submit" class="btn btn-success">
          <i class="far fa-save"></i> შენახვა
        </button>
      </form>
    </div>
  </div>
</section>
@endsection
