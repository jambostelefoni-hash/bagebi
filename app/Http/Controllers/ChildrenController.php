<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\RegistrationText;

class ChildrenController extends Controller
{
    //

    public function index () {
        $defaults = [
            'title' => '',
            'subtitle' => '',
            'description' => '',
            'rules' => 'რეგისტრაციის წესები ჯერ არ არის დამატებული.'
        ];

        $model = RegistrationText::first();
        $registrationText = [
            'title' => $model && $model->title ? $model->title : $defaults['title'],
            'subtitle' => $model && $model->subtitle ? $model->subtitle : $defaults['subtitle'],
            'description' => $model && $model->description ? $model->description : $defaults['description'],
            'rules' => $model && $model->rules ? $model->rules : $defaults['rules']
        ];

        return view('children', [
            'registrationText' => $registrationText
        ]);
    }
}
