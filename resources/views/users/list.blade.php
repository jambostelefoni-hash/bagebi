@extends('layouts.app')
@section('content')
<x-ui.page-header eyebrow="წვდომა და უფლებები" title="მომხმარებლები და დირექტორები" description="შექმენით ნებისმიერი რაოდენობის დირექტორი და მიაბით შესაბამის ბაღს.">
    <x-slot name="actions"><x-ui.button variant="primary" :href="route('users.create')"><i class="fas fa-user-plus"></i> ახალი მომხმარებელი</x-ui.button></x-slot>
</x-ui.page-header>
<x-ui.page>
    <div class="summary-strip">
        <x-ui.stat-card label="სულ მომხმარებელი" :value="$model->count()" icon="fas fa-users" />
        <x-ui.stat-card label="დირექტორი" :value="$model->where('role', 'director')->count()" icon="fas fa-user-tie" variant="info" />
        <x-ui.stat-card label="ადმინისტრატორი" :value="$model->where('role', 'union_admin')->count()" icon="fas fa-user-shield" />
    </div>
    <x-ui.card title="წვდომების ჩამონათვალი" description="თითო ბაღზე შესაძლებელია რამდენიმე დირექტორის დანიშვნა" flush>
        <x-slot name="actions"><div class="table-search"><i class="fas fa-search"></i><input id="user-search" type="search" placeholder="მოძებნეთ მომხმარებელი" aria-label="მომხმარებლის ძებნა"></div></x-slot>
        <x-ui.table id="users-table" class="modern-data-table">
            <thead><tr><th>მომხმარებელი</th><th>როლი</th><th>მინიჭებული ბაღი</th><th>შექმნის თარიღი</th><th class="text-right">მოქმედება</th></tr></thead>
            <tbody>@forelse($model as $item)<tr>
                <td><div class="identity-cell"><span>{{ mb_strtoupper(mb_substr($item->name,0,1)) }}</span><div><strong>{{ $item->name }}</strong><small>{{ $item->email }}</small></div></div></td>
                <td><x-ui.badge :variant="$item->role === 'union_admin' ? 'primary' : 'info'"><i class="fas {{ $item->role === 'union_admin' ? 'fa-user-shield' : 'fa-user-tie' }}"></i>{{ $item->role === 'union_admin' ? 'გაერთიანების ადმინისტრატორი' : 'ბაღის დირექტორი' }}</x-ui.badge></td>
                <td>{{ optional($item->kindergarten)->name ?: 'ყველა ბაღი' }}</td><td>{{ optional($item->created_at)->format('d.m.Y') }}</td>
                <td><div class="row-actions"><x-ui.button variant="outline-primary" size="sm" :href="route('users.show', $item->id)" title="რედაქტირება"><i class="fas fa-edit"></i><span>რედაქტირება</span></x-ui.button>@if((int)$item->id !== (int)auth()->id())<x-ui.button variant="outline-danger" size="sm" data-href="{{ route('users.destroy',$item->id) }}" data-confirm-action title="წაშლა"><i class="fas fa-trash-alt"></i><span>წაშლა</span></x-ui.button>@endif</div></td>
            </tr>@empty<tr><td colspan="5"><x-ui.empty-state icon="fas fa-user-friends" title="მომხმარებლები არ მოიძებნა" description="დაამატეთ პირველი დირექტორი ან ადმინისტრატორი." /></td></tr>@endforelse</tbody>
        </x-ui.table>
    </x-ui.card>
</x-ui.page>
@endsection
@push('scripts')<script nonce="{{ $cspNonce }}">document.getElementById('user-search')?.addEventListener('input',function(){const value=this.value.toLocaleLowerCase('ka');document.querySelectorAll('#users-table tbody tr').forEach(row=>row.hidden=!row.textContent.toLocaleLowerCase('ka').includes(value));});</script>@endpush
