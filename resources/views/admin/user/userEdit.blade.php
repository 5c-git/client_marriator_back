@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>{{ $user->email }}</h1>
@stop

@section('content')

    @php
        if($user->roles()->first()){
            if($user->roles()->first()->id == 1){
                $checked = 'checked';
            }else{
                $checked = '';
            }
        }else{
            $checked = '';
        }
        if($user->pin_active){
            $pinChecked = 'checked';
        }else{
            $pinChecked = '';
        }
    @endphp
    <br>
    <h4>User ID - {{ $user->id }}</h4>
    <h4>User permission - {{ $user->roles()->first()->name }}</h4>
    <form class="userEdit">
        @csrf
        <input type="hidden" name="id" value="{{ $user->id }}">
        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" value="{{ $user->name }}" placeholder="{{ $user->name }}">
            </div>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="tel" class="form-control" name="email" id="email" required value="{{ $user->email }}" placeholder="{{ $user->email }}">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPassword" name="password" placeholder="Password">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputPasswordConfirm" class="col-sm-2 col-form-label">Confirm Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPasswordConfirm" name="confirmPassword" placeholder="Confirm Password">
            </div>
        </div>
        <div class="form-group row">
                <label class="col-form-label col-sm-2 pt-0">Admin</label>
                <div class="col-sm-10">
                    <div class="form-check">
                        <input class="form-check-input" name="permission" type="checkbox" id="gridCheck1" {{$checked}}>
                        <label class="form-check-label" for="gridCheck1">
                           Yes
                        </label>
                    </div>

                </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Edit</button>
                <a href="{{route('usersList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Return</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop

