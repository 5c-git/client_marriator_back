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
                    <div class="addItemBlock">
                    @foreach($settings as $k=>$setting)
                    <div class="row form-group addItem">
                        <label for="error" class="col-sm-2 col-form-label">Настройки</label>
                        <div class="col-sm-5">
                            <div class="search-block">
                                <textarea class="form-control" type="text" name="settings[key][]" placeholder="Ключ">{{$setting->key}}</textarea>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <textarea type="text" class="form-control" name="settings[value][]" placeholder="Значение">{{$setting->value}}</textarea>
                        </div>
                    </div>
                    @endforeach
                    <div class="row form-group addItem">
                        <label for="error" class="col-sm-2 col-form-label">Настройки</label>

                        <div class="col-sm-5">
                            <div class="search-block">
                                <textarea class="form-control" type="text" name="settings[key][]" placeholder="Ключ"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <textarea type="text" class="form-control" name="settings[value][]" placeholder="Значение"></textarea>
                        </div>
                    </div>
                    </br>
                    <button class="btn btn-primary addItemButton">Добавить </button>
                    </div>
                    <br>
                    <br>
                    <button type="submit" class="btn btn-success">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
