@extends('admin.layouts.app')

@section('content')
    <form action="{{route('admin.question.update',$q->id)}}" enctype="multipart/form-data" method="post">
        @csrf
        <div class="form-group">
            <label>Вопрос</label>
            <input type="text"  class="form-control" name="question" value="{{$q->title}}" >
        </div>


        @foreach(\App\Models\Answer::where('question_id',$q->id)->get() as $k => $a)
            <div class="row">
                <div class="col-md-10">
                    <input type="hidden" name="answers[{{$k}}][id]" value="{{$a->id}}">
                    <input class="form-control" for="answer_{{$a->id}}" name="answers[{{$k}}][title]" value="{{$a->title}}">
                </div>
                <div class="col-md-2">
                    <input {{$a->is_correct ? 'checked' :''}} type="radio" id="answer_{{$a->id}}" name="is_correct" value="{{$a->id}}" class="form-control"  >
                    <label for="answer_{{$a->id}}">правильный ответ </label>
                </div>


            </div>

        @endforeach
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
