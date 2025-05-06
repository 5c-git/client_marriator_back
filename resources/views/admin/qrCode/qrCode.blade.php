@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Настройки</h1>
@stop

@section('content')

    <div class="container-fluid">
        <form class="qrCodeCreate">
            @csrf
            <div class="form-group row">
                <label for="select" class="col-sm-2 col-form-label">Роль</label>
                <select class="rolesCustomJS custom-select" name="roles[]" required multiple>
                    <option value="">Не выбрано</option>
                    @foreach($roles as $role)
                        <option value="{{$role->id}}">{{$role->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group row">
                <label for="phone" class="col-sm-2 col-form-label">Телефон</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="phone" id="phone" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="phone" class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
            </div>
            <div class="addSelectJs">
{{--                <div class="form-group row" style="display: block">--}}
{{--                    <label for="select" class="col-sm-6 col-form-label">Справочник</label>--}}
{{--                    <select class="rolesCustomJSBindings custom-select" name="" required>--}}

{{--                    </select>--}}
{{--                </div>--}}
{{--                <div class="form-group row" style="display: block">--}}
{{--                    <label for="select" class="col-sm-6 col-form-label">Справочник</label>--}}
{{--                    <select class="rolesCustomJSBindings custom-select" name="" required>--}}

{{--                    </select>--}}
{{--                </div>--}}
            </div>
            <button type="submit" class="btn btn-success">Сохранить</button>
        </form>

        <div class="linkBlockJs" style="display: none">
            <p>Ссылка:</p>
            <span class="linkJs"></span>
        </div>
    </div>
    <br>
    <br>
    <br>

        @php
            $heads = [
                'ID',
                'Телефон',
                'Email',
                'Права',
                ['label' => 'Actions', 'no-export' => true, 'width' => 5],
            ];
            $usersData = [];
            foreach ($users as $user){
                if($user->roles){
                    $permission = $user->roles->pluck('name')->join(', ');
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

               $usersData[] = [$user->id ,$user->phone ,$user->email,  $permission , '<nobr>'.$btnEdit.$btnDelete.'</nobr>'];
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



{{--@section('css')--}}
{{--    <link rel="stylesheet" href="/css/admin_custom.css">--}}
{{--@stop--}}
@stop
@section('js')
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop
