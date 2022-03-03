@extends('admin.layouts.app')

@section('content')
    <form action="{{route('admin.category.create')}}" enctype="multipart/form-data" method="post">
        @csrf
        <div class="form-group">
            <label>название</label>
            <input type="text" required  class="form-control" name="title"  >
        </div>

        <div class="form-group">
            <label>Фото</label>
            <input type="file"  class="form-control" name="image"  required>
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
