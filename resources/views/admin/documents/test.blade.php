@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Настройки</h1>
@stop

@section('content')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js" ></script>
    <div class="container-fluid">
        @csrf
        <span class="contentHiden" style="display:none"> {!! $data !!}</span>
        <iframe id="summernote-frame" style="width: 100%; height: 700px; border: 1px solid #ccc;"></iframe>
        <button type="submit" class="btn btn-success saveHtml">Сохранить</button>
        <button type="submit" class="btn btn-success downloadHtml">Скачать pdf</button>
    </div>
@stop
@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
