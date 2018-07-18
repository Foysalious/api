<?php namespace App\Http\Controllers;

use App\Models\TopUpVendor;
use Illuminate\Http\Request;

class TopUpController extends Controller
{
    public function getVendor(Request $request)
    {
        try {
            $vendors = TopUpVendor::select('id', 'name', 'is_published')->get();
            foreach ($vendors as $vendor){
                $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
                array_add($vendor, 'asset', $asset_name);
            }
            return api_response($request, $vendors, 200, ['vendors' => $vendors]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}