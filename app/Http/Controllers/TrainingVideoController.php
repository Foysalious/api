<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;

class TrainingVideoController extends Controller
{
    public function index(Request $request){
        $link=[
            'banner'=>'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/slides/1580974062_app_slide.jpg',
            'video_info'=>json_decode('{"link":"https://www.youtube.com/watch?v=bg2q2KeytMA","title":"\u0995\u09bf\u09ad\u09be\u09ac\u09c7 \u09aa\u09c7\u09ae\u09c7\u09a8\u09cd\u099f \u09b2\u09bf\u0982\u0995 \u09b6\u09c7\u09df\u09be\u09b0 \u0995\u09b0\u09ac\u09c7\u09a8? \u0964\u0964 sManager \u0964\u0964 \u09a1\u09bf\u099c\u09bf\u099f\u09be\u09b2 \u0995\u09be\u09b2\u09c7\u0995\u09b6\u09a8 \u0964\u0964 Digital Collection","duration":"00:00:30","thumbnails":{"default":{"url":"https://i.ytimg.com/vi/bg2q2KeytMA/default.jpg","width":120,"height":90},"medium":{"url":"https://i.ytimg.com/vi/bg2q2KeytMA/mqdefault.jpg","width":320,"height":180},"high":{"url":"https://i.ytimg.com/vi/bg2q2KeytMA/hqdefault.jpg","width":480,"height":360}}}'),
            'screen'=>'bondhu_registration',
            'title'=>"Registration Training Video",
            'description'=>"Some Description",
        ];
        return api_response($request,$link,200,['data'=>$link]);
    }
}
