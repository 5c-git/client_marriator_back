@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Создание нового поля</h1>
        </div>
    </div>
@stop

@section('content')

    <style>
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #ffffff !important;
            color: #495057 !important;
        }
        .select2-results__option[aria-selected="true"] {
            color: #212529 !important;
            background-color: #c8c8c8 !important;
        }
    </style>
    <form class="status formCustomSubmit" action="{{route('fieldsCreateAjax')}}">
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
                <input type="text" class="form-control" name="uuid" id="uuid">
            </div>
        </div>

        <div class="form-group row">
            <label for="description" class="col-sm-2 col-form-label">Описание</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="description" id="description"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Тип поля</label>
            <div class="col-sm-10">
                <select class="custom-select" name="type" required>
                    @foreach($typeEnum as $type)
                        <option value="{{$type->value}}">{{$type->typeName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label for="step" class="col-sm-2 col-form-label">Этап</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="step" id="step">
            </div>
        </div>

        <div class="form-group row">
            <label for="sort" class="col-sm-2 col-form-label">Сортировка</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="sort" id="sort">
            </div>
        </div>

        <div class="form-group row">
            <label for="label" class="col-sm-2 col-form-label">Ярлык</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="label" id="label"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="heading" class="col-sm-2 col-form-label">Заголовок</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="heading" id="heading"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="placeholder" class="col-sm-2 col-form-label">Текст в поле</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="placeholder" id="placeholder"></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="dividerTop" class="col-sm-2 col-form-label">Линия сверху</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="dividerTop" value="1" id="dividerTop">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="dividerBottom" class="col-sm-2 col-form-label">Линия снизу</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="dividerBottom" value="1" id="dividerBottom">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="required" class="col-sm-2 col-form-label">Обязательное поле</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="required" value="1" id="required">
                </div>
            </div>
        </div>


        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Справочник</label>
            <div class="col-sm-10">
                <select class="custom-select" name="directory">
                    <option value="">Не выбрано</option>
                    @foreach($directoryEnum as $directory)
                        <option value="{{$directory->value}}">{{$directory->directoryName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка полей</label>
            <div class="col-sm-10">
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                    <x-adminlte-select2 id="parentFields" name="parentFields[0][]" multiple>
                        @foreach($fields as $field)
                            @foreach($field['value'] as $fieldVal)
                            @if(!empty($fieldVal['uuid']))
                                <option value="{{$fieldVal['uuid']}}">{{$field['name']}}: {{$fieldVal['name']}} [{{$fieldVal['uuid']}}]</option>
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
            <label for="active" class="col-sm-2 col-form-label">Активность</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="active" value="1" id="active">
                </div>
            </div>
        </div>



        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('fieldsList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active"
                   role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

