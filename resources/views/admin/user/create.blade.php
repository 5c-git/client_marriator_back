@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>New user</h1>
@stop

@section('content')

    <form class="userCreate">
        @csrf
        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name">
            </div>
        </div>
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="tel" class="form-control" name="email" id="email" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
                <input type="password" required class="form-control" id="inputPassword" name="password" placeholder="Password">
            </div>
        </div>
        <div class="form-group row">
            <label for="inputPasswordConfirm" class="col-sm-2 col-form-label">Confirm Password</label>
            <div class="col-sm-10">
                <input type="password" required class="form-control" id="inputPasswordConfirm" name="confirmPassword" placeholder="Confirm Password">
            </div>
        </div>
        <div class="form-group row">
                <label class="col-form-label col-sm-2 pt-0">Admin</label>
                <div class="col-sm-10">
                    <div class="form-check">
                        <input class="form-check-input" name="permission" type="checkbox" id="gridCheck1">
                        <label class="form-check-label" for="gridCheck1">
                            Yes
                        </label>
                    </div>

                </div>
        </div>

        <div class="form-group row">
            <label for="select" class="col-sm-2 col-form-label">Роли пользователей</label>
            <div class="col-sm-10">
                <div class="row addItemSelect">
                    <div class="col-sm-9">
                        <x-adminlte-select2 data-name="roles" id="parentFields" name="roles[]" multiple>
                            @foreach($roles as $field)
                                <option value="{{$field['id']}}">{{$field['name']}}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Create</button>
                <a href="{{route('usersList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Return</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>
@stop

