<?php namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->partner;

    }

}