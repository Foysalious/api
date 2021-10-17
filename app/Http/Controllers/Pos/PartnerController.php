<?php namespace App\Http\Controllers\Pos;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function findById($partner, Request $request)
    {
        $partner = Partner::where('id', $partner)->select('id', 'name', 'logo', 'sub_domain')->first();
        if (!$partner) return http_response($request, null, 404);
        return http_response($request, $partner, 200, ['partner' => $partner]);
    }
}