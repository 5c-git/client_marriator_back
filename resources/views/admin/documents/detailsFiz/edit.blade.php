@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>{{$edit->type->getName()}}  {{$edit->id}}</h1>
        </div>
    </div>
@stop

@section('content')


    <form class="status formCustomSubmit" action="{{route('documentsEditAjax')}}">
        @csrf
        <input type="hidden" name="id" value="{{$edit->id}}">


        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" value="{{$edit->name}}" required>
            </div>
        </div>

        <div class="form-group row" style="display: none">
            <label for="type" class="col-sm-2 col-form-label">Тип</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="type" id="type" value="{{$edit->type->value}}" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="date_start" class="col-sm-2 col-form-label">Дата начала</label>
            <div class="col-sm-10">
                <input type="date" class="form-control" name="date_start" id="date_start" value="{{$edit->date_start->format('Y-m-d')}}" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="date_end" class="col-sm-2 col-form-label">Дата конца</label>
            <div class="col-sm-10">
                <input type="date" class="form-control" name="date_end" id="date_end" value="{{$edit->date_end->format('Y-m-d')}}" required>
            </div>
        </div>

        <div class="form-group row" style="display: none">
            <label for="version" class="col-sm-2 col-form-label">Шаблон</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="template" id="template" value="{{$edit->template}}" required>
            </div>
        </div>

        <script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"
        ></script>
        <script src="{{ asset('vendor/tinymce/tinymce/tinymce.min.js') }}" referrerpolicy="origin" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function () {

                if($('#tiny').length) {
                    tinymce.init({
                        selector: 'textarea#tiny',
                        height: 700,
                        license_key: 'gpl',
                        menubar: false,
                        plugins: [
                            "advlist", "anchor", "autolink", "charmap", "code", "fullscreen",
                            "help", "image", "insertdatetime", "link", "lists", "media",
                            "preview", "searchreplace", "table", "visualblocks", "importword",
                        ],
                        toolbar: "undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | importword | table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol",
                    });
                }
                $('.saveHtml').click(function (e) {
                    e.preventDefault();

                    var myContent = tinymce.get('tiny').getContent();
                    var token = $('input[name="_token"]').val();

                    $.ajax({
                        url: '/admin/documents/save',
                        type: "POST",
                        data: {data: myContent, _token: token},
                        success: function (data) {
                            $(document).Toasts('create', {
                                title: 'Success',
                                body: 'Успешное сохранение',
                                autohide: true,
                                delay: 2000,
                                class: 'bg-success',
                            })
                        }
                    });
                });

                $('.formCustomSubmit').submit(function (e) {
                    $('#content').html(tinymce.get('tiny').getContent());
                })

                $('.downloadHtml').click(function (e) {
                    e.preventDefault();

                    var myContent = tinymce.get('tiny').getContent();
                    var token = $('input[name="_token"]').val();

                    $.ajax({
                        url: '/admin/documents/download',
                        type: "POST",
                        data: {data: myContent, _token: token},
                        xhrFields: {
                            responseType: 'blob' // Важно: указываем что ожидаем бинарные данные
                        },
                        success: function (data, status, xhr) {
                            // Получаем имя файла из заголовков
                            const contentDisposition = xhr.getResponseHeader('content-disposition');
                            let filename = 'document.pdf';

                            if (contentDisposition) {
                                const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                                if (filenameMatch) {
                                    filename = filenameMatch[1];
                                }
                            }

                            // Создаем blob из полученных данных
                            const blob = new Blob([data], { type: 'application/pdf' });
                            const url = window.URL.createObjectURL(blob);

                            // Создаем временную ссылку для скачивания
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                            // Освобождаем память
                            window.URL.revokeObjectURL(url);
                        },
                        error: function (xhr, status, error) {
                            $(document).Toasts('create', {
                                title: 'Error',
                                body: 'Не успешное формирование документа',
                                autohide: true,
                                delay: 4000,
                                class: 'bg-danger',
                            })
                        }
                    });
                });
            });
        </script>

        <div>
            <textarea id="tiny">{!! $data !!}</textarea>
            <textarea name="content" id="content" style="display: none"></textarea>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                </br>
                <button type="submit" class="btn btn-success downloadHtml">Скачать pdf</button>
                </br>
                </br>
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('documentsList',$edit->type->name)}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>

    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

