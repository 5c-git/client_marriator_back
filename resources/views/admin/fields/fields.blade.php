@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-10">
            <h1>Список полей</h1>
        </div>
        <div class="col-sm-2" style="text-align: end;">
            <a href="{{route('fieldsCreate')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Создать</a>
        </div>
    </div>
@stop

@section('content')
    @php
        $heads = [
        'ID',
        'Name',
        'Uuid',
        'active',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        $fieldData = [];
        foreach ($fields as $field){
        $btnEdit = '<a href="'.route('fieldsEdit',$field->id).'"><button class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                <i class="fa fa-lg fa-fw fa-pen"></i>
            </button></a>';
        $btnDelete = '<a href="'.route('fieldsDelete',$field->id).'"><button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                <i class="fa fa-lg fa-fw fa-trash"></i>
            </button></a>';
        $btnName = '<a href="'.route('fieldsEdit',$field->id).'">'.$field->name.'</a>';

        $fieldData[] = [$field->id , $btnName, $field->uuid,$field->active, '<nobr>'.$btnEdit.$btnDelete.'</nobr>'];
        }

        $config = [
        'data' =>
        $fieldData
        ,
        'order' => [[0, 'desc']],
        'columns' => [null, null,null, null, ['orderable' => false]],
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

