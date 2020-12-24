<?php namespace App\Http\Controllers\Thana;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Location\ThanaList;

class ThanaController extends Controller
{

    public function index(Request $request, ThanaList $thanaList)
    {
        $thanas = $thanaList->getAllThana();
        return api_response($request, null, 200, ['thanas' => $thanas]);
    }
}
