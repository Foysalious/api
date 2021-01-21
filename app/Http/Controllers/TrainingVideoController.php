<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Sheba\Dal\TrainingVideo\Contract as TrainingVideoRepository;

class TrainingVideoController extends Controller
{
    public function index(Request $request, TrainingVideoRepository $video_repository)
    {
        $data = ($request->has('key')) ? $video_repository->getByScreen($request->key) : $video_repository->getPublished();
        $link = $this->formatResponse($data);
        return api_response($request, $link,200,['data' => $link]);
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
