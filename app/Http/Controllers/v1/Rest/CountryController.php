<?php

namespace App\Http\Controllers\v1\Rest;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index() {
        return Country::all();
    }

    public function show(Country $country) {
        return $country;
    }

    public function cities(Country $country) {
        return $country->cities()->orderBy('title')->get();
    }

    public function showCity(Country $country, City $city) {
        return $city;
    }
}
