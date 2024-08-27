@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Import directory</h1>
@stop

@section('content')
    <style>
        .loader,
        .loader:before,
        .loader:after {
            background: #65abf8;
            -webkit-animation: load1 1s infinite ease-in-out;
            animation: load1 1s infinite ease-in-out;
            width: 1em;
            height: 4em;
        }
        .loader {
            color: #65abf8;
            text-indent: -9999em;
            margin: 88px auto;
            position: relative;
            font-size: 11px;
            -webkit-transform: translateZ(0);
            -ms-transform: translateZ(0);
            transform: translateZ(0);
            -webkit-animation-delay: -0.16s;
            animation-delay: -0.16s;
        }
        .loader:before,
        .loader:after {
            position: absolute;
            top: 0;
            content: '';
        }
        .loader:before {
            left: -1.5em;
            -webkit-animation-delay: -0.32s;
            animation-delay: -0.32s;
        }
        .loader:after {
            left: 1.5em;
        }
        @-webkit-keyframes load1 {
            0%,
            80%,
            100% {
                box-shadow: 0 0;
                height: 4em;
            }
            40% {
                box-shadow: 0 -2em;
                height: 5em;
            }
        }
        @keyframes load1 {
            0%,
            80%,
            100% {
                box-shadow: 0 0;
                height: 4em;
            }
            40% {
                box-shadow: 0 -2em;
                height: 5em;
            }
        }
    </style>
    <div class="container-fluid">

        <form class="importFormDirectory">
            @csrf
        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Справочник</label>
            <div class="col-sm-10">
                <select class="custom-select" name="importType" required>
                    @foreach($directoryEnum as $directory)
                    <option value="{{$directory->value}}">{{$directory->directoryName()}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Загрузка файла</label>
            <div class="col-sm-10">
                <div class="custom-file">
                    <input type="file" name="importFile" class="custom-file-input" id="customFile" required>
                    <label class="custom-file-label" for="customFile" id="lableForcustomFile">Выберете файл</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Send</button>

        </form>

        <br>
        <br>
        <br>
        <div class="loader loaderForJs" style="display: none">loading</div>
        <table class="table jsDataImport" style="display: none">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">New</th>
                <th scope="col">Old</th>

            </tr>
            </thead>

            <tbody class="jsDataImportBody">

            </tbody>
        </table>

        <form class="importSaveFormDirectory" style="display:none;">
        @csrf
            <input type="hidden" name="link" class="linkJs">
            <input type="hidden" name="type" class="typeJs">
            <button type="submit" class="btn btn-success">Save</button>
        </form>
        <br>
        <br>
        <br>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
