@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Настройки</h1>
@stop

@section('content')

    <div class="container-fluid">
        <form class="settingEdit">
            @csrf
            <div class="form-group row">
                <div class="col-sm-10">



                    <button type="submit" class="btn btn-success">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
@stop

{{--@section('css')--}}
{{--    <link rel="stylesheet" href="/css/admin_custom.css">--}}
{{--@stop--}}

@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
