@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Типы справок</h1>
@stop

@section('content')

    <div class="container-fluid">
        <form class="certificatesEdit">
            @csrf
            <div class="form-group row">
                <div class="col-sm-10">
                    <div class="addItemBlock">
                        @foreach($certificates as $k=>$certificate)
                            <div class="row form-group addItem">
                                <label for="error" class="col-sm-2 col-form-label">Тип справки</label>
                                <div class="col-sm-5">
                                    <div class="search-block">
                                        <textarea class="form-control" type="text" name="certificates[key][]" placeholder="Ключ">{{$certificate->key}}</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <textarea type="text" class="form-control" name="certificates[value][]" placeholder="Тип справки">{{$certificate->value}}</textarea>
                                </div>
                            </div>
                        @endforeach
                        <div class="row form-group addItem">
                            <label for="error" class="col-sm-2 col-form-label">Тип справок</label>

                            <div class="col-sm-5">
                                <div class="search-block">
                                    <textarea class="form-control" type="text" name="certificates[key][]" placeholder="Ключ"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <textarea type="text" class="form-control" name="certificates[value][]" placeholder="Тип справки"></textarea>
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
