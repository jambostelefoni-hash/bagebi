@extends('layouts.app')

@section('content')

<div class="content-header">
	  <div class="container-fluid">
	   <div class="row mb-2">
	    <div class="col-sm-6">
	     <h1 class="m-0">ბაღი</h1>
	    </div><!-- /.col -->
	   </div><!-- /.row -->
	  </div><!-- /.container-fluid -->
	 </div>
    <section class="content">
     {!! Form::model($model, ['route' => 'kindergartens.store']) !!}
     {!! Form::hidden('id', $model->id) !!}
       <div class="card card-primary card-outline card-tabs">
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-8 order-2 order-md-1">
            <div class="tab-content" id="custom-tabs-three-tabContent">
            
             <div class="form-group">
			    {!! Form::label('name', 'ბაღის დასახელება', ['class' => 'awesome']) !!}
			    {!! Form::text('name', $model->name, ['class' => 'form-control']) !!}
			  </div>

       
<div>


  
            <nav class="w-100">
              <div class="nav nav-tabs" id="product-tab" role="tablist">
                
                @foreach ($data['group_ranges'] as $key => $range)
  <a class="nav-item nav-link @if($loop->first){{ 'active' }}@endif" id="tab-desc-{{$key}}" data-toggle="tab" href="#tab-{{$key}}" role="tab" aria-controls="tab-{{$key}}" aria-selected="@if ($loop->first) {{ 'true' }} @endif">{{$range}} </a>
  @endforeach

                
                
                
              </div>
            </nav>
            <div class="tab-content p-3" id="nav-tabContent">

@foreach ($data['group_ranges'] as $key => $range)

@php
$ageRange = ($model->currentAge($key)) ? $model->currentAge($key)->toArray() : [];
@endphp

<div class="tab-pane fade @if($loop->first){{ 'active show' }}@endif" id="tab-{{$key}}" role="tabpanel" aria-labelledby="tab-desc-{{$key}}">
<div class="table-responsive">
                    <table class="table">
                      <tbody><tr>
                        <th style="width:50%">ადგილების რაოდენობა:</th>
                        <td>
          {{ Form::text('range['.$key.'][space_length]', data_get($ageRange, 'pivot.space_length', 0), ['class' => 'form-control']) }}
      </td>
                      </tr>
                      <tr>
                        <th>შევსებული ადგილი:</th>
                        <td>{{ Form::text('range['.$key.'][space_filled]', data_get($ageRange, 'by_id.total', 0), ['class' => 'form-control', 'readonly' => true]) }}</td>
                      </tr>
                      <tr>
                        <th>თავისუფალი ადგილი</th>
                        <td>
                          <input type="text" class="form-control" readonly 
                            name="range[{{$key}}][space_free]" value="{{data_get($ageRange, 'pivot.space_free', 0)}}">
                        </td>
                      </tr>
                    </tbody></table>
                  </div>
</div>

@endforeach
              
              
             
            </div>
          </div> 

            </div>       
          </div>
          <div class="col-12 col-md-12 col-lg-4 order-1 order-md-2">
           
            <div class="form-group">
            {!! Form::select('municipality_id', $data['municipalities'], null, ['class' => 'custom-select', 'placeholder' => 'აირჩიეთ რეგიონი']) !!}
            </div>
            <button onclick="location.href = '{{ route('kindergartens.list') }}'" type="button" class="btn btn-danger  btn-block" style="margin-right: 5px;">
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







