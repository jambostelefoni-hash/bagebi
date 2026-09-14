<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Model\API\Kindergartener;
use DB;

class Kindergarten extends Model
{
    protected $fillable = [
        'name',
        'municipality_id',
    ];

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('m/d/y');
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id', 'id');
    }

    public function groupAgeRanges()
    {
        return $this->belongsToMany(
            GroupAgeRange::class,
            'kindergarten_group_age_range',
            'kindergarten_id',
            'group_age_range'
        )
        ->withPivot(['space_length', 'space_filled', 'space_free', 'space_reserved']);
    }

    public function currentAge($rangeId)
    {
        $groupAgeRange = $this->groupAgeRanges()
            ->wherePivot('group_age_range', $rangeId)
            ->first();

        if ($groupAgeRange) {
            $groupAgeRange->setRelation(
                'byId',
                $this->KindergartenersByGroupId($rangeId)
            );
        }

        return $groupAgeRange;
    }

    /**
     * ითვლის აქტიურ ბავშვებს კონკრეტულ ბაღსა და ჯგუფში.
     */
    public function activeChildrenCount($groupId)
{
    return Kindergartener::where('kindergarten_id', $this->id)
        ->where('group_id', $groupId)
        ->whereIn('application_status', ['enrolled', 'suspended'])
        ->count();
}

    public function Kindergarteners()
    {
        return $this->hasMany(Kindergartener::class)->where('application_status', 'enrolled');
    }

    public function occupiedChildren()
    {
        return $this->hasMany(Kindergartener::class)->whereIn('application_status', ['enrolled', 'suspended']);
    }

    public function KindergartenersList()
    {
        return $this->Kindergarteners()
            ->join(
                'group_age_ranges',
                'kindergarteners.group_id',
                '=',
                'group_age_ranges.id'
            )
            ->select(
                'kindergarten_id',
                'group_id',
                DB::raw('count(*) as total'),
                'group_age_ranges.range'
            )
            ->groupBy('group_id', 'kindergarten_id');
    }

    public function KindergartenersByGroupId($rangeId)
    {
        return $this->KindergartenersList()
            ->where('group_id', $rangeId)
            ->first();
    }
}
