@extends('layouts.app')

@section('content')

<div class="content-header">
  <div class="container-fluid">
   <div class="row mb-2">
    <div class="col-sm-6">
     <h1 class="m-0">პრიორიტეტი</h1>
    </div><!-- /.col -->
   </div><!-- /.row -->
  </div><!-- /.container-fluid -->
 </div>
 <!-- /.content-header -->

 <!-- Main content -->
 <section class="content">
  <!-- Default box -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">პრიორიტეტების ჩამონათვალი</h3>
      <div class="card-tools">
        <div class="input-group input-group-sm" style="width: 150px;">          
          <button 
            type="submit" onclick="location.href = '{{ route('prioriteties.show') }}'" class="btn btn-sm btn-outline-success">
              <i class="fas fa-shield-alt"></i> დამატება
          </button>          
        </div>
      </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body table-responsive p-0">
      <table style="table-layout:fixed;" class="table table-hover text-wrap">
        <thead>
          <tr style="text-align:center;">
            <th>ID</th>
            <th>პრიორიტეტი</th>
            <th>მოქმედება</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($model as $item)
           <tr>
            <td>{{$item->id}}</td>
            <td>{{$item->name}}</td>
            <td style="text-align:center;" >
                
                
              <button 
                type="button" 
                class="btn btn-sm btn-success" 
                onclick="location.href = '{{route('prioriteties.show', ['id' => $item->id])}}'">
                  <i class="fas fa-shield-alt"></i> რედაქტირება
              </button>
              <button 
                type="button" 
                class="btn btn-sm btn-danger"
                data-href="{{route('prioriteties.destroy', ['id' => $item->id])}}"
                onclick="nottify(event)">
                  <i class="fas fa-shield-alt"></i> წაშლა
              </button>
            </td>
           </tr>
          @endforeach          
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
 <!-- /.card-footer-->
</div>
<!-- /.card -->
</section>
@endsection

@push('scripts')
<script></script>
@endpush

