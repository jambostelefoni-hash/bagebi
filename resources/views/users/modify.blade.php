@extends('layouts.app')
@section('content')
<div class="content-header modern-page-header"><div class="container-fluid"><span class="dashboard-eyebrow">წვდომის რედაქტირება</span><h1>{{ $model->name }}</h1><p>განაახლეთ როლი, ბაღი ან ანგარიშის უსაფრთხოების მონაცემები.</p></div></div>
<section class="content">{!! Form::model($model,['route'=>'users.store']) !!}{!! Form::hidden('id',$model->id) !!}@include('users._form',['model'=>$model,'isEdit'=>true]){!! Form::close() !!}</section>
@endsection
