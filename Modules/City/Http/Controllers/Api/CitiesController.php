<?php

namespace Modules\City\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\City\Models\City;

class CitiesController extends Controller
{
    public function getCities(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $data = City::where('status', 1)->where('country_id', $request->get('country_id'))->paginate($perPage);
        return response()->json($data);
    }
}
