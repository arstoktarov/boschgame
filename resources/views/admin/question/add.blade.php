@extends('admin.layouts.app')

@section('content')
    <form action="{{route('admin.question.create')}}" enctype="multipart/form-data" method="post">
        @csrf
        <div class="form-group">
            <label>Вопрос</label>
            <input type="text"  class="form-control" name="question"  required >
        </div>
        <input type="hidden" name="category_id" value="{{$category_id}}">

            <div class="row">
                <div class="col-md-10">
                    <input class="form-control" name="answers[1]" required placeholder="Ответ №1 ">
                </div>
                <div class="col-md-2">
                    <input type="radio" id="answer_1" name="is_correct"  class="form-control" required  value="1" >
                    <label for="answer_1">правильный ответ </label>
                </div>


                <div class="col-md-10">
                    <input class="form-control" name="answers[2]" required placeholder="Ответ №2 ">
                </div>
                <div class="col-md-2">
                    <input type="radio" id="answer_2" name="is_correct"  class="form-control" required  value="2" >
                    <label for="answer_2">правильный ответ </label>
                </div>



                <div class="col-md-10">
                    <input class="form-control" name="answers[3]" required placeholder="Ответ №3 ">
                </div>
                <div class="col-md-2">
                    <input type="radio" id="answer_3" name="is_correct"  class="form-control" required  value="3" >
                    <label for="answer_3">правильный ответ </label>
                </div>


                <div class="col-md-10">
                    <input class="form-control" name="answers[4]" required placeholder="Ответ №4 ">
                </div>
                <div class="col-md-2">
                    <input type="radio" id="answer_4" name="is_correct"  class="form-control"  required value="4" >
                    <label for="answer_4">правильный ответ </label>
                </div>


            </div>

        <br>
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
