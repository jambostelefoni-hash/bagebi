@extends('layouts.app')

@section('content')

<div class="content-header">
	  <div class="container-fluid">
	   <div class="row mb-2">
	    <div class="col-sm-6">
	     <h1 class="m-0">რეგიონი</h1>
	    </div><!-- /.col -->
	   </div><!-- /.row -->
	  </div><!-- /.container-fluid -->
	 </div>
    <section class="content">
      <form method="POST" action="{{ route('register') }}">
       @csrf
       <div class="card card-primary card-outline card-tabs">
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-8 order-2 order-md-1">
            <div class="tab-content" id="custom-tabs-three-tabContent">
            
            <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('სახელი') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}">

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('ელ.ფოსტა') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('პაროლი') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" >

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('დაადასტურეთ პაროლი') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" >
                            </div>
                        </div>

            </div>       
          </div>
          <div class="col-12 col-md-12 col-lg-4 order-1 order-md-2">
            <button type="button" class="btn btn-danger  btn-block" style="margin-right: 5px;">
        <i class="far fa-window-close"></i> გაუქმება
      </button>
      <button type="submit" class="btn btn-success  btn-block">
        <i class="far fa-paper-plane"></i> გაგზავნა
      </button>
          </div>
        </div>        
      </div>
  </div>


   
    </section>
@endsection

@push('scripts')
<script></script>
@endpush


