@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Создание Направления деятельности</h1>
        </div>
    </div>
@stop

@section('content')
    <form class="status formCustomSubmit" action="{{route('activitiesCreateAjax')}}">
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
            <label for="img" class="col-sm-2 col-form-label">Картинка</label>
            <div class="col-sm-10">
                <x-adminlte-input-file name="img" id="img" />
            </div>
        </div>

        <div class="form-group row">
            <label for="preview_text" class="col-sm-2 col-form-label">Превью текст</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="preview_text" id="preview_text"></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="detail_name" class="col-sm-2 col-form-label">Детальное название</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="detail_name" id="detail_name">
            </div>
        </div>

        <div class="form-group row">
            <label for="detail_text" class="col-sm-2 col-form-label">Детальный текст</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="detail_text" id="detail_text"></textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="detail_img" class="col-sm-2 col-form-label">Детальная картинка</label>
            <div class="col-sm-10">
                <x-adminlte-input-file name="detail_img" id="detail_img" />
            </div>
        </div>

        <div class="form-group row">
            <label for="link_text" class="col-sm-2 col-form-label">Текст ссылки</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="link_text" id="link_text">
            </div>
        </div>
        <div class="form-group row">
            <label for="link" class="col-sm-2 col-form-label">Ссылка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="link" id="link">
            </div>
        </div>

        <div class="form-group row">
            <label for="type" class="col-sm-2 col-form-label">Тип ссылки</label>
            <div class="col-sm-10">
                <select type="text" class="form-control" name="type" id="type">
                    <option value="external">Внутренняя</option>
                    <option value="internal">Внешняя</option>
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
                <a href="{{route('activitiesList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active"
                   role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

