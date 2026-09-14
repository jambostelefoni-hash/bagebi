<?php
namespace App\Services;
use App\Model\WorkCalendarDay;
use Carbon\Carbon;
class WorkCalendarService
{
    public function isWorkingDay(Carbon $date, ?int $gardenId=null): bool
    {
        $override=WorkCalendarDay::whereDate('date',$date)->where(function($q)use($gardenId){$q->where('kindergarten_id',$gardenId)->orWhereNull('kindergarten_id');})->orderByRaw('kindergarten_id IS NULL')->first();
        return $override ? (bool)$override->is_working_day : !$date->isWeekend();
    }
    public function addWorkingDays(Carbon $date,int $days,?int $gardenId=null): Carbon
    {
        $result=$date->copy();$added=0;
        while($added<$days){$result->addDay();if($this->isWorkingDay($result,$gardenId))$added++;}
        return $result;
    }
}
