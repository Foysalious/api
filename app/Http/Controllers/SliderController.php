<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $images = Slider::select('id', 'image_link', 'small_image_link', 'target_link', 'target_type', 'target_id')->show();
            return count($images) > 0 ? api_response($request, $images, 200, ['images' => $images]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}