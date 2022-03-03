@extends('admin.layouts.app')

@section('content')
    <div class="row clearfix">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">

                <div class="header">
{{--                    <h2 >{{$category->title}}</h2>--}}
{{--                    <a href="{{route('admin.question.add',$category->id)}}" class="btn btn-success"> <i class="material-icons">add</i></a>--}}

                    <form action="{{route('admin.statistics.month')}}">
                        @csrf
                        <label for="month">Choose a month: </label>
                        <input type="month" name="month" id="month">

                        <button>показать</button>
                    </form>


                </div>



                <div class="body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Статус</th>
                                <th>Статус</th>
                                <th>Создан</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <tr>
                                    <td>{{$game->id}}</td>
                                    <td>{{$game->status}}</td>
                                    <td>{{$game->finish_status}}</td>
                                    <td>{{$game->created_at}}</td>
                                    <td>
                                        <a href="{{route('admin.category.game',$game->id)}}" class=" waves-effect btn btn-primary"><i class="material-icons">visibility</i></a>
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

