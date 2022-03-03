@extends('admin.layouts.app')

@section('content')
    <h3>Расчет</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>ID поездки</th>
            <th>От куда</th>
            <th>Куда</th>
            <th>Сумма</th>
            <th>Номер Места</th>
            <th>Водитель</th>
            <th>Водитель (Номер)</th>
            <th>время отправления</th>
            <th>время прибытия</th>
        </tr>
        </thead>
        <tbody>
        @foreach($places as $p)
            <tr>
                <th scope="row">{{$p->id}}</th>
                <td>{{$p->car_travel_id}}</td>
                <td>{{$p->from_city}}</td>
                <td>{{$p->to_city}}</td>
                <td>{{$p->price}}тг</td>
                <td>{{$p->number}}</td>
                <td>{{$p->driver_name}}</td>
                <td>{{$p->driver_phone}}</td>
                <td>{{$p->departure_time}}</td>
                <td>{{$p->destination_time}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

