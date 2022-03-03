@extends('admin.layouts.app')

@section('content')
    <form action="{{route('admin.user.update',$user->id)}}" enctype="multipart/form-data" method="post">
        @csrf
        <div class="form-group">
            <label>Имя</label>
            <input type="text"  class="form-control" name="first_name" value="{{$user->first_name}}" >
        </div>
        <div class="form-group">
            <label>Фамилия</label>
            <input type="text"  class="form-control" name="last_name" value="{{$user->last_name}}" >
        </div>

        <div class="form-group">
            <label>Логин</label>
            <input type="text"  class="form-control" name="login" value="{{$user->login}}" required>
        </div>

        <div class="form-group">
            <label>Телефон номер</label>
            <input type="text"  class="form-control" name="phone" value="{{$user->phone}}" required>
        </div>

        <div class="form-group">
            <label>Места работы</label>
            <input type="text"  class="form-control" name="workplace" value="{{$user->workplace}}" required>
        </div>
        <div class="form-group">
            <label>Организация</label>
            <input type="text"  class="form-control" name="organization" value="{{$user->organization}}" required>
        </div>


        <div class="form-group">
            <label>Новый пароль</label>
            <input type="text"  class="form-control" name="password_new"  >
        </div>



        <div class="form-group">
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </div>
    </form>
@endsection

@push('js')
    <script>
        $(function () {
            CKEDITOR.replace('ckeditor');
            CKEDITOR.config.height = 300;
        })
    </script>
@endpush
