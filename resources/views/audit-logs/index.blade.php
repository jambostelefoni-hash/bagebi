@extends('layouts.app')

@section('content')
@php
  $actionLabels = [
    'kindergartener.create' => 'ბავშვის დამატება',
    'kindergartener.update' => 'ბავშვის განახლება',
    'kindergartener.delete' => 'ბავშვის წაშლა',
    'kindergartener.bulk_action' => 'ბავშვებზე bulk  მოქმედება',
    'user.update' => 'მომხმარებლის განახლება',
    'user.delete' => 'მომხმარებლის წაშლა',
    'settings.update' => 'პარამეტრების განახლება',
    'settings.date' => 'თარიღის პარამეტრები',
    'settings.learningStart' => 'სწავლის დაწყება',
    'settings.learningEnd' => 'სწავლის დასრულება',
    'settings.learning' => 'სწავლის პროცესის შესრულება',
    'public_page.update' => 'საჯარო გვერდის განახლება',
    'registration_text.update' => 'რეგისტრაციის ტექსტის განახლება'
  ];

  $fieldLabels = [
    'kids_first_name' => 'ბავშვის სახელი',
    'kids_last_name' => 'ბავშვის გვარი',
    'kids_personal_number' => 'ბავშვის პირადი ნომერი',
    'mother_personal_number' => 'დედის პირადი ნომერი',
    'father_personal_number' => 'მამის პირადი ნომერი',
    'mother_first_name' => 'დედის სახელი',
    'mother_last_name' => 'დედის გვარი',
    'father_first_name' => 'მამის სახელი',
    'father_last_name' => 'მამის გვარი',
    'mobile_number' => 'მობილურის ნომერი',
    'email' => 'ელ. ფოსტა',
    'municipality_id' => 'მუნიციპალიტეტი',
    'kindergarten_id' => 'ბაღი',
    'group_id' => 'ჯგუფი',
    'priority_id' => 'პრიორიტეტი',
    'has_permission' => 'დადასტურება',
    'active_status_id' => 'სტატუსი',
    'title' => 'სათაური',
    'subtitle' => 'ქვესათაური',
    'description' => 'აღწერა',
    'isRegistrationStart' => 'რეგისტრაცია',
    'isPrioritetiesStart' => 'პრიორიტეტები',
    'canPorting' => 'პორტირების ნებართვა',
    'isLearningStart' => 'სწავლის სტატუსი',
    'name' => 'სახელი',
    'password' => 'პაროლი',
    'role' => 'როლი'
  ];

  // Model mapping with correct class names
  $modelMap = [
    'municipality_id' => ['class' => 'App\Model\Municipality', 'field' => 'name'],
    'kindergarten_id' => ['class' => 'App\Model\Kindergarten', 'field' => 'name'],
    'group_id' => ['class' => 'App\Model\GroupAgeRange', 'field' => 'range'],
    'priority_id' => ['class' => 'App\Model\KindergartnerPriority', 'field' => 'name'],
    'active_status_id' => ['class' => 'App\Model\ActiveStatus', 'field' => 'name'],
  ];
@endphp

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">აუდიტის ჟურნალი</h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="card">
    <div class="card-body table-responsive p-0">
      <table class="table table-hover text-nowrap">
        <thead>
          <tr>
            <th>თარიღი</th>
            <th>მომხმარებელი</th>
            <th>ქმედება</th>
            <th>ცვლილება</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($logs as $log)
            <tr>
              <td>{{ $log->created_at }}</td>
              <td>{{ $log->user ? $log->user->name : '-' }}</td>
              <td style="min-width: 200px;">
                @php
                  $actionText = $actionLabels[$log->action] ?? $log->action;
                  
                  // Add record identifiers
                  if (strpos($log->action, 'kindergartener') !== false) {
                    if (isset($log->auditable)) {
                      $firstName = $log->auditable->kids_first_name ?? '';
                      $lastName = $log->auditable->kids_last_name ?? '';
                      $personalNumber = $log->auditable->kids_personal_number ?? '';
                      
                      if ($firstName || $lastName) {
                        $actionText .= '<br><span class="badge badge-info" style="font-size: 90%;">ბავშვი: ' . trim($firstName . ' ' . $lastName) . '</span>';
                      }
                      if ($personalNumber) {
                        $actionText .= '<br><span class="badge badge-info" style="font-size: 90%;">პ/ნ: ' . $personalNumber . '</span>';
                      }
                    }
                  }
                  
                  // Add changed fields summary
                  if ($log->changes && (strpos($log->action, 'update') !== false || strpos($log->action, 'bulk_action') !== false)) {
                    $changedFields = [];
                    foreach ($log->changes as $key => $value) {
                      if (!in_array($key, ['kids_first_name', 'kids_last_name', 'kids_personal_number'])) {
                        $changedFields[] = $fieldLabels[$key] ?? $key;
                      }
                    }
                    if (!empty($changedFields)) {
                      $actionText .= '<br><small class="text-muted" style="font-size: 85%;">შეიცვალა: ' . implode(', ', $changedFields) . '</small>';
                    }
                  }
                @endphp
                {!! $actionText !!}
              </td>
              <td style="max-width: 400px;">
                @if ($log->changes)
                  <div class="audit-changes" style="font-size: 90%;">
                    @foreach ($log->changes as $key => $value)
                      @php
                        $label = $fieldLabels[$key] ?? $key;
                        
                        // Function to render value with ID lookup
                        $renderWithLookup = function($val, $fieldKey) use ($modelMap) {
                          if ($val === null || $val === '') return '-';
                          if (is_bool($val)) return $val ? 'დიახ' : 'არა';
                          if (is_array($val)) return json_encode($val, JSON_UNESCAPED_UNICODE);
                          
                          // Lookup for ID fields
                          if (isset($modelMap[$fieldKey]) && is_numeric($val)) {
                            try {
                              $config = $modelMap[$fieldKey];
                              $modelClass = $config['class'];
                              $fieldName = $config['field'];
                              
                              if (class_exists($modelClass)) {
                                $item = $modelClass::find($val);
                                if ($item && isset($item->$fieldName)) {
                                  return $item->$fieldName;
                                }
                              }
                            } catch (\Exception $e) {
                              // Silent fail, return ID
                            }
                            return "ID: $val";
                          }
                          
                          return $val;
                        };
                      @endphp
                      
                      @if (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value))
                        <div style="margin-bottom: 5px; padding: 3px 0; border-bottom: 1px solid #f4f4f4;">
                          <strong>{{ $label }}:</strong><br>
                          <span class="text-danger">{{ $renderWithLookup($value['old'], $key) }}</span> 
                          → 
                          <span class="text-success">{{ $renderWithLookup($value['new'], $key) }}</span>
                        </div>
                      @else
                        <div style="margin-bottom: 5px; padding: 3px 0; border-bottom: 1px solid #f4f4f4;">
                          <strong>{{ $label }}:</strong> 
                          <span class="text-success">{{ $renderWithLookup($value, $key) }}</span>
                        </div>
                      @endif
                    @endforeach
                  </div>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>{{ $log->ip }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">ჩანაწერები ჯერ არ არსებობს</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>

<style>
  .audit-changes {
    max-height: 300px;
    overflow-y: auto;
  }
  .table td {
    vertical-align: top;
  }
</style>
@endsection