@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Создание Контрагента</h1>
        </div>
    </div>
@stop

@section('content')
    <form class="status formCustomSubmit" action="{{route('counterpartyCreateAjax')}}">
        @csrf

        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="position" class="col-sm-2 col-form-label">Должность</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="position" id="position" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="full_name" class="col-sm-2 col-form-label">Полное имя фамилия</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="full_name" id="full_name" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="brand_name" class="col-sm-2 col-form-label">Бренд</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="brand_name" id="brand_name" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="kpp" class="col-sm-2 col-form-label">Kpp</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="kpp" id="kpp" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_name" class="col-sm-2 col-form-label">Имя банка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_name" id="bank_name" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_account_number" class="col-sm-2 col-form-label">Номер аккаунта банка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_account_number" id="bank_account_number" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_corr_account" class="col-sm-2 col-form-label">Корпоративный счет</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_corr_account" id="bank_corr_account" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_bic" class="col-sm-2 col-form-label">bic</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_bic" id="bank_bic" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="okpo" class="col-sm-2 col-form-label">okpo</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="okpo" id="okpo" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="okved" class="col-sm-2 col-form-label">okved</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="okved" id="okved" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="phone" class="col-sm-2 col-form-label">Телефон</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="phone" id="phone" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="web" class="col-sm-2 col-form-label">Корпоративный веб сайт</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="web" id="web" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="uuid" class="col-sm-2 col-form-label">Uuid</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" value="{{$uuidDirectoryFields}}" name="uuid" id="uuid" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="inn" class="col-sm-2 col-form-label">ИНН</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="inn" value="" id="inn" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="ogrn" class="col-sm-2 col-form-label">ОГРН</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="ogrn" value="" id="ogrn" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="legal_address" class="col-sm-2 col-form-label">Юр.адрес</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="legal_address" value="" id="legal_address" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="legal_email" class="col-sm-2 col-form-label">Почт.адрес</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="legal_email" value="" id="legal_email" required>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('counterpartyList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active"
                   role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

