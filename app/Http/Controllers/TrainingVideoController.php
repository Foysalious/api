<?php namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Dal\TrainingVideo\Contract as TrainingVideoRepository;
use Sheba\TrainingVideo\Response;

class TrainingVideoController extends Controller
{
    /**
     * @param Request $request
     * @param TrainingVideoRepository $video_repository
     * @return JsonResponse
     */
    public function index(Request $request, TrainingVideoRepository $video_repository)
    {
        try {
            $data = ($request->filled('key')) ? $video_repository->getByScreen($request->key) : $video_repository->getPublished();
            $link = Response::get($data);
            return api_response($request, $link, 200, ['data' => $link]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function formatResponse($training_video_data)
    {
        return $training_video_data->map(function ($item){
            return [
                'banner'             => $item->banner,
                'video_info'         => json_decode($item->video_info),
                'screen'             => $item->screen,
                'title'              => $item->title,
                'description'        => $item->description,
                'publication_status' => 1,
                'title_bn'           => $item->title_bn,
                'description_bn'     => $item->description_bn
            ];
        });
    }
}
