<?php
namespace App\Exports;
use App\Model\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class AttendanceExport implements FromCollection,WithHeadings,WithMapping
{
    private $gardenId;private $from;private $to;
    public function __construct($gardenId,$from=null,$to=null){$this->gardenId=$gardenId;$this->from=$from;$this->to=$to;}
    public function collection(){return Attendance::with('kindergartener')->where('kindergarten_id',$this->gardenId)->when($this->from,fn($q)=>$q->whereDate('attendance_date','>=',$this->from))->when($this->to,fn($q)=>$q->whereDate('attendance_date','<=',$this->to))->orderBy('attendance_date')->get();}
    public function headings():array{return ['თარიღი','ბავშვი','პირადი ნომერი','სტატუსი'];}
    public function map($row):array{return [$row->attendance_date->format('Y-m-d'),$row->kindergartener->kids_first_name.' '.$row->kindergartener->kids_last_name,$row->kindergartener->kids_personal_number,$row->status_label];}
}
