<?php

namespace App\Http\Controllers;

use App\Model\RegistrationText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationTextController extends Controller
{
    public function index()
    {
        $model = RegistrationText::first();
        if (!$model) {
            $model = new RegistrationText($this->defaults());
        }

        return view('registration-texts.index', [
            'model' => $model
        ]);
    }

    public function rules()
    {
        $model = RegistrationText::first();
        if (!$model) {
            $model = new RegistrationText($this->defaults());
        }

        return view('registration-texts.rules', [
            'model' => $model
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000']
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $model = RegistrationText::firstOrNew();
        $model->fill($request->only(['title', 'subtitle', 'description']));
        $changes = $this->buildAuditChanges($model);
        $model->save();

        $this->logAudit('registration_text.update', RegistrationText::class, $model->id, 'Registration text updated', $changes);

        $message = [
            'flashType' => 'success',
            'flashMessage' => 'ტექსტი განახლდა წარმატებით'
        ];

        return back()->withInput()->withErrors([])->with($message);
    }

    public function storeRules(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rules' => ['nullable', 'string', 'max:4000']
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $model = RegistrationText::firstOrNew();
        $model->fill($request->only(['rules']));
        $changes = $this->buildAuditChanges($model);
        $model->save();

        $this->logAudit('registration_text.rules.update', RegistrationText::class, $model->id, 'Registration rules updated', $changes);

        $message = [
            'flashType' => 'success',
            'flashMessage' => 'წესები განახლდა წარმატებით'
        ];

        return back()->withInput()->withErrors([])->with($message);
    }

    private function defaults()
    {
        return [
            'title' => 'ბაღების გაერთიანება',
            'subtitle' => 'ბავშვის რეგისტრაცია',
            'description' => 'შეავსეთ განაცხადი ზუსტად და სრულად. სისტემა ავტომატურად გადამოწმებს აუცილებელ ველებს.',
            'rules' => 'რეგისტრაციის წესები ჯერ არ არის დამატებული.'
        ];
    }
}
