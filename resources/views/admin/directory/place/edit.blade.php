@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Место проведения  {{$edit->id}}</h1>
        </div>
    </div>
@stop

@section('content')

    @php
        if($edit->active == 1){
           $checkBox = 'checked';
        }else{
           $checkBox = '';
        }
    @endphp
    <form class="status formCustomSubmit" action="{{route('placeEditAjax')}}">
        @csrf
        <input type="hidden" name="id" value="{{$edit->id}}">


        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" value="{{$edit->name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="uuid" class="col-sm-2 col-form-label">Uuid</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="uuid" value="{{$edit->uuid}}" id="uuid" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="address_kladr" class="col-sm-2 col-form-label">Адрес по КЛАДРУ</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="address_kladr" value="{{$edit->address_kladr}}" id="address_kladr" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="latitude" class="col-sm-2 col-form-label">Координаты Широта</label>
            <div class="col-sm-10">
                <input type="number" min="-90" max="90" class="form-control" name="latitude" value="{{$edit->latitude}}" id="latitude" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="longitude" class="col-sm-2 col-form-label">Координаты Долгота</label>
            <div class="col-sm-10">
                <input type="number" min="-90" max="90" class="form-control" name="longitude" value="{{$edit->longitude}}" id="longitude" required>
            </div>
        </div>



        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('placeList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

