@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Проект  {{$edit->id}}</h1>
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
    <form class="status formCustomSubmit" action="{{route('projectEditAjax')}}">
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
            <label for="select" class="col-sm-2 col-form-label">Привязка Мест проведения</label>
            <div class="col-sm-10">
                @if(!empty($edit->place))
                    @foreach($edit->place as $kfield=>$placeData)
                        <div class="row addItemSelect">
                            <div class="col-sm-9">
                                <x-adminlte-select2 data-name="place" id="place{{$kfield}}" name="place[{{$kfield}}][]">
                                    @foreach($place as $placeField)
                                        @if($placeData->id == $placeField->id)
                                            <option selected value="{{$placeData->id}}">{{$placeData->name}}: {{$placeData->name}} [{{$placeData->uuid}}]</option>
                                        @else
                                            <option value="{{$placeField->id}}">{{$placeField->name}}: {{$placeField->name}} [{{$placeField->uuid}}]</option>
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
                        <x-adminlte-select2 data-name="place" id="parentFields" name="place[{{count($edit->place??[])}}][]">
                            <option selected="true" disabled="disabled">Не выбрано</option>
                            @foreach($place as $field)
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
            <label for="select" class="col-sm-2 col-form-label">Привязка Услуг</label>
            <div class="col-sm-10">
                @if(!empty($edit->viewActivities))
                    @foreach($edit->viewActivities as $kfield=>$viewActivitiesData)
                        <div class="row addItemSelect">
                            <div class="col-sm-9">
                                <x-adminlte-select2 data-name="viewActivities" id="viewActivities{{$kfield}}" name="viewActivities[{{$kfield}}][]">
                                    @foreach($viewActivities as $viewActivitiesField)
                                        @if($viewActivitiesData->id == $viewActivitiesField->id)
                                            <option selected value="{{$viewActivitiesData->id}}">{{$viewActivitiesData->name}}: {{$viewActivitiesData->name}} [{{$viewActivitiesData->uuid}}]</option>
                                        @else
                                            <option value="{{$viewActivitiesField->id}}">{{$viewActivitiesField->name}}: {{$viewActivitiesField->name}} [{{$viewActivitiesField->uuid}}]</option>
                                        @endif
                                    @endforeach
                                </x-adminlte-select2>
                            </div>
                            <div class="col-sm-3">
                                <a class="removeItemButtonSelect btn btn-danger">Удалить элемент</a>
                            </div>
                            <div class="form-group row">
                                <label for="price" class="col-sm-3 col-form-label">Цена</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" value="{{$viewActivitiesData->pivot->price}}" name="price[{{$kfield}}]" id="price" required>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="viewActivities" id="parentFields2" name="viewActivities[{{count($edit->viewActivities??[])}}][]">
                            <option selected="true" disabled="disabled">Не выбрано</option>
                            @foreach($viewActivities as $field)
                                @if(!empty($field['uuid']))
                                    <option value="{{$field['id']}}">{{$field['name']}}: {{$field['name']}} [{{$field['uuid']}}]</option>
                                @endif
                            @endforeach
                        </x-adminlte-select2>
                    </div>
                    <div class="col-sm-3">
                        <a class="removeItemButtonSelect btn btn-danger">Удалить элемент</a>
                    </div>
                    <div class="form-group row">
                        <label for="price" class="col-sm-3 col-form-label">Цена</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" value="" name="price[]" id="price" required>
                        </div>
                    </div>

                </div>
                <button class="btn btn-primary addItemButtonSelect">Добавить привязку</button>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка Контрагентов</label>
            <div class="col-sm-10">
                @if(!empty($edit->counterparty))
                    @foreach($edit->counterparty as $kfield=>$counterpartyData)
                        <div class="row addItemSelect">
                            <div class="col-sm-9">
                                <x-adminlte-select2 data-name="counterparty" id="counterparty{{$kfield}}" name="counterparty[{{$kfield}}][]">
                                    @foreach($counterparty as $counterpartyField)
                                        @if($counterpartyData->id == $counterpartyField->id)
                                            <option selected value="{{$counterpartyData->id}}">{{$counterpartyData->name}}: {{$counterpartyData->name}} [{{$counterpartyData->uuid}}]</option>
                                        @else
                                            <option value="{{$counterpartyField->id}}">{{$counterpartyField->name}}: {{$counterpartyField->name}} [{{$counterpartyField->uuid}}]</option>
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
                        <x-adminlte-select2 data-name="counterparty" id="parentFields3" name="counterparty[{{count($edit->counterparty??[])}}][]">
                            <option selected="true" disabled="disabled">Не выбрано</option>
                            @foreach($counterparty as $field)
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
            <label for="select" class="col-sm-2 col-form-label">Привязка Брендов</label>
            <div class="col-sm-10">
                @if(!empty($edit->brands))
                    @foreach($edit->brands as $kfield=>$brandData)
                        <div class="row addItemSelect">
                            <div class="col-sm-9">
                                <x-adminlte-select2 data-name="brands" id="brands{{$kfield}}" name="brands[{{$kfield}}][]">
                                    @foreach($brands as $brandField)
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
                        <x-adminlte-select2 data-name="brands" id="brands" name="brands[{{count($edit->brands??[])}}][]">
                            <option selected="true" disabled="disabled">Не выбрано</option>
                            @foreach($brands as $field)
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
                <a href="{{route('projectList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

