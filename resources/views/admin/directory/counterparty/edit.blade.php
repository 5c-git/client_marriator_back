@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="row">
        <div class="col-sm-11">
            <h1>Контрагент  {{$edit->id}}</h1>
        </div>
    </div>
@stop

@section('content')

    @php
        if($edit->active == 1){
           $checkBox = 'checked';
        }else{
           $checkBox = '';
        }
    @endphp
    <form class="status formCustomSubmit" action="{{route('counterpartyEditAjax')}}">
        @csrf
        <input type="hidden" name="id" value="{{$edit->id}}">


        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">Имя</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="name" id="name" value="{{$edit->name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="position" class="col-sm-2 col-form-label">Должность</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="position" id="position" value="{{$edit->position}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="full_name" class="col-sm-2 col-form-label">Полное имя фамилия</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="full_name" id="full_name" value="{{$edit->full_name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="brand_name" class="col-sm-2 col-form-label">Бренд</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="brand_name" id="brand_name" value="{{$edit->brand_name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="kpp" class="col-sm-2 col-form-label">Kpp</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="kpp" id="kpp" value="{{$edit->kpp}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_name" class="col-sm-2 col-form-label">Имя банка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{$edit->bank_name}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_corr_account" class="col-sm-2 col-form-label">Корпоративный счет</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_corr_account" id="bank_corr_account" value="{{$edit->bank_corr_account}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_bic" class="col-sm-2 col-form-label">bic</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_bic" id="bank_bic" value="{{$edit->bank_bic}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="okpo" class="col-sm-2 col-form-label">okpo</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="okpo" id="okpo" value="{{$edit->okpo}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="okved" class="col-sm-2 col-form-label">okved</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="okved" id="okved" value="{{$edit->okved}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="phone" class="col-sm-2 col-form-label">Телефон</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="phone" id="phone" value="{{$edit->phone}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="web" class="col-sm-2 col-form-label">Корпоративный веб сайт</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="web" id="web" value="{{$edit->web}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="bank_account_number" class="col-sm-2 col-form-label">Номер аккаунта банка</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="bank_account_number" id="bank_account_number" value="{{$edit->bank_account_number}}" required>
            </div>
        </div>
        <div class="form-group row">
            <label for="uuid" class="col-sm-2 col-form-label">Uuid</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="uuid" value="{{$edit->uuid}}" id="uuid" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="inn" class="col-sm-2 col-form-label">ИНН</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="inn" value="{{$edit->inn}}" id="inn" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="ogrn" class="col-sm-2 col-form-label">ОГРН</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="ogrn" value="{{$edit->ogrn}}" id="ogrn" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="legal_address" class="col-sm-2 col-form-label">Юр.адрес</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="legal_address" value="{{$edit->legal_address}}" id="legal_address" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="legal_email" class="col-sm-2 col-form-label">Почт.адрес</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="legal_email" value="{{$edit->legal_email}}" id="legal_email" required>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{route('counterpartyList')}}" style="margin-left: 10px" class="btn btn-secondary btn-md active" role="button" aria-pressed="true">Вернуться</a>
            </div>
        </div>
    </form>
    <script src="{{ asset('js/custom.js') }}" defer></script>

@stop

