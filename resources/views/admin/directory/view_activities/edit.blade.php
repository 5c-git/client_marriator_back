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
    <form class="status formCustomSubmit" action="{{route('view_activitiesEditAjax')}}">
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
            <label for="external_id_timeBook" class="col-sm-2 col-form-label">Внешний индификатор timeBook</label>
            <div class="col-sm-9">
                <x-adminlte-select2 data-name="external_id_timeBook" id="external_id_timeBook"
                                    name="external_id_timeBook">
                    @foreach($wfm as $wfmOne)
                        @if($wfmOne->type->value == \App\Enum\Wfm\WfmTypeEnum::timeBook->value)
                            @if($edit->external_id_timeBook == $wfmOne->externalId)
                                <option selected value="{{$wfmOne->externalId}}">{{$wfmOne->name}}</option>
                            @else
                                <option value="{{$wfmOne->externalId}}">{{$wfmOne->name}}</option>
                            @endif
                        @endif
                    @endforeach
                </x-adminlte-select2>
            </div>
        </div>
        <div class="form-group row">
            <label for="external_id_x5" class="col-sm-2 col-form-label">Внешний индификатор x5</label>
            <div class="col-sm-9">
                <x-adminlte-select2 data-name="external_id_x5" id="external_id_x5" name="external_id_x5">
                    @foreach($wfm as $wfmOne)
                        @if($wfmOne->type->value == \App\Enum\Wfm\WfmTypeEnum::x5->value)
                            @if($edit->external_id_x5 == $wfmOne->externalId)
                                <option selected value="{{$wfmOne->externalId}}">{{$wfmOne->name}}</option>
                            @else
                                <option value="{{$wfmOne->externalId}}">{{$wfmOne->name}}</option>
                            @endif
                        @endif
                    @endforeach
                </x-adminlte-select2>
            </div>
        </div>
        <div class="form-group row">
            <label for="external_id_verme" class="col-sm-2 col-form-label">Внешний индификатор verme</label>
            <div class="col-sm-9">
                <x-adminlte-select2 data-name="external_id_verme" id="external_id_verme" name="external_id_verme">
                    @foreach($wfm as $wfmOne)
                        @if($wfmOne->type->value == \App\Enum\Wfm\WfmTypeEnum::verme->value)
                            @if($edit->external_id_verme == $wfmOne->externalId)
                                <option selected value="{{$wfmOne->externalId}}">{{$wfmOne->name}}</option>
                            @else
                                <option value="{{$wfmOne->externalId}}">{{$wfmOne->name}}</option>
                            @endif
                        @endif
                    @endforeach
                </x-adminlte-select2>
            </div>
        </div>

        <div class="form-group row">
            <label for="img" class="col-sm-2 col-form-label">Image</label>
            <div class="col-sm-10">
                <x-adminlte-input-file name="img" id="img" />

                @php
                    if(!empty($edit->img)){
                       echo '<img src="'.$edit->img.'" width="300">
                       <br>
                       <br>
                       <a href="'.$edit->img.'" target="_blank">Image element</a>
                        <br>
                        <label style="margin-top: 5px;" for="delImg">Del image</label>
                       <input type="checkbox" id="delImg" name="delImg" value="yes"/>
                       ';
                    }
                @endphp
            </div>
        </div>

        <div class="form-group row">
            <label for="preview_text" class="col-sm-2 col-form-label">Превью текст</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="preview_text" id="preview_text">{{$edit->preview_text}}</textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="detail_name" class="col-sm-2 col-form-label">Детальное название</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="detail_name" id="detail_name" value="{{$edit->detail_name}}">
            </div>
        </div>

        <div class="form-group row">
            <label for="detail_text" class="col-sm-2 col-form-label">Детальный текст</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="detail_text" id="detail_text">{{$edit->detail_text}}</textarea>
            </div>
        </div>

        <div class="form-group row">
            <label for="detail_img" class="col-sm-2 col-form-label">Детальная картинка</label>
            <div class="col-sm-10">
                <x-adminlte-input-file name="detail_img" id="detail_img" />

                @php
                    if(!empty($edit->detail_img)){
                       echo '<img src="'.$edit->detail_img.'" width="300">
                       <br>
                       <br>
                       <a href="'.$edit->detail_img.'" target="_blank">Image element</a>
                        <br>
                        <label style="margin-top: 5px;" for="delImg">Del image</label>
                       <input type="checkbox" id="delImgDetail" name="delImgDetail" value="yes"/>
                       ';
                    }
                @endphp
            </div>
        </div>

        <div class="form-group row">
            <label for="link_text" class="col-sm-2 col-form-label">Текст ссылки</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="link_text" id="link_text" value="{{$edit->link_text}}">
            </div>
        </div>
        <div class="form-group row">
            <label for="link" class="col-sm-2 col-form-label">Ссылка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="link" id="link" value="{{$edit->link}}">
            </div>
        </div>

        <div class="form-group row">
            <label for="type" class="col-sm-2 col-form-label">Тип ссылки</label>
            <div class="col-sm-10">
                <select type="text" class="form-control" name="type" id="type">
                    <option value="external" {{$edit->type == 'external'?'selected':''}}>Внутренняя</option>
                    <option value="internal" {{$edit->type == 'internal'?'selected':''}}>Внешняя</option>
                </select>
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
            <label for="self_employed" class="col-sm-2 col-form-label">Для самозанятых</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="self_employed" value="1" {{$edit->self_employed?'checked':''}}  id="self_employed">
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="traveling" class="col-sm-2 col-form-label">Разьездная</label>
            <div class="offset-sm-2 col-sm-10">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" style="margin-top: -1.5rem" name="traveling" value="1" {{$edit->traveling?'checked':''}} id="traveling">
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
            <label for="standard" class="col-sm-2 col-form-label">Норматив учета</label>
            <div class="col-sm-10">
                <div class="row">
                    <div class="col-sm-9">
                        <x-adminlte-select2 id="standard" name="standard" required>
                            @foreach($norm as $field)
                                @if(!empty($field['uuid']))
                                    @if($edit->standard == $field['uuid'])
                                        <option value="{{$field['uuid']}}" selected>{{$field['name']}}</option>
                                    @else
                                        <option value="{{$field['uuid']}}">{{$field['name']}}</option>
                                    @endif
                                @endif
                            @endforeach
                        </x-adminlte-select2>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Похожие виды деятельности</label>
            <div class="col-sm-10">
                    <div class="row">
                        <div class="col-sm-9">
                            <x-adminlte-select2 id="viewActivities" name="viewActivities[]" multiple>
                                @foreach($viewBelong as $viewK=>$viewActivities)
                                    @if(in_array($viewActivities->id,$edit->belongsViewActivities))
                                        <option selected value="{{$viewActivities->id}}">{{$viewActivities->name}} [{{$viewActivities->id}}]</option>
                                    @else
                                        <option value="{{$viewActivities->id}}">{{$viewActivities->name}} [{{$viewActivities->id}}]</option>
                                    @endif
                                @endforeach
                            </x-adminlte-select2>
                        </div>
                    </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('view_activitiesList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

