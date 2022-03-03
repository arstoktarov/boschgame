@extends('admin.layouts.app')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">

                <div class="body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>ID игры</th>
                                <th>ID создателя</th>
                                <th>Статус</th>
                                <th>Создан</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <tr>
                                    <td>{{$game->id}}</td>
                                    <td>{{$game->creator_id}}</td>
                                    <td>{{$game->status}}</td>
                                    <td>{{$game->created_at}}</td>
                                    <td>
{{--                                        <a href="{{route('admin.question.index',$cat->id)}}" class=" waves-effect btn btn-primary">Вопросы и ответы</a>--}}
{{--                                        <a href="{{route('admin.category.edit',$cat->id)}}" class="waves-effect btn btn-success"><i class="material-icons">mode_edit</i></a>--}}
                                        <a href="{{route('admin.category.game',$game->id)}}" class=" waves-effect btn btn-primary"><i class="material-icons">visibility</i></a>
{{--                                        <a href="{{route('admin.category.destroy',$cat->id)}}" onclick="return confirm('Вы уверены что хотите удалить?')" class="waves-effect btn btn-danger"><i class="material-icons">delete</i></a>--}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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

