<?php

namespace App\Http\Controllers;

use App\Model\GroupAgeRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GroupAgeRangeController extends Controller
{
    public function index()
    {
        $groups = GroupAgeRange::withCount('kindergartens')->orderBy('id')->get();

        return view('group-age-ranges.list', compact('groups'));
    }

    public function show($id = null)
    {
        return view('group-age-ranges.modify', [
            'model' => GroupAgeRange::firstOrNew(['id' => $id]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:group_age_ranges,id'],
            'range' => ['required', 'string', 'max:45', Rule::unique('group_age_ranges', 'range')->ignore($request->id)],
        ]);

        $group = GroupAgeRange::firstOrNew(['id' => $data['id'] ?? null]);
        $oldName = $group->range;
        $group->range = trim($data['range']);
        $group->save();

        $this->logAudit(
            $group->wasRecentlyCreated ? 'group_age_range.create' : 'group_age_range.update',
            GroupAgeRange::class,
            $group->id,
            'Age group saved',
            ['range' => ['old' => $oldName, 'new' => $group->range]]
        );

        return redirect()->route('group-age-ranges.list')->with([
            'flashType' => 'success',
            'flashMessage' => 'ასაკობრივი ჯგუფი წარმატებით შეინახა.',
        ]);
    }

    public function destroy($id)
    {
        $group = GroupAgeRange::findOrFail($id);
        $isUsed = DB::table('kindergarteners')->where('group_id', $id)->exists()
            || DB::table('kindergarten_group_age_range')->where('group_age_range', $id)->exists()
            || DB::table('waiting_list_entries')->where('group_id', $id)->exists()
            || DB::table('attendances')->where('group_id', $id)->exists();

        if ($isUsed) {
            return back()->withErrors(['group' => 'ჯგუფი გამოიყენება ბაღში ან აღსაზრდელის ჩანაწერში და მისი წაშლა შეუძლებელია. ჯერ მოხსენით შესაბამისი ბაღების ტევადობიდან.']);
        }

        $details = ['range' => $group->range];
        $group->delete();
        $this->logAudit('group_age_range.delete', GroupAgeRange::class, $id, 'Age group deleted', $details);

        return back()->with(['flashType' => 'success', 'flashMessage' => 'ასაკობრივი ჯგუფი წაიშალა.']);
    }
}
