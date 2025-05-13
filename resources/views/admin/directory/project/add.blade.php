@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Создание Проекта</h1>
        </div>
    </div>
@stop

@section('content')
    <form class="status formCustomSubmit" action="{{route('projectCreateAjax')}}">
        @csrf

        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="uuid" class="col-sm-2 col-form-label">Uuid</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" value="{{$uuidDirectoryFields}}" name="uuid" id="uuid" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка Мест проведения</label>
            <div class="col-sm-10">
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="place" id="parentFields" name="place[0][]">
                            <option>Не выбрано</option>
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
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="viewActivities" id="parentFields2" name="viewActivities[0][]">
                            <option>Не выбрано</option>
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
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="counterparty" id="parentFields3" name="counterparty[0][]">
                            <option>Не выбрано</option>
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
            <label for="parentFields4" class="col-sm-2 col-form-label">Привязка Брендов</label>
            <div class="col-sm-10">
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="brands" id="parentFields4" name="brands[0][]">
                            <option>Не выбрано</option>
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
                <a href="{{route('projectList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active"
                   role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

