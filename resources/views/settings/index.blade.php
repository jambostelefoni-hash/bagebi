@extends('layouts.app')

@section('content')

<?php

function hasObjectKey($arr, $keyName) {
  return (array_key_exists($keyName, $arr) && $arr[$keyName] && !is_null($arr[$keyName]));
}

?>

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

    <div class="row">
<div class="col">        <div class="callout callout-info">
  {!! Form::model($model, ['route' => 'settings.learningStart']) !!}
                  <h5>სწავლის დაწყება</h5>
<hr/>
                  <p>
                    @if ($canStart)
                    <div class="custom-control custom-checkbox">
                          <button class="btn btn-danger" type="submit">დაწყება</button>
                        </div>
                    @else სწავლის დაწყების ბრძანებით სარგებლობა შეგეძლებათ <b>{{$permission['object']['start']}}</b> @endif
                  </p>
                  {!! Form::close() !!}
                </div>
</div>

<div class="col">        <div class="callout callout-info">
  {!! Form::model($model, ['route' => 'settings.learningEnd']) !!}
                  <h5>სწავლის დასრულება</h5>
<hr/>
                  <p>
                    @if ($canEnd)<div class="custom-control custom-checkbox">
                         <button class="btn btn-danger" type="submit">დასრულება</button>
                        </div>
                    @else სწავლის დასრულების ბრძანებით სარგებლობა შეგეძლებათ <b>{{$permission['object']['end']}}</b> @endif
                    </p>
                    {!! Form::close() !!}
                </div>
</div>
</div>

  {!! Form::model($model, ['route' => 'settings.store']) !!}
  <input type="hidden" name="slug" value="basic">
  <div class="card card-primary card-outline card-tabs">
      <div class="card-body">
<div class="row">
<div class="col">        <div class="callout callout-info">
                  <h5>რეგისტრაციის ჩართვა/გამორთვა</h5>
<hr/>
                  <p><div class="custom-control custom-checkbox">
                          <input class="custom-control-input" type="checkbox" 
                            @if (hasObjectKey($model->object, 'isRegistrationStart')) {{'checked'}} @endif id="customCheckbox2" value="true" name="object[isRegistrationStart]">
                          <label for="customCheckbox2" class="custom-control-label">@if (hasObjectKey($model->object, 'isRegistrationStart')) {{'ჩართული'}} @else {{'გამორთული'}} @endif</label>
                        </div></p>
                </div>
</div>
<div class="col">        <div class="callout callout-info">
                  <h5>პრიორიტეტების ჩართვა/გამორთვა</h5>
<hr/>
                  <p><div class="custom-control custom-checkbox">
                          <input class="custom-control-input" type="checkbox" id="customCheckbox3" 
                           @if(hasObjectKey($model->object, 'isPrioritetiesStart')) {{'checked'}} @endif name="object[isPrioritetiesStart]" value="true">
                          <label for="customCheckbox3" class="custom-control-label">@if (hasObjectKey($model->object, 'isPrioritetiesStart')) {{'ჩართული'}} @else {{'გამორთული'}} @endif</label>
                        </div></p>
                </div>
</div>
</div>


      <div class="form-group">
                <label for="inputDescription">შეტყობინება რეგისტრაციის თარიღისთვის:</label>
                <textarea name="object[nottification]" id="inputDescription" class="form-control" rows="4">
@if(hasObjectKey($model->object, 'isPrioritetiesStart')){{$model->object['nottification']}}@endif
                </textarea>
              </div>

      <button type="submit" class="btn btn-success  btn-block">
        <i class="far fa-paper-plane"></i> გაგზავნა
      </button>
  </div>

</div>
{!! Form::close() !!}
  </section>
@endsection

@push('scripts')
<script>

let route = @json(route('settings.learning'));

let isEducationStart = document.querySelector('#isEducationStart');
let isEducationEnd = document.querySelector('#isEducationEnd');


isEducationStart.addEventListener('change', (event) => {
  if (event.target.checked) isEducationEnd.checked = false
  if (event.target.checked) document.querySelector('label[for="isEducationStart"]').innerHTML = 'დაწყებული'
  else document.querySelector('label[for="isEducationStart"]').innerHTML = 'გამორთული'
  document.querySelector('label[for="isEducationEnd"]').innerHTML = 'გამორთული'
});

isEducationEnd.addEventListener('change', (event) => {
  if (event.target.checked) isEducationStart.checked = false
  if (event.target.checked) document.querySelector('label[for="isEducationEnd"]').innerHTML = 'დასრულებული'
  else document.querySelector('label[for="isEducationEnd"]').innerHTML = 'დასრულებული'
  document.querySelector('label[for="isEducationStart"]').innerHTML = 'გამორთული'
})


  // fetch(route, {
  //   method: 'POST', // or 'PUT'
  //   headers: {
  //     'Content-Type': 'application/json',
  //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  //   },
  //   body: JSON.stringify({ checked: event.target.checked }),
  // })
  // .then(response => response.json())
  // .then(data => {
  //   console.log('Success:', data);
  // })
  // .catch((error) => {
  //   console.error('Error:', error);
  // })

</script>
@endpush











