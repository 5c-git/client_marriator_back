@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Создание вида деятельности из {{$enum->name}}</h1>
        </div>
    </div>
@stop

@section('content')
    <form class="status formCustomSubmit" action="{{route('wfmViewCreateAjax',['wfmType'=>$enum->name])}}">
        @csrf

        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="externalId" class="col-sm-2 col-form-label">Внешний ключ</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="externalId" id="externalId" required>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('heightList',['wfmType'=>$enum->name])}}" style="margin-left: 10px" class="btn btn-secondary btn-md active"
                   role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

