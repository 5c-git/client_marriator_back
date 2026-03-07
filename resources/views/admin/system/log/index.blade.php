@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Логи</h1>
@stop

@section('content')


    <table class="table table-bordered table-hover">
        <tbody>
        @foreach($result as $k=>$res)
        <tr data-widget="expandable-table" aria-expanded="false">
            <td>{{$k}}</td>
        </tr>
        <tr class="expandable-body">
            <td>
                @foreach($res as $n=>$file)
                <p style="margin-bottom: unset">
                    <a href="{{$file}}">{{$n}}</a>
                </p>
                @endforeach
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>


@stop
@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
