@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Поле  {{$bank->id}}</h1>
        </div>
    </div>
@stop

@section('content')

    @php
        if($bank->active == 1){
           $checkBox = 'checked';
        }else{
           $checkBox = '';
        }
    @endphp
    <form class="status formCustomSubmit" action="{{route('bankEditAjax')}}">
        @csrf
        <input type="hidden" name="id" value="{{$bank->id}}">


        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" value="{{$bank->name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="uuid" class="col-sm-2 col-form-label">Uuid</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="uuid" value="{{$bank->uuid}}" id="uuid">
            </div>
        </div>
        <div class="form-group row">
            <label for="bic" class="col-sm-2 col-form-label">Bic</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bic" id="bic" value="{{$bank->bic}}" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="description" class="col-sm-2 col-form-label">Описание</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="description" id="description">{{$bank->description}}</textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="active" class="col-sm-2 col-form-label">Активность</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="active" value="1" {{$checkBox}}  id="activeЈ">
                </div>
            </div>
        </div>



        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('bankList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

