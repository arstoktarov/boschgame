<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function random(Request $request) {
        $rules = [
            'count' => 'numeric|min:1|max:5',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return self::Response(400, null, $validator->errors()->first());

        $categories = Category::inRandomOrder()->limit($request['count'] ?? Category::DEFAULT_RANDOM_COUNT)->get();

        return $categories;
    }

    public function index(Request $request) {
        return Category::all();
    }
}
