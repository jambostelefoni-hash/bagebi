@extends('layouts.app')

@section('title', 'აღსაზრდელები')

@section('content')
<x-ui.page-header eyebrow="აღსაზრდელთა მართვა" title="აღსაზრდელები" description="მოძებნეთ, მართეთ სტატუსები და ნახეთ თითოეული ბავშვის მონაცემი.">
  <x-slot name="actions"><x-ui.button :href="route('kindergarteners.show')"><x-admin-icon name="plus" /> აღსაზრდელის დამატება</x-ui.button></x-slot>
</x-ui.page-header>

<x-ui.page class="kids-page">
  <x-ui.card class="kids-list-card" :flush="true">
    <x-slot name="header">
      <div><h3 class="card-title">აღსაზრდელების ჩამონათვალი</h3><span class="kids-list-caption">სულ {{ $total }} ჩანაწერი</span></div>
    </x-slot>
    <x-slot name="actions"><x-ui.button variant="outline" size="sm" :href="route('kindergarteners.export')"><x-admin-icon name="file-excel" /> Excel</x-ui.button></x-slot>

    <div class="kids-list-body">
      @if(auth()->user()->isUnionAdmin())
      <form method="POST" action="{{ route('kindergarteners.order') }}">@csrf
      <div class="is-hidden" id="checkbox-section"></div>
      <div class="kids-toolbar">
        <div class="kids-filter-field kids-filter-field--search">
          <label for="searchable">ძიება</label>
          <div class="kids-search">
            <span class="kids-search__icon" aria-hidden="true"><x-admin-icon name="search" /></span>
            <input id="searchable" type="search" class="form-control" placeholder="სახელი, ბაღი ან სტატუსი">
          </div>
        </div>

        <div class="kids-filter-field">
          <label for="cars-select">მოქმედება</label>
          <select name="action" id="cars-select" class="custom-select">
            <option value="" selected>აირჩიეთ მოქმედება</option>
          </select>
        </div>

        <div class="kids-filter-field">
          <label for="models-select">შედეგი</label>
          <select name="destination" id="models-select" class="custom-select">
            <option value="" selected>შედეგი</option>
          </select>
        </div>

        <div class="kids-filter-field" id="status-reason-field" hidden>
          <label for="status-reason">ცვლილების მიზეზი</label>
          <input type="text" name="reason" id="status-reason" class="form-control" maxlength="500" placeholder="მიუთითეთ მიზეზი">
        </div>

        <div class="kids-filter-action"><button type="submit" class="btn btn-primary">შესრულება</button></div>
      </div>
      </form>
      @endif
      @if(!auth()->user()->isUnionAdmin())<div class="is-hidden" id="checkbox-section"></div>@endif

      <div class="table-responsive"><table class="table table-hover text-nowrap" id="table"></table></div>
    </div>
  </x-ui.card>
</x-ui.page>
@endsection

@push('scripts')

<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/sl-1.2.5/datatables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.1/js/dataTables.bootstrap4.js"></script>
<script type="text/javascript" src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/js/dataTables.checkboxes.js"></script>

<script nonce="{{ $cspNonce }}">

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
  createModel('პრიორიტეტის დასტურის გაუქმება', '0', '1'),
  createModel('პრიორიტეტის დადასტურება', '1', '1'),
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
  var reasonField = document.getElementById('status-reason-field');
  var reasonInput = document.getElementById('status-reason');
  if (reasonField && reasonInput) {
    reasonField.hidden = selectedCar !== '2';
    reasonInput.required = selectedCar === '2';
    if (selectedCar !== '2') reasonInput.value = '';
  }
}

if (carsSelect) { addOptions(carsSelect, cars); carsSelect.addEventListener('change', updateModels); }

var applicationStatusLabels = @json(config('statuses.application'));
var applicationStatusClasses = {
  registered: 'registered',
  waiting: 'waiting',
  enrolled: 'enrolled',
  suspended: 'suspended',
  cancelled: 'cancelled'
};

const datatable = $('#table').DataTable({
  "processing": true,
  "serverSide": true,
  "ajax": @json(route('kindergarteners.data')),
  "ordering": true,
  "info": false,
  "autoWidth": false,
  "responsive": true,
  "lengthChange": false,
  fixedColumns: true,
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
    { render: (d,t,row) => {
        const status = applicationStatusClasses[row.application_status] ?? 'neutral';
        const label = applicationStatusLabels[row.application_status] ?? 'უცნობი სტატუსი';
        return `<span class="application-status application-status--${status}">${label}</span>`;
      }
    },
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
        return `${!row.graduate ? `<i class="fas fa-edit table-action table-action--edit" data-edit-url="${route}/${row.id}"></i>` : ''}
                ${canDelete ? `<i class="fas fa-trash table-action table-action--delete" data-href="${routeDelate.replace('__ID__', row.id)}"></i>` : ''}`
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

$('#table tbody').on('click', '.table-action--edit', function () { window.location.href = this.dataset.editUrl; });
$('#table tbody').on('click', '.table-action--delete', function (event) { nottify(event); });

</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.1/css/dataTables.bootstrap4.css">
<link rel="stylesheet" href="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/css/dataTables.checkboxes.css">

@endpush
