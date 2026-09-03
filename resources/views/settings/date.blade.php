@extends('layouts.app')

@section('content')

<div class="content-header">
	  <div class="container-fluid">
	   <div class="row mb-2">
	    <div class="col-sm-6">
	     <h1 class="m-0">მართვა</h1>
	    </div><!-- /.col -->
	   </div><!-- /.row -->
	  </div><!-- /.container-fluid -->
	 </div>
	 
  <section class="content">
  	@if (!data_get($settings, 'basic.object.isLearningStart'))
  	<div class="alert alert-dismissible alert-warning">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> შეტყობინება!</h5>
            იმისთვის რომ საიტმა დაიწყოს მუშაობა, შეიყვანეთ სასწავლო წლის დაწყების და დასრულების თარიღი!
          </div>
    @endif
  {!! Form::model($model, ['route' => 'settings.date-store']) !!}
  <input type="hidden" name="slug" value="date">
  <div class="card card-primary card-outline card-tabs settings-date-card">
      <div class="card-body">
<div class="form-row settings-date-form-row">
    <div class="col-md-4 mb-3">
      <label for="validationTooltip01">დაწყება</label>
      <input readonly id="start" type="text" class="form-control" name="object[start]" value="{{data_get($settings, 'date.object.start')}}" autocomplete="off">
    </div>
    <div class="col-md-4 mb-3">
      <label for="validationTooltip02">დასრულება</label>
      <input readonly name="object[end]" type="text" class="form-control" id="end" value="{{data_get($settings, 'date.object.end')}}" autocomplete="off">
    </div>
</div>
<button class="btn btn-primary" type="submit">შენახვა</button>
      
  </div>

</div>
{!! Form::close() !!}
  </section>
@endsection

@push('scripts')
<script>
const start = document.getElementById('start');
const datepickerStart = new Datepicker(start, { disableTouchKeyboard: true });

const end = document.getElementById('end');
const datepickerEnd = new Datepicker(end, {});
</script>
@endpush






@push('scripts')
<style>
  .datepicker {
    z-index: 9999 !important;
  }

  .card.settings-date-card,
  .card.settings-date-card .card-body,
  .content,
  .content > *,
  section.content {
    overflow: visible !important;
  }
</style>

<script>
const start = document.getElementById('start');
const datepickerStart = new Datepicker(start, { disableTouchKeyboard: true });
const end = document.getElementById('end');
const datepickerEnd = new Datepicker(end, {});
</script>
@endpush







