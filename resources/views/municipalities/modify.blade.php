@extends('layouts.app')

@section('content')

<div class="content-header">
	  <div class="container-fluid">
	   <div class="row mb-2">
	    <div class="col-sm-6">
	     <h1 class="m-0">მუნიციპალიტეტი</h1>
	    </div><!-- /.col -->
	   </div><!-- /.row -->
	  </div><!-- /.container-fluid -->
	 </div>
    <section class="content">
     {!! Form::model($model, ['route' => 'municipalities.store']) !!}
     {!! Form::hidden('id', $model->id) !!}
       <div class="card card-primary card-outline card-tabs">
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-8 order-2 order-md-1">
            <div class="tab-content" id="custom-tabs-three-tabContent">
            
             <div class="form-group">
			    {!! Form::label('name', 'დასახელება', ['class' => 'awesome']) !!}
			    {!! Form::text('name', $model->name, ['class' => 'form-control']) !!}
			  </div>

            </div>       
          </div>
          <div class="col-12 col-md-12 col-lg-4 order-1 order-md-2">
            <div class="form-group">
            {!! Form::select('region_id', $data['regions'], null, ['class' => 'custom-select', 'placeholder' => 'აირჩიეთ რეგიონი']) !!}
            </div>
            <button onclick="location.href = '{{ route('municipalities.list') }}'" type="button" class="btn btn-danger  btn-block" style="margin-right: 5px;">
        <i class="far fa-window-close"></i> გაუქმება
      </button>
      <button type="submit" class="btn btn-success  btn-block">
        <i class="far fa-paper-plane"></i> გაგზავნა
      </button>
          </div>
        </div>        
      </div>
  </div>
     {!! Form::close() !!}

   
    </section>
@endsection

@push('scripts')
<script></script>
@endpush




