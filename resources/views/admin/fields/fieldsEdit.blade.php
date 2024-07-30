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
            <label for="step" class="col-sm-2 col-form-label">Шаг в регистрации</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="step" id="step" value="{{$field->step}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="sort" class="col-sm-2 col-form-label">Сортировка</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="sort" id="sort" value="{{$field->sort}}">
            </div>
        </div>


        <div class="form-group row">
            <label for="label" class="col-sm-2 col-form-label">Ярлык</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="label" id="label">{{$field->label}}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="heading" class="col-sm-2 col-form-label">Заголовок</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="heading" id="heading">{{$field->heading}}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="placeholder" class="col-sm-2 col-form-label">Текст в поле</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="placeholder" id="placeholder">{{$field->placeholder}}</textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="dividerTop" class="col-sm-2 col-form-label">Линия сверху</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="dividerTop" {{$field->dividerTop == 1?'checked':''}} value="1" id="dividerTop">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="dividerBottom" class="col-sm-2 col-form-label">Линия снизу</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="dividerBottom" value="1" {{$field->dividerBottom == 1?'checked':''}} id="dividerBottom">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="helperInfo_text" class="col-sm-2 col-form-label">Вспомогательный блок текст</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="helperInfo_text" id="helperInfo_text">{{$field->helperInfo_text}}</textarea>
            </div>
        </div>
        <div class="form-group row">
            <label for="helperInfo_link_text" class="col-sm-2 col-form-label">Вспомогательный блок текст ссылки</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="helperInfo_link_text" id="helperInfo_link_text" value="{{$field->helperInfo_link_text}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="helperInfo_link" class="col-sm-2 col-form-label">Вспомогательный блок ссылка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="helperInfo_link" id="helperInfo_link" value="{{$field->helperInfo_link}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="helperInfo_link_type" class="col-sm-2 col-form-label">Вспомогательный тип ссылки</label>
            <div class="col-sm-10">
                <select type="text" class="form-control" name="helperInfo_link_type" id="helperInfo_link_type">
                    <option value="external" {{$field->helperInfo_link_type == 'external'?'selected':''}}>Внутренняя</option>
                    <option value="internal" {{$field->helperInfo_link_type == 'external'?'selected':''}}>Внешняя</option>
                </select>
            </div>
        </div>



        <div class="form-group row">
            <label for="drawerInfo_text" class="col-sm-2 col-form-label">Вспомогательный текст (для типа поля Файл)</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="drawerInfo_text" id="drawerInfo_text">{{$field->drawerInfo_text}}</textarea>
            </div>
        </div>
{{--        <div class="form-group row">--}}
{{--            <label for="drawerInfo_images" class="col-sm-2 col-form-label">Вспомогательные картинки (для типа поля Файл)</label>--}}
{{--            <div class="col-sm-10">--}}
{{--                <x-adminlte-input-file name="drawerInfo_images" multiple="multiple" id="drawerInfo_images" />--}}
{{--            </div>--}}
{{--        </div>--}}



        <div class="form-group row">
            <label for="required" class="col-sm-2 col-form-label">Обязательное поле</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="required" value="1" {{$field->required == 1?'checked':''}} id="required">
                </div>
            </div>
        </div>


        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Справочник</label>
            <div class="col-sm-10">
                <select class="custom-select" name="directory">
                    <option value="">Не выбрано</option>
                    @foreach($directoryEnum as $directory)
                        <option value="{{$directory->value}}" {{$field->directory == $directory->value?'selected="selected"':''}}>{{$directory->directoryName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Привязка полей</label>
            <div class="col-sm-10">
                @foreach($field->parentFields as $kfield=>$parentField)
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
                            <x-adminlte-select2 id="parentFields{{count($field->parentFields)+1}}" name="parentFields[{{count($field->parentFields)+1}}][]" multiple>
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
            <label for="active" class="col-sm-2 col-form-label">Активность</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="active" value="1" {{$checkBox}}  id="activeЈ">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Секция в личном кабинете</label>
            <div class="col-sm-10">
                <select class="custom-select" name="section" required>
                    @foreach($sectionEnum as $section)
                        <option value="{{$section->value}}" {{$field->section == $section->value?'selected="selected"':''}}>{{$section->typeName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>


{{--        <div class="form-group row">--}}
{{--            <label for="estate" class="col-sm-2 col-form-label">Поле для вкладки имущество</label>--}}
{{--            <div class="offset-sm-2 col-sm-10">--}}
{{--                <div class="form-check">--}}
{{--                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="estate" value="1" {{$field->estate == 1?'checked':''}} id="estate">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group row">--}}
{{--            <label for="requisites" class="col-sm-2 col-form-label">Поле для вкладки реквизиты</label>--}}
{{--            <div class="offset-sm-2 col-sm-10">--}}
{{--                <div class="form-check">--}}
{{--                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="requisites" value="1" {{$field->requisites == 1?'checked':''}} id="requisites">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

        <div class="form-group row">
            <label for="screen" class="col-sm-2 col-form-label">Вспомогательный скриншот</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="screen" id="screen">{{$field->screen}}</textarea>
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

