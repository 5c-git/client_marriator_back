@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Контрагент  {{$edit->id}}</h1>
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
    <form class="status formCustomSubmit" action="{{route('counterpartyEditAjax')}}">
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
            <label for="inn" class="col-sm-2 col-form-label">ИНН</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="inn" value="{{$edit->inn}}" id="inn" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="ogrn" class="col-sm-2 col-form-label">ОГРН</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="ogrn" value="{{$edit->ogrn}}" id="ogrn" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="legal_address" class="col-sm-2 col-form-label">Юр.адрес</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="legal_address" value="{{$edit->legal_address}}" id="legal_address" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="legal_email" class="col-sm-2 col-form-label">Почт.адрес</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="legal_email" value="{{$edit->legal_email}}" id="legal_email" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка брендов</label>
            <div class="col-sm-10">
                @if(!empty($edit->brand))
                    @foreach($edit->brand as $kfield=>$brandData)
                        <div class="row addItemSelect">
                            <div class="col-sm-9">
                                <x-adminlte-select2 data-name="brand" id="brand{{$kfield}}" name="brand[{{$kfield}}][]">
                                    @foreach($brand as $brandField)
                                        @if($brandData->id == $brandField->id)
                                            <option selected value="{{$brandData->id}}">{{$brandData->name}}: {{$brandData->name}} [{{$brandData->uuid}}]</option>
                                        @else
                                            <option value="{{$brandField->id}}">{{$brandField->name}}: {{$brandField->name}} [{{$brandField->uuid}}]</option>
                                        @endif
                                    @endforeach
                                </x-adminlte-select2>
                            </div>
                            <div class="col-sm-3">
                                <a class="removeItemButtonSelect btn btn-danger">Удалить элемент</a>
                            </div>
                        </div>
                    @endforeach
                @endif
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="brand" id="parentFields" name="brand[0][]">
                            @foreach($brand as $field)
                                @if(!empty($field['uuid']))
                                    <option value="{{$field['id']}}">{{$field['name']}}: {{$field['name']}} [{{$field['uuid']}}]</option>
                                @endif
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
                <a href="{{route('counterpartyList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

