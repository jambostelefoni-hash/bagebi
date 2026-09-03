<?php

namespace App\Exports;

use App\Model\API\Kindergartener;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KindergartenerExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Kindergartener::query()
          ->with('municipality', 'kindergarten', 'groupRange', 'priority', 'activeStatus')->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
          'მუნიციპალიტეტი',
          'ბაღი',
          'ჯგუფი',
          'სტატუსი',
          'პრიორიტეტი',
          'ბავშვის პირადობის N:',
          'ბავშვის სახელი',
          'ბავშვის გვარი',
          'დედის პირადობის N:',
          'დედის სახელი',
          'დედის გვარი',
          'მამის პირადობის N:',
          'მამის სახელი',
          'მამის გვარი',
          'მობილურის ნომერი',
          'ელ-ფოსტა',
          'რეგისტრაციის დრო'
        ];
    }

    public function map($kindergartener): array
    {
        $excelArr = [
            $kindergartener->municipality->name,
            $kindergartener->kindergarten->name,
            $kindergartener->groupRange ? $kindergartener->groupRange->range : 'დამთავრებული',
            $kindergartener->activeStatus->name,
            $kindergartener->priority ? $kindergartener->priority->name : 'არ სარგებლობს',
            $kindergartener->kids_personal_number,
            $kindergartener->kids_first_name,
            $kindergartener->kids_last_name,
            $kindergartener->mother_personal_number,
            $kindergartener->mother_first_name,
            $kindergartener->mother_last_name,
            $kindergartener->father_personal_number,
            $kindergartener->father_first_name,
            $kindergartener->father_last_name,
            $kindergartener->mobile_number,
            $kindergartener->email,
            $kindergartener->created_at
        ];

        return $excelArr;
    }
}





