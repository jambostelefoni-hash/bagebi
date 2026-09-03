<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $model = User::all();
        return view('users.list', ['model' => $model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($request->id)],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        };

        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'მომხმარებლის მონაცემი წარმატებით განახლდა!'
        ];

        $model = User::find($request->id);
        if (!$model) {
            return redirect()->back()->withInput()->withErrors(['id' => 'User not found.']);
        }

        if ($request->filled('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        } else {
            $request->request->remove('password');
        }
        $model->fill($request->all());
        $changes = $this->buildAuditChanges($model);
        if (isset($changes['password'])) {
            unset($changes['password']);
        }
        $model->save();

        $this->logAudit('user.update', User::class, $model->id, 'User updated', $changes);

        return back()->withInput()->withErrors([])->with($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        //
        $model = User::firstOrNew(['id' => $id]);

        return view('users.modify')->withModel($model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if (!isset($id)) return back();

        if ((int) $id === (int) auth()->user()->id) {
            return back()->with([
                'flashType' => 'error',
                'flashMessage' => 'საკუთარი ანგარიშის წაშლა დაუშვებელია.'
            ]);
        }

        $model = User::find($id);
        $details = $model ? ['name' => $model->name, 'email' => $model->email] : null;
        User::destroy($id);
        $this->logAudit('user.delete', User::class, $id, 'User deleted', $details);
        $message = [
          'flashType'    => 'success',
          'flashMessage' => 'მომხმარებელი წაიშალა წარმატებით'
        ];
        return back()->with($message);
    }
}













