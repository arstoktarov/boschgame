<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\UserResource;
use App\Models\Answer;
use App\Models\Car;
use App\Models\CarTravel;
use App\Models\CarTravelPlace;
use App\Models\Category;
use App\Models\CommentDislike;
use App\Models\CommentLike;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\Question;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QuestionController extends Controller

{

    public function index($id,Request $request)
    {
        $data['questions'] = Question::where('category_id',$id)->get();
        $data['category'] = Category::findOrFail($id);

        return view('admin.question.index', $data);
    }


    public function add($id)
    {
        $data['category_id' ] = $id;
        return view('admin.question.add',$data);

    }
    public function create(Request $request)
    {
        $q = new Question();
        $q->title = $request['question'];
        $q->category_id = $request['category_id'];
        $q->save();

        foreach ($request['answers'] as $k => $a){
            $answer = new Answer();
            $answer->title = $a;
            $answer->question_id  = $q->id;


            if ($k == $request['is_correct']){
                $answer->is_correct = 1;
            }else{
                $answer->is_correct = 0;
            }
            $answer->save();
        }


        return redirect()->route('admin.question.index',$request['category_id']);

    }


    public function edit($id, Request $request)
    {
      $data['q'] = Question::findOrFail($id);
        return view('admin.question.edit',$data);
    }

    public function update($id, Request $request)
    {
        $q = Question::findOrFail($id);
        if ($request['question']){
            $q->title = $request['question'];
            $q->save();
        }

        foreach ($request['answers'] as $answer) {
            $a = Answer::findOrFail($answer['id']);
            $a->title = $answer['title'];

            if ($a->id == $request['is_correct']){
                $a->is_correct =1;
            }else{
                $a->is_correct = 0;
            }
            $a->save();
        }


        return redirect()->route('admin.question.index',$q->category_id);
    }

    public function destroy($id)
    {
        $l = Question::findOrFail($id);
        $l->delete();
        return redirect()->back();
    }


}
