@extends('admin.layouts.app')

@section('content')
    <table>
        <tr>
            <th>ID</th>
            <td>{{$id}}</td>
        </tr>
        <tr>
            <th>Имя</th>
            <td>{{$user->first_name}}</td>
        </tr>
        <tr>
            <th>Фамилия</th>
            <td>{{$user->last_name}}</td>
        </tr>
        <tr>
            <th>Логин</th>
            <td>{{$user->login}}</td>
        </tr>
        <tr>
            <th>Телефон номер</th>
            <td>{{$user->phone}}</td>
        </tr>
        <tr>
            <th>Страна</th>
            <td>{{$user->country}}</td>
        </tr>
        <tr>
            <th>Город</th>
            <td>{{$user->city}}</td>
        </tr>
        <tr>
            <th>Рейтинг</th>
            <td>{{$user->scores}}</td>
        </tr>
        <tr>
            <th>Места работы</th>
            <td>{{$user->workplace}}</td>
        </tr>
        <tr>
            <th>Организация</th>
            <td>{{$user->organization}}</td>
        </tr>
        <tr>
            <th>количество сыгранных матч</th>
            <td>{{\App\Models\GameUser::where('user_id',$user->id)->count()}}</td>
        </tr>
    </table>



    <style>
        th,td{
            border: 1px solid #000;
            padding: 10px 25px;
        }
    </style>
@endsection

