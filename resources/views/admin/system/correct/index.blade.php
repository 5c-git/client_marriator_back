@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Correct</h1>
@stop

@section('content')

    <form class="status formCustomSubmit" action="{{route('correctCreate')}}">
        @csrf
        <div class="form-group row">
            <label for="file" class="col-sm-2 col-form-label">Документ</label>
            <div class="col-sm-10">
                <x-adminlte-input-file name="file" id="file" />
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Распознать</button>
            </div>
        </div>
    </form>

    @php
        $heads = [
        'ID',
        'Ссылка',
        'Статус',
        'Ответ',
        ];

        $listData = [];
        foreach ($documents as $document){
        $listData[] = [$document->id , $document->link,$document->status->name,$document->unprocessed_data];
        }

        $config = [
        'data' =>
        $listData
        ,
        'order' => [[0, 'desc']],
        'columns' => [null, null,null,null],
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

@stop
@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
