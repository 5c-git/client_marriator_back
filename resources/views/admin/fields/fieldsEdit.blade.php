@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Поле  {{$field->id}}</h1>
        </div>
    </div>
@stop

@section('content')

    @php
        if($field->active == 1){
           $checkBox = 'checked';
        }else{
           $checkBox = '';
        }
    @endphp
    <form class="status formCustomSubmit" action="{{route('fieldsEditAjax')}}">
        @csrf
        <input type="hidden" name="id" value="{{$field->id}}">


        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" value="{{$field->name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="uuid" class="col-sm-2 col-form-label">Uuid</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="uuid" value="{{$field->uuid}}" id="uuid">
            </div>
        </div>

        <div class="form-group row">
            <label for="description" class="col-sm-2 col-form-label">Описание</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="description" id="description">{{$field->description}}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Тип поля</label>
            <div class="col-sm-10">
                <select class="custom-select" name="type" required>
                    @foreach($typeEnum as $type)
                        <option value="{{$type->value}}" {{$field->type == $type->value?'selected="selected"':''}}>{{$type->typeName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="step" class="col-sm-2 col-form-label">Этап</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="step" id="step" value="{{$field->step}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Справочник</label>
            <div class="col-sm-10">
                <select class="custom-select" name="directory" required>
                    @foreach($directoryEnum as $directory)
                        <option value="{{$directory->value}}" {{$field->directory == $directory->value?'selected="selected"':''}}>{{$directory->directoryName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка полей</label>
            <div class="col-sm-10">
                <x-adminlte-select2 id="parentFields" name="parentFields[]" multiple>
                    @foreach($fields as $fieldOne)
                        @if(!empty($fieldOne->uuid))
                            @if(in_array($fieldOne->uuid,$field->parentFields))
                                <option selected value="{{$fieldOne->uuid}}">{{$fieldOne->name}} [{{$fieldOne->uuid}}]</option>
                            @else
                                <option value="{{$fieldOne->uuid}}">{{$fieldOne->name}} [{{$fieldOne->uuid}}]</option>
                            @endif
                        @endif
                    @endforeach
                </x-adminlte-select2>
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
                <a href="{{route('fieldsList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

