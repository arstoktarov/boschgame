@extends('admin.layouts.app')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="header">
                    <h2 >Пользователи</h2>
                </div>

                <div class="header">
                    <form action="{{route('admin.user.index')}}">
                        @csrf
                        <input type="search" name="search" value="{{$search}}" placeholder="текст поиска...">
                        <select class="form-control" name="city" id="city">
                            @foreach($cities as $city)
                                <option value="{{$city->id}}">{{$city->title}}</option>
                            @endforeach
                        </select>
                        
                        <button>показать</button>
                    </form>
                </div>

                <div class="body">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Логин</th>
                                <th>Телефон номер</th>
                                <th>Страна , город</th>
                                <th>Рейтинг</th>
                                <th>Регистрация</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{$user->id}}</td>
                                    <td>{{$user->first_name }} {{$user->last_name }}</td>
                                    <td>{{$user->login}}</td>
                                    <td>{{$user->phone}}</td>
                                    <td>{{$user->country}}, {{$user->city}}</td>
                                    <td>{{$user->scores}}</td>
                                    <td>{{$user->created_at}}</td>
                                    <td>
                                        <a href="{{route('admin.user.show',$user->id)}}" class=" waves-effect btn btn-primary"><i class="material-icons">visibility</i></a>
                                        <a href="{{route('admin.user.edit',$user->id)}}" class="waves-effect btn btn-success"><i class="material-icons">mode_edit</i></a>
                                        <a href="{{route('admin.user.destroy',$user->id)}}" onclick="return confirm('Вы уверены что хотите удалить?')" class="waves-effect btn btn-danger"><i class="material-icons">delete</i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                {{$users->links()}}

            </div>
        </div>
    </div>


    <style>
        .header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
        }

        .header a {
            grid-column: end;
        }
        .header form input{
            border:1px solid #efefef;
            height: 30px;
            padding: 0px 5px;
        }
        .header form button{
            border:1px solid #efefef;
            height: 30px;
            padding: 0px 5px;
        }
    </style>
@endsection

