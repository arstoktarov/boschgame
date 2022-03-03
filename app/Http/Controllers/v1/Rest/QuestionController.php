<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\Suggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function createSuggestion(Request $request) {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'email' => 'required|email',
            'description' => 'required|string|max:500'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $suggestion = new Suggestion();
        $suggestion->user_id = auth()->id();
        $suggestion->category_id = $request['category_id'];
        $suggestion->user_email = $request['email'];
        $suggestion->description = $request['description'];
        $suggestion->save();

        return self::Response(200, $suggestion);
    }
}
