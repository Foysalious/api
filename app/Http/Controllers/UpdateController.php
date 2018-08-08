<?php namespace App\Http\Controllers;

use App\Models\Update;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function getUpdates(Request $request)
    {
        try {
            $updates = Update::select('id', 'title', 'message', 'image_link', 'video_link', 'target_link', 'publication_status')->get();

            return api_response($request, $updates, 200, ['updates' => $updates]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}