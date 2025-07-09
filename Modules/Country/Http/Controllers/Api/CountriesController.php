<?php

namespace Modules\Country\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Country\Models\Country;

class CountriesController extends Controller
{
    public function getCountries(Request $request){
        return response()->json(['countries' => Country::where('status', 1)->with('cities')->get()]);
    }
}
