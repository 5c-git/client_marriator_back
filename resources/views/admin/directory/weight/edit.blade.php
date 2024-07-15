@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Поле  {{$edit->id}}</h1>
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
    <form class="status formCustomSubmit" action="{{route('weightEditAjax')}}">
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
            <label for="active" class="col-sm-2 col-form-label">Активность</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="active" value="1" {{$checkBox}}  id="active">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка полей</label>
            <div class="col-sm-10">
                @foreach($edit->parentFields as $kfield=>$parentField)
                    <div class="row addItemSelect">
                        <div class="col-sm-9">
                            <x-adminlte-select2 id="parentFields{{$kfield}}" name="parentFields[{{$kfield}}][]" multiple>
                                @foreach($fields as $fieldOne)
                                    @foreach($fieldOne['value'] as $fieldVal)
                                        @if(!empty($fieldVal['uuid']))
                                            @if(in_array($fieldVal['uuid'],$parentField))
                                                <option selected value="{{$fieldVal['uuid']}}">{{$fieldOne['name']}}: {{$fieldVal['name']}} [{{$fieldVal['uuid']}}]</option>
                                            @else
                                                <option value="{{$fieldVal['uuid']}}">{{$fieldOne['name']}}: {{$fieldVal['name']}} [{{$fieldVal['uuid']}}]</option>
                                            @endif
                                        @endif
                                    @endforeach
                                @endforeach
                            </x-adminlte-select2>
                        </div>
                        <div class="col-sm-3">
                            <a class="removeItemButtonSelect btn btn-danger">Удалить элемент</a>
                        </div>
                    </div>
                @endforeach
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 id="parentFields{{count($edit->parentFields)+1}}" name="parentFields[{{count($edit->parentFields)+1}}][]" multiple>
                            @foreach($fields as $fieldOne)
                                @foreach($fieldOne['value'] as $fieldVal)
                                    @if(!empty($fieldVal['uuid']))
                                        <option value="{{$fieldVal['uuid']}}">{{$fieldOne['name']}}: {{$fieldVal['name']}} [{{$fieldVal['uuid']}}]</option>
                                    @endif
                                @endforeach
                            @endforeach
                        </x-adminlte-select2>
                    </div>
                    <div class="col-sm-3">
                        <a class="removeItemButtonSelect btn btn-danger">Удалить элемент</a>
                    </div>
                </div>
                <button class="btn btn-primary addItemButtonSelect">Добавить привязку</button>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('weightList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

