@extends('admin.layouts.app')

@section('content')
    <form action="{{route('admin.setting')}}" enctype="multipart/form-data" method="post">
        @csrf
        <div class="form-group">
            <label>условия пользования</label>
            <textarea name="terms_of_use" class="form-control editor">{{$setting->terms_of_use}}</textarea>
        </div>
        <div class="form-group">
            <label>политика конфиденциальности</label>
            <textarea name="privacy_policy" class="form-control editor">{{$setting->privacy_policy}}</textarea>
        </div>

        <div class="form-group">
            <label>контакты</label>
            <textarea name="contact" class="form-control editor">{{$setting->contact}}</textarea>
        </div>

        <div class="form-group">
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>
@endsection


