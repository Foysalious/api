<?php namespace Sheba\TrainingVideo;

class Response
{
    /**
     * @param $data
     * @return mixed
     */
    public static function get($data)
    {
        return $data->map(function ($item){
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
