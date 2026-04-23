@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        <a class="nav-link" href="/horizon/">
            <i class="fas fa-fw fa-window-restore"></i>
            <p style="display: contents">Laravel Horizon</p>
        </a>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
