@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Import directory</h1>
@stop

@section('content')
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
        <table class="table jsDataImport" style="display: none">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">New</th>
                <th scope="col">Old</th>

            </tr>
            </thead>

            <tbody class="jsDataImportBody">
            <tr>
                <th scope="row">1</th>
                <td>
                    Uuid - ffff
                    <br>
                    Name - ffff2
                </td>
                <td>
                    Uuid - ffff
                    <br>
                    Name - ffff2
                </td>

            </tr>

            </tbody>
        </table>

        <form class="importSaveFormDirectory" style="display:none;">
        @csrf
            <input type="hidden" name="link" class="linkJs">
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
