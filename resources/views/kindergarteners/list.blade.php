@extends('layouts.app')

@section('content')

<div class="content-header kids-page-header">
  <div class="container-fluid">
    <div class="kids-page-heading">
      <div><span class="dashboard-eyebrow">აღსაზრდელთა მართვა</span><h1 class="m-0">აღსაზრდელები</h1><p>მოძებნეთ, მართეთ სტატუსები და ნახეთ თითოეული ბავშვის მონაცემი.</p></div>
      <a href="{{ route('kindergarteners.show') }}" class="btn btn-success kids-add-button"><i class="fas fa-plus"></i> აღსაზრდელის დამატება</a>
    </div>
  </div>
</div>

<section class="content kids-page">
  <div class="card kids-list-card">
    <div class="card-header">
      <div><h3 class="card-title">აღსაზრდელების ჩამონათვალი</h3><span class="kids-list-caption">სულ {{ count($model) }} ჩანაწერი</span></div>
      <a href="{{ route('kindergarteners.export') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-excel"></i> Excel</a>
    </div>

    <div class="card-body table-responsive p-2">
      @if(auth()->user()->isUnionAdmin())
      {!! Form::model($model, ['route' => 'kindergarteners.order']) !!}
      <div style="display: none;" id="checkbox-section"></div>
      <div class="row kids-toolbar">

        <div class="col-lg-5">
          <div class="form-group">
            <div class="input-group kids-search">
              <span class="input-group-text"><i class="fas fa-search"></i></span><input id="searchable" type="text" class="form-control" placeholder="ძებნა სახელით, ბაღით ან სტატუსით" >
            </div>
          </div>
        </div>

        <div class="col-lg-3">
          <select name="action" id="cars-select" class="custom-select" onchange="updateModels()">
            <option value="" selected>აირჩიეთ მოქმედება</option>
          </select> 
        </div>

        <div class="col-lg-2">
          <select name="destination" id="models-select" class="custom-select">
            <option value="" selected>შედეგი</option>
          </select>
        </div>

        <div class="col-lg-2"><button type="submit" class="btn btn-block btn-primary">შესრულება</button></div>
      </div>
      {!! Form::close() !!}   
      @endif
      @if(!auth()->user()->isUnionAdmin())<div style="display:none" id="checkbox-section"></div>@endif

      <table class="table table-hover text-nowrap" id="table" ></table>
    </div>
  </div>
</section>
@endsection

@push('scripts')

<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/sl-1.2.5/datatables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap4.js"></script>
<script type="text/javascript" src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/js/dataTables.checkboxes.js"></script>

<script>

var carsSelect = document.getElementById('cars-select');
var modelsSelect = document.getElementById('models-select');

function createCar(name, id) { return { name: name,id: id } }
function createModel(name, id, car) { return { name: name, id: id, car: car } }

function removeOptions(select) {
  while (select.options.length > 1) { select.remove(1); }
  select.value = "";
}
function addOptions(select, options) {
  options.forEach(function(option) {
    select.options.add(new Option(option.name, option.id));
  });
}
var cars = [
  createCar('პრიორიტეტის', '1'),
  createCar('სტატუსის შეცვლა', '2')
];

var models = [
  createModel('დასტურის გაუქმება', '0', '1'),
  createModel('დადასტურება', '1', '1'),
  createModel('დარეგისტრირებული', 'registered', '2'),
  createModel('მომლოდინე', 'waiting', '2'),
  createModel('ჩარიცხული', 'enrolled', '2'),
  createModel('შეჩერებული', 'suspended', '2'),
  createModel('გაუქმებული', 'cancelled', '2')
];

function updateModels() {
  var selectedCar = carsSelect.value;
  var options = models.filter(function(model) {
    return model.car === selectedCar;
  });
  removeOptions(modelsSelect);
  addOptions(modelsSelect, options);
}

if (carsSelect) addOptions(carsSelect, cars);

var app = @json($model);
var applicationStatusLabels = @json(config('statuses.application'));

const datatable = $('#table').DataTable({
  "ordering": true,
  "info": false,
  "autoWidth": false,
  "responsive": true,
  "lengthChange": false,
  fixedColumns: true,
  data: app,
  "order": [[ 0, "desc" ]],
  columnDefs: [
    { targets: 0, visible: false },
    { title: 'მუნიც...', targets: 1 },
    { title: 'ბაღი', targets: 2 },
    { title: 'ასაკი', targets: 3 },
    { title: 'პრიორიტეტი', targets: 4 },
    { title: 'სტატუსი', targets: 5 },
    { title: 'ბავშვის N:', targets: 6 },
    { title: 'ბავშვი', targets: 7 },
    { title: 'დაბადების თარიღი', targets: 8 },
    { title: 'თარიღი', targets: 9 },
    {
      'targets': 10,
      'checkboxes': {
        'selectRow': true,
        stateSave: false
      }
    },
    { title: 'მოქმედება', targets: 11 }
  ],
  'select': {
    'style': 'multi',
    selector: 'td.dt-checkboxes-cell'
  },
  createdRow: function (row, data, index) {
    if (data.application_status === 'cancelled' || data.application_status === 'suspended') { row.style.backgroundColor = '#faf6f4'; }
  },
  columns: [
    { data: 'id' },
    { render: (d,t,row) => row.municipality?.name ?? '---' },
    { data: 'kindergarten.name' },
    { render: (d,t,row) => row.group_range ? row.group_range.range : '---' },
    { render: (d,t,row) => row.priority 
        ? `<span class="badge badge-${row.priority.has_permission ? 'success' : 'danger'}">
             ${row.priority.has_permission ? 'დადასტურებული <i class="icon fas fa-check"></i>' : 'დაუდასტურებელი' }
           </span>`
        : '<span class="badge badge-primary">არ სარგებლობს</span>'
    },
    { render: (d,t,row) => applicationStatusLabels[row.application_status] ?? 'უცნობი სტატუსი' },
    { data: 'kids_personal_number' },
    { render: (d,t,row) => `${row.kids_first_name} ${row.kids_last_name}` },
    { render: (d,t,row) => row.birth_date ? row.birth_date : '---' },
    { data: 'created_at', render: function (value, type) {
        if (type !== 'display') return value;
        const parts = String(value || '').match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}:\d{2})/);
        return parts ? `<span class="date-stack"><span>${parts[3]}.${parts[2]}.${parts[1]}</span><small>${parts[4]}</small></span>` : '—';
      }
    },
    { data: 'id', className: 'text-left' },
    {
      data: null,
      className: "dt-center editor-edit",
      render: function ( data, type, row ) {
        const route = @json(route('kindergarteners.show'));
        const routeDelate = @json(route('kindergarteners.destroy', ['id' => '__ID__']));
        const canDelete = @json(auth()->user()->isUnionAdmin());
        return `${!row.graduate ? `<i style="cursor:pointer; margin-right:17px; color:black;" class="fas fa-edit" 
          onclick='letsRedirect(event, "${route}", ${row.id})'></i>` : ''}
                ${canDelete ? `<i style="cursor:pointer; color:black;" class="fas fa-trash" onclick='nottify(event)' data-href="${routeDelate.replace('__ID__', row.id)}"></i>` : ''}`
      },
      orderable: false
    }
  ]
});

let checkboxDiv = document.querySelector("#checkbox-section");

function createCheckbox (value) {
  let checkbox = document.createElement('input');
  checkbox.type = "checkbox";
  checkbox.name = "ids[]";
  checkbox.value = value;
  checkbox.checked = true;
  checkboxDiv.appendChild(checkbox);
}

$(document).on("change", "input[type='checkbox']", function() {
  var rows_selected = datatable.column(10).checkboxes.selected();
  checkboxDiv.innerHTML = "";
  rows_selected.map(function(value) {
    createCheckbox(value);
  })
})

function letsRedirect (event, link, id) {
  event.preventDefault()
  document.querySelectorAll("tr").forEach((elm) => { elm.classList.remove('selected') })
  document.querySelectorAll("input").forEach((elm) => { elm.checked = false })
  return location.href = link + '/' + id;
}

$('#searchable').keyup(function () {
  datatable.search($(this).val()).draw();
});

$('.dataTables_filter').css('display', 'none');

</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.1/css/dataTables.bootstrap4.css">
<link rel="stylesheet" href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/css/dataTables.checkboxes.css">

<style>
  table.dataTable tbody>tr.selected, table.dataTable tbody>tr>.selected { background-color: #B0BED9; }
  .kids-page-header{padding-bottom:18px!important}.kids-page-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:18px}.kids-page-heading h1{margin-top:4px!important}.kids-page-heading p{margin:7px 0 0;color:var(--panel-muted);font-size:.87rem}.kids-add-button{white-space:nowrap}.kids-list-card .card-header{display:flex;align-items:center;justify-content:space-between;gap:14px}.kids-list-caption{display:block;margin-top:5px;color:var(--panel-muted);font-size:.76rem;font-weight:700}.kids-toolbar{align-items:center;margin-bottom:6px}.kids-toolbar .form-group{margin:0}.kids-search .input-group-text{border:1px solid #d8e1ec;border-right:0;border-radius:11px 0 0 11px;background:#fff;color:var(--panel-teal)}.kids-search .form-control{border-left:0!important;border-radius:0 11px 11px 0!important}.kids-list-card .dataTables_wrapper{padding-top:8px}.kids-list-card .badge{padding:6px 9px;border-radius:99px;font-size:.7rem}.kids-list-card .fa-edit,.kids-list-card .fa-trash{width:30px;height:30px;display:inline-grid;place-items:center;border-radius:8px;background:#eef5fb!important;color:var(--panel-navy)!important;margin-right:5px!important}.kids-list-card .fa-trash{background:#fff0ed!important;color:#cb5a47!important}@media(max-width:767px){.kids-page-heading{align-items:flex-start;flex-direction:column}.kids-add-button{width:100%}.kids-toolbar>div{margin-bottom:10px}.kids-list-card .card-header{align-items:flex-start;flex-direction:column}.kids-list-card .card-header .btn{width:100%}}
</style>
@endpush
