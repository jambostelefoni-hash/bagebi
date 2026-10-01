@props(['id' => null])
<div class="table-responsive ui-table-wrap"><table @if($id) id="{{ $id }}" @endif {{ $attributes->except('id')->class(['table', 'table-hover', 'ui-table']) }}>{{ $slot }}</table></div>
