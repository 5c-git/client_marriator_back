@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-10">
            <h1>Места проведения</h1>
        </div>
        <div class="col-sm-2" style="text-align: end;">
            <a href="{{route('placeCreate')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Создать</a>
        </div>
    </div>
@stop

@section('content')
    @php
        $heads = [
        'ID',
        'Название',
        'Uuid',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $listData = [];
        foreach ($list as $listItem){
        $btnEdit = '<a href="'.route('placeEdit',$listItem->id).'"><button class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                <i class="fa fa-lg fa-fw fa-pen"></i>
            </button></a>';
        $btnDelete = '<a href="'.route('placeDelete',$listItem->id).'"><button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                <i class="fa fa-lg fa-fw fa-trash"></i>
            </button></a>';
        $btnName = '<a href="'.route('placeEdit',$listItem->id).'">'.$listItem->name.'</a>';

        $listData[] = [$listItem->id , $btnName, $listItem->uuid, '<nobr>'.$btnEdit.$btnDelete.'</nobr>'];
        }

        $config = [
        'data' =>
        $listData
        ,
        'order' => [[0, 'desc']],
        'columns' => [null, null,null, ['orderable' => false]],
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

