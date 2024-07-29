@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-10">
            <h1>Пользователи</h1>
        </div>
        <div class="col-sm-2">
            <a href="{{route('usersCreate')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Create</a>
        </div>
    </div>
@stop

@section('content')
    @php
        $heads = [
            'ID',
            'Email',
            'Имя',
            'Права',
            ['label' => 'Actions', 'no-export' => true, 'width' => 5],
        ];

        foreach ($users as $user){
            if($user->roles()->first()){
                $permission = $user->roles()->first()->name;
            }else{
                $permission = '';
            }
            $btnEdit = '<a href="'.route('userEdit',$user->id).'"><button class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i>
                    </button></a>';
        $btnDelete = '<a href="'.route('userDelete',$user->id).'"><button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                          <i class="fa fa-lg fa-fw fa-trash"></i>
                      </button></a>';
if(!empty($user->email_verified_at)){
    $email_verified_at = 'Yes';
}else{
        $email_verified_at = 'No';
}

           $usersData[] = [$user->id ,$user->email ,$user->name,  $permission , '<nobr>'.$btnEdit.$btnDelete.'</nobr>'];
        }

        $config = [
            'data' =>
               $usersData
            ,
            'order' => [[1, 'asc']],
            'columns' => [null,null, null, null, ['orderable' => false]],
        ];
    @endphp

    {{-- Minimal example / fill data using the component slot --}}
    <x-adminlte-datatable id="table1" :heads="$heads" bordered>
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

