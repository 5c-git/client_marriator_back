@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Настройки</h1>
@stop

@section('content')
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/@tinymce/tinymce-jquery@2/dist/tinymce-jquery.min.js"></script>
{{--    <script src="https://cdn.jsdelivr.net/npm/@tinymce/tinymce-jquery@2/dist/tinymce-jquery.min.js"></script>--}}
{{--    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js" ></script>--}}
    <div class="container-fluid">
        @csrf
        <div>
            <textarea id="tiny">{!! $data !!}</textarea>
        </div>
        <br>
        <button type="submit" class="btn btn-success saveHtml">Сохранить</button>
        <button type="submit" class="btn btn-success downloadHtml">Скачать pdf</button>
        <script>
            if($('#tiny').length) {
                $('textarea#tiny').tinymce({
                    height: 700,
                    api_key: 'sl4p43nmar9kjziclksqdn11eft7isc58jxfdgnx4xnhh30v',
                    menubar: false,
                    plugins: [
                        "advlist", "anchor", "autolink", "charmap", "code", "fullscreen",
                        "help", "image", "insertdatetime", "link", "lists", "media",
                        "preview", "searchreplace", "table", "visualblocks",
                    ],
                    toolbar: "undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
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
        </script>
{{--        <span class="contentHiden" style="display:none"> {!! $data !!}</span>--}}
{{--        <iframe id="summernote-frame" style="width: 100%; height: 700px; border: 1px solid #ccc;"></iframe>--}}

    </div>
@stop
@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
