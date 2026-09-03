@extends('layouts.app')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">რეგისტრაციის წესები</h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('registration-texts.rules.store') }}">
        @csrf
        <div class="form-group">
          <label for="rules">წესების ტექსტი</label>
          <textarea
            id="rules"
            name="rules"
            class="form-control"
            rows="10"
          >{{ old('rules', $model->rules) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">
          <i class="far fa-save"></i> შენახვა
        </button>
      </form>
    </div>
  </div>
</section>
@endsection
