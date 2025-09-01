@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-10">
            <h1>Задачи</h1>
        </div>
    </div>
@stop

@section('content')
    @php
        $heads = [
        'ID',
        'Создатель',
        'Ответственный',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $listData = [];
        foreach ($list as $listItem){
        $btnDelete = '<a href="'.route('taskTestDelete',$listItem->id).'"><button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                <i class="fa fa-lg fa-fw fa-trash"></i>
            </button></a>';

        $listData[] = [$listItem->id , $listItem->user_id,$listItem->accept_user_id, '<nobr>'.$btnDelete.'</nobr>'];
        }

        $config = [
        'data' =>
        $listData
        ,
        'order' => [[0, 'desc']],
        'columns' => [null, null, null, ['orderable' => false]],
        ];
    @endphp

    {{-- Minimal example / fill data using the component slot --}}
    <x-adminlte-datatable id="table1" :heads="$heads" bordered :config="$config">
        @foreach($config['data'] as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{!! $cell !!}</td>
                @endforeach
            </tr>
        @endforeach
    </x-adminlte-datatable>

    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

